<?php
namespace CamassoMedelago\DriveMeSafely\Service;

use Doctrine\ORM\EntityManagerInterface; 
use CamassoMedelago\DriveMeSafely\Foundation\FIscritto;
use CamassoMedelago\DriveMeSafely\Foundation\FPatente;
use CamassoMedelago\DriveMeSafely\Foundation\FSpesa;
use CamassoMedelago\DriveMeSafely\Foundation\FUtenteRegistrato;
use CamassoMedelago\DriveMeSafely\Entity\EIscritto;
use CamassoMedelago\DriveMeSafely\Entity\EPatente;
use CamassoMedelago\DriveMeSafely\DTO\PacchettoPatenteDTO;
use DateTimeImmutable;

//Service che implementa la logica di business dell'iscrizione
class SIscrizione
{
    private EntityManagerInterface $em;
    private FIscritto $fIscritto;
    private FUtenteRegistrato $fUtente;
    private FPatente $fPatente;
    private FSpesa $fSpesa;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
        $this->fIscritto = new FIscritto($em);
        $this->fUtente   = new FUtenteRegistrato($em);
        $this->fPatente  = new FPatente($em);
        $this->fSpesa    = new FSpesa($em);
    }

    //1)Metodo che riporta tutte le patenti disponibili per l'iscrizione 
    public function getPatenti(): array
    {
        $patenti = $this->fPatente->findPacchettiPatenti();

        $pacchetti = [];

        foreach ($patenti as $patente) {

            $spese = $patente->getSpese()->toArray();

            $importoTotale = 0.0;

            foreach ($spese as $spesa) {
                $importoTotale += (float)$spesa->getImporto();
            }

           $pacchetti[] = new PacchettoPatenteDTO(
                $patente,
                $spese,
                $importoTotale
            );
        }

        return $pacchetti;
    }

    //2)Metodo che riporta il pacchetto patente selezionato dall'utente
    public function getPacchetto(int $idPatente): PacchettoPatenteDTO
    {
        //Controllo che l'id della patente sia valido
        if ($idPatente <= 0) {
            throw new \InvalidArgumentException(
                "Non è stato selezionato un pacchetto valido."
            );
        }

        $patente = $this->fPatente->findPacchettoById($idPatente);

        if ($patente === null) {
            throw new \RuntimeException(
                "Pacchetto patente non trovato a sistema."
            );
        }

        $spese = $patente->getSpese()->toArray();

        $importoTotale = 0.0;

        foreach ($spese as $spesa) {
            $importoTotale += (float)$spesa->getImporto();
        }

        return new PacchettoPatenteDTO(
            $patente,
            $spese,
            $importoTotale
        );
    }


   
    //3)Metodo che crea l'entità EIscritto con i dati inseriti dall'utente, non viene ancora salvato nel db
    public function iscrizione(
        int $idPa,
        string $nome,
        string $cognome,
        string $username,
        string $email,
        string $password,
        string $codiceFiscale,
        string $indirizzo,
        string $luogoNascita,
        DateTimeImmutable $dataNascita,
        string $telefono
    ): EIscritto {

        //Controllo patente
        if ($idPa <= 0) {
            throw new \InvalidArgumentException(
                "Non è stato selezionato un pacchetto valido."
            );
        }

        $patente = $this->fPatente->findPacchettoById($idPa);

        if ($patente === null) {
            throw new \InvalidArgumentException(
                "Patente non trovata."
            );
        }

        //Controllo nome
        if (trim($nome) === '') {
            throw new \InvalidArgumentException(
                "Il nome non può essere nullo o vuoto."
            );
        }

        $nomePulito = trim($nome);

        if (!$this->isNomeValido($nomePulito)) {
            throw new \InvalidArgumentException(
                "Nome non valido."
            );
        }

        //Controllo cognome
        if (trim($cognome) === '') {
            throw new \InvalidArgumentException(
                "Il cognome non può essere nullo o vuoto."
            );
        }

        $cognomePulito = trim($cognome);

        if (!$this->isNomeValido($cognomePulito)) {
            throw new \InvalidArgumentException(
                "Cognome non valido."
            );
        }

        //Controllo data di nascita
        if (!$this->isDataValida($dataNascita)) {
            throw new \InvalidArgumentException(
                "Data di nascita non valida."
            );
        }

        $etaMinima = $this->getEtaMinimaPerPatente($patente->getTipo());
        if (!$this->isIdoneoPatente($dataNascita, $etaMinima)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Iscrizione non valida: l'età minima per la patente %s è %d anni.",
                    $patente->getTipo(),
                    $etaMinima
                )
            );
        }

        //Controllo codice fiscale
        if (trim($codiceFiscale) === '') {
            throw new \InvalidArgumentException(
                "Il codice fiscale non può essere nullo o vuoto."
            );
        }

        $cf = strtoupper(trim($codiceFiscale));

        if (!$this->isCodiceFiscaleValido($cf)) {
            throw new \InvalidArgumentException(
                "Codice fiscale non valido."
            );
        }

        if ($this->fIscritto->existsByCF($cf)) {
            throw new \RuntimeException(
                "Il Codice Fiscale inserito è già associato a un allievo."
            );
        }

        //Controllo username
        if (trim($username) === '') {
            throw new \InvalidArgumentException(
                "Lo username non può essere nullo o vuoto."
            );
        }

        $usernamePulito = trim($username);

        if (!$this->isUsernameValido($usernamePulito)) {
            throw new \InvalidArgumentException(
                "Lo username può contenere solo lettere, numeri, trattini e underscore (3-50 caratteri)."
            );
        }

        if ($this->fUtente->getByUsername($usernamePulito) !== null) {
            throw new \RuntimeException(
                "Username non valido o già utilizzato."
            );
        }

        //Controllo password
        if (trim($password) === '') {
            throw new \InvalidArgumentException(
                "La password non può essere nulla o vuota."
            );
        }

        if (!$this->isPasswordValida($password)) {
            throw new \InvalidArgumentException(
                "La password deve contenere almeno 8 caratteri, "
                . "una maiuscola, una minuscola, un numero "
                . "e un carattere speciale."
            );
        }

        //Controllo del telefono
        if (trim($telefono) === '') {
            throw new \InvalidArgumentException(
                "Il numero di telefono non può essere nullo."
            );
        }

        $telefonoPulito = trim($telefono);

        if (!$this->isTelefonoValido($telefonoPulito)) {
            throw new \InvalidArgumentException(
                "Numero di telefono non valido."
            );
        }

        //Controllo email
        if (trim($email) === '') {
            throw new \InvalidArgumentException(
                "L'email non può essere nulla o vuota."
            );
        }

        $emailPulita = strtolower(trim($email));

        if (!$this->isEmailValida($emailPulita)) {
            throw new \InvalidArgumentException(
                "Email non valida."
            );
        }

        if ($this->fUtente->getByEmail($emailPulita) !== null) {
            throw new \RuntimeException(
                "L'email inserita è già associata a un utente registrato."
            );
        }

        //Controllo indirizzo
        if (trim($indirizzo) === '') {
            throw new \InvalidArgumentException(
                "L'indirizzo non può essere nullo o vuoto."
            );
        }

        $indirizzoPulito = trim($indirizzo);

        if (!$this->isIndirizzoValido($indirizzoPulito)) {
            throw new \InvalidArgumentException(
                "Indirizzo non valido."
            );
        }


        //Controllo luogo di nascita
        if (trim($luogoNascita) === '') {
            throw new \InvalidArgumentException(
                "Il luogo di nascita non può essere nullo o vuoto."
            );
        }

        $luogoNascitaPulito = trim($luogoNascita);

        if (!$this->isLuogoNascitaValido($luogoNascitaPulito)) {
            throw new \InvalidArgumentException(
                "Luogo di nascita non valido."
            );
        }

        //Creazione dell'iscritto
        $iscritto = new EIscritto(
            $nomePulito,
            $cognomePulito,
            $emailPulita,
            $usernamePulito,
            $password,
            true,
            $cf,
            $dataNascita,
            $luogoNascitaPulito,
            $indirizzoPulito,
            $telefonoPulito,
            $patente
        );

        return $iscritto;
    }


    //4)Metodo che salva l'entità EIscritto nel database
    public function confermaIscrizione(EIscritto $iscritto): void
    {
        $this->em->beginTransaction();
        try {
            $this->fIscritto->save($iscritto);
            $this->em->flush();
            $this->em->commit();
            
        } catch (\Throwable $e) {
           
            $this->em->rollback();
            throw $e; 
        }
    }

    //Metodi ausiliari per la validazione dei dati inseriti dall'utente
    private function isNomeValido(string $valore): bool
    {
        return mb_strlen($valore) >= 2
            && mb_strlen($valore) <= 50
            && preg_match(
                "/^[\p{L}' -]+$/u",
                $valore
            );
    }


    public function getEtaMinimaPerPatente(string $tipoPatente): int
    {
        $tipo = strtoupper(trim($tipoPatente));

        return match ($tipo) {
            'AM' => 14,
            'A1', 'B1' => 16,
            'A2', 'B', 'BE', 'B96' => 18,
            'C1', 'C1E' => 18,
            'C', 'CE', 'D1', 'D1E' => 21,
            'A', 'D', 'DE' => 24,
            default => 18,
        };
    }

    private function isIdoneoPatente(
        DateTimeImmutable $data,
        int $etaMinima = 18
    ): bool {
        $dataMinima = new DateTimeImmutable(
            "-{$etaMinima} years"
        );

        return $data <= $dataMinima;
    }


    private function isUsernameValido(string $username): bool
    {
        return mb_strlen($username) >= 3
            && mb_strlen($username) <= 50
            && preg_match('/^[A-Za-z0-9_.-]+$/', $username);
    }

    private function isDataValida(
        DateTimeImmutable $data
    ): bool {
        $oggi = new DateTimeImmutable('today');
        $limiteSecolare = new DateTimeImmutable('-120 years');

        return $data <= $oggi && $data >= $limiteSecolare;
    }

    private function isTelefonoValido(
        string $telefono
    ): bool {
        $soloCifre = preg_replace('/[^0-9]/', '', $telefono);

        return strlen($soloCifre) >= 9
            && strlen($soloCifre) <= 15
            && preg_match(
                '/^\+?[0-9 ]+$/',
                $telefono
            );
    }

    private function isCodiceFiscaleValido(
        string $cf
    ): bool {
        return preg_match(
            '/^[A-Z0-9]{16}$/',
            $cf
        );
    }

    private function isEmailValida(
        string $email
    ): bool {
        return strlen($email) <= 100
            && filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            ) !== false;
    }

    private function isIndirizzoValido(
        string $indirizzo
    ): bool {
        return mb_strlen($indirizzo) >= 5
            && mb_strlen($indirizzo) <= 100
            && preg_match(
                "/^[\p{L}\p{N} .,'\/\-]+$/u",
                $indirizzo
            );
    }

    private function isLuogoNascitaValido(
        string $luogo
    ): bool {
        return mb_strlen($luogo) >= 2
            && mb_strlen($luogo) <= 100
            && preg_match(
                "/^[\p{L} .'-]+$/u",
                $luogo
            );
    }

    private function isPasswordValida(
        string $password
    ): bool {
        if (
            strlen($password) < 8 ||
            strlen($password) > 64
        ) {
            return false;
        }
        //Espressioni regolari
        return preg_match('/[A-Z]/', $password)
            && preg_match('/[a-z]/', $password)
            && preg_match('/[0-9]/', $password)
            && preg_match('/[^A-Za-z0-9]/', $password);
    }
}
