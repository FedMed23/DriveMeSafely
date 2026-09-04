<?php

namespace CamassoMedelago\DriveMeSafely\Service;

use CamassoMedelago\DriveMeSafely\Entity\EIscritto;
use CamassoMedelago\DriveMeSafely\Entity\EDipendente;
use CamassoMedelago\DriveMeSafely\Entity\EPagamento;
use CamassoMedelago\DriveMeSafely\Entity\ESpesa;
use CamassoMedelago\DriveMeSafely\Entity\EProprietario;
use CamassoMedelago\DriveMeSafely\Entity\EUtenteRegistrato;
use CamassoMedelago\DriveMeSafely\Entity\ECartaDiCredito;
use CamassoMedelago\DriveMeSafely\DTO\CassaDTO;
use CamassoMedelago\DriveMeSafely\Foundation\FSpesa;
use CamassoMedelago\DriveMeSafely\Foundation\FUtenteRegistrato;
use CamassoMedelago\DriveMeSafely\Foundation\FPagamento;
use CamassoMedelago\DriveMeSafely\Foundation\FCartaDiCredito;
use CamassoMedelago\DriveMeSafely\Utils\CartaDiCreditoUtil;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

//Service che gestisce le operazioni relative alle spese e ai pagamenti, sia dell'iscritto sia del proprieterio
class SPagamentoSpese
{
    private EntityManagerInterface $em;
    private FUtenteRegistrato $fUtente;
    private FSpesa $fSpesa;
    private FPagamento $fPagamento;
    private FCartaDiCredito $fCarta;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->fUtente = new FUtenteRegistrato($em);
        $this->fSpesa = new FSpesa($em);
        $this->fPagamento = new FPagamento($em);
        $this->fCarta = new FCartaDiCredito($em);
    }
     
     //1)Restituisce il report delle spese per un utente specifico, includendo il totale delle spese, l'importo pagato e il saldo rimanente.
    public function getSpese(int $idUtente): array
    { 
        //Verifica utente esistente
        $utente = $this->fUtente->getById($idUtente);
        if ($utente === null) {
            throw new \InvalidArgumentException('Utente non trovato.');
        }

        //Recupero delle spese tramite la patente dell'utente se è un iscritto, altrimenti recupero tutte le spese per il proprietario
        $patente = null;
        $spese = [];
        
        if ($utente instanceof EIscritto) {
            $patente = $utente->getTipoPatente();
            if ($patente !== null) {
                $spese = $this->fSpesa->findSpeseIscritto($idUtente);
            }
        } elseif ($utente instanceof EProprietario) {
            $spese = $this->fSpesa->findSpeseProprietario();
        } elseif ($utente instanceof EDipendente) {
            throw new \InvalidArgumentException('I dipendenti non hanno spese personali da visualizzare o saldare.');
        } else {
            throw new \InvalidArgumentException('Tipo di utente non riconosciuto.');
        }

        $righeSpese = [];
        foreach ($spese as $spesa) {
            $pagamento = $this->fPagamento->findByUtenteAndSpesa($utente, $spesa);

            $righeSpese[] = [
                'spesa' => $spesa,
                'pagamento' => $pagamento,
            ];
        }

        //Da rivedere sintatticamente
        $pagato = $utente instanceof EIscritto
            ? $this->fPagamento->getTotalePagatoIscritto($idUtente)
            : $this->fPagamento->getTotalePagatoProprietario($idUtente);

        $costoTotale = array_reduce(
            $spese,
            static fn (float $totale, ESpesa $spesa): float =>
                $totale + $spesa->getImporto(),
            0.0
        );

        return [
            'patente' => $patente,
            'righeSpese' => $righeSpese,
            'costoTotale' => $costoTotale,
            'pagato' => $pagato,
            'saldo' => $costoTotale - $pagato,
        ];
    }

    //1)Metodo che restituisce i movimenti di cassa giornalieri per il proprieterio
    public function getMovimentiCassa(int $idProprietario): CassaDTO
    {
        $utente = $this->fUtente->getById($idProprietario);
        if ($utente === null) {
            throw new \InvalidArgumentException('Proprietario non trovato.');
        }

        if (!$utente instanceof EProprietario) {
            throw new \InvalidArgumentException(
                'Solo il proprietario può visualizzare la cassa.'
            );
        }

        $data = new DateTimeImmutable('today');

        return new CassaDTO(
            $this->fPagamento->findStoricoPagamenti(),
            $this->fPagamento->getEntrateGiornaliere($data),
            $this->fPagamento->getUsciteGiornaliere($data)
        );
    }

    //2)Recupera la spesa selezionata
    public function getSpesa(int $idSpesa): ESpesa
    {
        $spesa = $this->fSpesa->findById($idSpesa);
        if ($spesa === null) {
            throw new \InvalidArgumentException('Spesa non trovata.');
        }

        return $spesa;
    }

    //3)Gestisce il pagamento di una spesa da parte di un utente, verificando la validità dei dati della carta di credito e creando un oggetto EPagamento.
    public function pagaSpesa(
        int $idUtente,
        int $idSpesa,
        string $numeroCarta,
        string $nomeTitolare,
        string $cognomeTitolare,
        DateTimeImmutable $dataScadenza,
        ?string $cvv = null
    ): EPagamento {
        
        //1)Controllo parametri utente
        $utente = $this->fUtente->getById($idUtente);
        if ($utente === null) {
            throw new \InvalidArgumentException('Utente non trovato.');
        }

        if ($utente instanceof EDipendente) {
            throw new \InvalidArgumentException('I dipendenti non possono effettuare pagamenti di spese.');
        }
        
        //2)Controllo parametri spesa
        $spesa = $this->getSpesa($idSpesa);
        $this->verificaSpesaAccessibile($utente, $spesa);

        //3)Verifica che la spesa non sia già stata pagata
        if ($utente instanceof EIscritto) {
            $pagata = $this->fPagamento->spesaPagataIscritto($idSpesa, $idUtente);
        } else {
            $pagata = $this->fPagamento->spesaPagataProprietario($idSpesa, $idUtente);
        }
        
        if ($pagata) {
            throw new \InvalidArgumentException('Spesa già pagata.');
        }

        //4)Normalizzazione e verifica dei dati della carta di credito
        $numeroCartaNormalizzato = CartaDiCreditoUtil::normalizzaNumeroCarta($numeroCarta);
        $nomeTitolare = trim($nomeTitolare);
        $cognomeTitolare = trim($cognomeTitolare);

        // Validazione algoritmo di Luhn e lunghezza standard
        if (!CartaDiCreditoUtil::validaLuhn($numeroCartaNormalizzato)) {
            throw new \InvalidArgumentException('Numero carta non valido.');
        }

        // Validazione CVV se presente
        if ($cvv !== null && $cvv !== '' && !CartaDiCreditoUtil::validaCvv($cvv)) {
            throw new \InvalidArgumentException('Codice CVV non valido (richieste 3 o 4 cifre).');
        }

        // Verifica scadenza: la carta è valida fino alle 23:59:59 dell'ultimo giorno del mese indicato
        if (CartaDiCreditoUtil::isCartaScaduta($dataScadenza)) {
            throw new \InvalidArgumentException('Carta scaduta.');
        }

        if (!CartaDiCreditoUtil::isDataScadenzaValida($dataScadenza)) {
            throw new \InvalidArgumentException('Data di scadenza non valida.');
        }

        if (
            !CartaDiCreditoUtil::validaTitolare($nomeTitolare)
            || !CartaDiCreditoUtil::validaTitolare($cognomeTitolare)
        ) {
            throw new \InvalidArgumentException(
                'Nome e cognome del titolare non validi (ammessi 2-50 caratteri, inclusi accenti, spazi e apostrofi).'
            );
        }

        // Ricerca per hash SHA-256 (conservazione sicura secondo PCI-DSS)
        $hashCarta = CartaDiCreditoUtil::hashNumeroCarta($numeroCartaNormalizzato);
        $ultimeCifre = CartaDiCreditoUtil::estraiUltimeCifre($numeroCartaNormalizzato);
        $carta = $this->fCarta->findByNumeroCartaHash($hashCarta);

        if ($carta === null) {
            // Fallback ricerca per numero in chiaro (retrocompatibilità)
            $carta = $this->fCarta->findByNumeroCarta($numeroCartaNormalizzato);
        }

        if ($carta === null) {
            $carta = new ECartaDiCredito(
                $nomeTitolare,
                $cognomeTitolare,
                $dataScadenza,
                $hashCarta,
                $ultimeCifre
            );
        } else {
            // Verifica corrispondenza dati anagrafici e mese/anno di scadenza
            $stessoNome = mb_strtolower($carta->getNomeTitolareCarta()) === mb_strtolower($nomeTitolare);
            $stessoCognome = mb_strtolower($carta->getCognomeTitolareCarta()) === mb_strtolower($cognomeTitolare);
            $stessaScadenza = $carta->getDataScadenza()->format('Y-m') === $dataScadenza->format('Y-m');

            if (!$stessoNome || !$stessoCognome || !$stessaScadenza) {
                throw new \InvalidArgumentException(
                    'I dati del titolare non corrispondono alla carta.'
                );
            }
        }
        
        //Creazione del pagamento
        $pagamento = new EPagamento();
        $pagamento->init($utente, $spesa, $carta);

        return $pagamento;
    }

    //4)Conferma transazionale e atomica del pagamento della spesa 
    public function confermaPagamento(EPagamento $pagamento): void
    {
        $this->em->beginTransaction();
        try {
            $utente = $pagamento->getUtenteRegistrato();
            $spesa = $pagamento->getSpesa();
            $idUtente = (int) $utente->getId();
            $idSpesa = (int) $spesa->getIdSpesa();

            // Riverifica concorrenza: controlla se la spesa è stata saldata nel frattempo
            $giaPagata = $utente instanceof EIscritto
                ? $this->fPagamento->spesaPagataIscritto($idSpesa, $idUtente)
                : $this->fPagamento->spesaPagataProprietario($idSpesa, $idUtente);

            if ($giaPagata) {
                throw new \InvalidArgumentException('Spesa già pagata da un\'altra transazione.');
            }

            $carta = $pagamento->getCartaDiCredito();
            if ($carta->getId() === null) {
                // Controllo atomico per evitare duplicati concorrenti su numero_carta hash
                $cartaEsistente = $this->fCarta->findByNumeroCartaHash($carta->getNumeroCarta());
                if ($cartaEsistente !== null) {
                    $pagamento->setCartaDiCredito($cartaEsistente);
                } else {
                    $this->em->persist($carta);
                }
            }

            $this->fPagamento->persist($pagamento);
            $this->em->flush();
            $this->em->commit();
        } catch (\Throwable $e) {
            if ($this->em->getConnection()->isTransactionActive()) {
                $this->em->rollback();
            }
            throw $e;
        }
    }
    //Metodi ausiliari 

    //Restituisce la spesa per un utente specifico, verificando che l'utente abbia accesso alla spesa.
    public function getSpesaPerUtente(int $idUtente, int $idSpesa): ESpesa
    {
        $utente = $this->fUtente->getById($idUtente);
        if ($utente === null) {
            throw new \InvalidArgumentException('Utente non trovato.');
        }

        $spesa = $this->getSpesa($idSpesa);
        $this->verificaSpesaAccessibile($utente, $spesa);

        return $spesa;
    }

    //Verifica che l'utente abbia accesso alla spesa specificata 
    private function verificaSpesaAccessibile(
        EUtenteRegistrato $utente,
        ESpesa $spesa
    ): void {
        if ($utente instanceof EProprietario) {
            if ($spesa->getAmbito() !== 'PROPRIETARIO') {
                throw new \InvalidArgumentException(
                    'La spesa non è disponibile per questo utente.'
                );
            }
            return;
        }

        if (!$utente instanceof EIscritto) {
            throw new \InvalidArgumentException(
                'Tipo di utente non riconosciuto.'
            );
        }

        if (!$this->fSpesa->isAssociataAIscritto(
            (int) $utente->getId(),
            (int) $spesa->getIdSpesa()
        )) {
            throw new \InvalidArgumentException(
                'La spesa non è associata al tuo pacchetto patente.'
            );
        }
    }

    //Controllo regex per il nome e cognome del titolare della carta di credito
    private function nomeValido(string $nome): bool
    {
        return (bool) preg_match(
            "/^[\p{L}]+(?:[ '-][\p{L}]+)*$/u",
            $nome
        );
    }
}
