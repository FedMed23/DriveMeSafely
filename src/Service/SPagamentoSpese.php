<?php

namespace CamassoMedelago\DriveMeSafely\Service;

use CamassoMedelago\DriveMeSafely\Entity\EIscritto;
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
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

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

    /**
     * Restituisce lo storico e il riepilogo giornaliero della cassa.
     *
     * La cassa è disponibile esclusivamente al proprietario.
     */
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
    public function pagaSpesa(int $idUtente, int $idSpesa, string $numeroCarta, string $nomeTitolare, string $cognomeTitolare, DateTimeImmutable $dataScadenza): EPagamento {
        
        //1)Controllo parametri utente
        $utente = $this->fUtente->getById($idUtente);
        if ($utente === null) {
            throw new \InvalidArgumentException('Utente non trovato.');
        }
        
        //2)Controllo parametri spesa
        $spesa = $this->getSpesa($idSpesa);
        $this->verificaSpesaAccessibile($utente, $spesa);

        //3)Verifica che sia un iscritto o un proprietario
        if ($utente instanceof EIscritto) {
            $pagata = $this->fPagamento->spesaPagataIscritto($idSpesa, $idUtente);
        } else {
            $pagata = $this->fPagamento->spesaPagataProprietario($idSpesa, $idUtente);
        }
        
        if ($pagata) {
            throw new \InvalidArgumentException('Spesa già pagata.');
        }

        //4)Verifica dei dati della carta di credito
        $numeroCarta = trim($numeroCarta);
        $nomeTitolare = trim($nomeTitolare);
        $cognomeTitolare = trim($cognomeTitolare);

        if (!preg_match('/^[0-9]{16}$/', $numeroCarta)) {
            throw new \InvalidArgumentException('Numero carta errato.');
        }

        if ($dataScadenza < new DateTimeImmutable('today')) {
            throw new \InvalidArgumentException('Carta scaduta.');
        }

        if (
            !$this->nomeValido($nomeTitolare)
            || !$this->nomeValido($cognomeTitolare)
        ) {
            throw new \InvalidArgumentException(
                'Nome e cognome del titolare non validi.'
            );
        }

        $carta = $this->fCarta->findByNumeroCarta($numeroCarta);

        //Da sistemare 
        if ($carta === null) {
            $carta = new ECartaDiCredito( $nomeTitolare,$cognomeTitolare,$dataScadenza,$numeroCarta);
        } elseif (
            $carta->getNomeTitolareCarta() !== $nomeTitolare
            || $carta->getCognomeTitolareCarta() !== $cognomeTitolare
            || $carta->getDataScadenza()->format('Y-m-d')
                !== $dataScadenza->format('Y-m-d')
        ) {
            throw new \InvalidArgumentException(
                'I dati del titolare non corrispondono alla carta.'
            );
        }
        
        //Creazione del pagamento
        $pagamento = new EPagamento();
        $pagamento->init($utente, $spesa, $carta);

        return $pagamento;
    }

    //4)Conferma del pagamento della spesa 
    public function confermaPagamento(EPagamento $pagamento): void
    {
        $this->em->beginTransaction();
        try {
            $carta = $pagamento->getCartaDiCredito();
            if ($carta->getId() === null) {
                $this->em->persist($carta);
            }
            $this->fPagamento->persist($pagamento);
            $this->em->flush();
            $this->em->commit();
        } catch (\Throwable $e) {
            $this->em->rollback();
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
