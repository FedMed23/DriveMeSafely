<?php
namespace CamassoMedelago\DriveMeSafely\Service;

use Doctrine\ORM\EntityManagerInterface; 
use CamassoMedelago\DriveMeSafely\Foundation\FIscritto;
use CamassoMedelago\DriveMeSafely\Foundation\FPatente;
use CamassoMedelago\DriveMeSafely\Foundation\FSpesa;
use CamassoMedelago\DriveMeSafely\Entity\EIscritto;
use CamassoMedelago\DriveMeSafely\Entity\EPatente;
use CamassoMedelago\DriveMeSafely\DTO\DPacchettoPatente;
use DateTimeImmutable;

class SIscrizione
{
    private EntityManagerInterface $em;
    private FIscritto $fIscritto;
    private FPatente $fPatente;
    private FSpesa $fSpesa;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
        $this->fIscritto = new FIscritto($em);
        $this->fPatente  = new FPatente($em);
        $this->fSpesa    = new FSpesa($em);
    }

    /*
     * ============================================================
     * 1. VISUALIZZAZIONE PACCHETTI PATENTE
     * ============================================================
     */

    /**
     * Restituisce tutti i pacchetti patente disponibili.
     *
     * Per ogni patente vengono recuperate le relative spese
     * e viene calcolato il costo totale del pacchetto.
     *
     * @return DPacchettoPatente[]
     */
    public function getPatenti(): array
    {
        $patenti = $this->fPatente->findPacchettiPatenti();

        $pacchetti = [];

        foreach ($patenti as $patente) {

            $spese = $patente->getSpese();

            $importoTotale = 0.0;

            foreach ($spese as $spesa) {
                $importoTotale += (float)$spesa->getImporto();
            }

            $pacchetti[] = new DPacchettoPatente(
                $patente,
                $spese,
                $importoTotale
            );
        }

        return $pacchetti;
    }


    /*
     * ============================================================
     * 2. SELEZIONE PACCHETTO
     * ============================================================
     */

    /**
     * Restituisce il pacchetto patente selezionato.
     */
    public function getPacchetto(int $idPatente): DPacchettoPatente
    {
        if ($idPatente <= 0) {
            throw new \InvalidArgumentException(
                "Non è stato selezionato un pacchetto valido."
            );
        }

        $patente = $this->fPatente->getPatenteById($idPatente);

        if ($patente === null) {
            throw new \RuntimeException(
                "Pacchetto patente non trovato a sistema."
            );
        }

        $spese = $this->patente->getSpese();

        $importoTotale = 0.0;

        foreach ($spese as $spesa) {
            $importoTotale += (float)$spesa->getImporto();
        }

        return new DPacchettoPatente(
            $patente,
            $spese,
            $importoTotale
        );
    }


    /*
     * ============================================================
     * 3. ISCRIZIONE
     * ============================================================
     */

    /**
     * Valida i dati inseriti e crea l'entità EIscritto.
     *
     * ATTENZIONE:
     * questo metodo NON salva ancora l'iscritto.
     *
     * Il salvataggio avviene solamente con
     * confermaIscrizione().
     */
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

        /*
         * --------------------------------------------------------
         * Patente
         * --------------------------------------------------------
         */

        if ($idPa <= 0) {
            throw new \InvalidArgumentException(
                "Non è stato selezionato un pacchetto valido."
            );
        }

        $patente = $this->fPatente->getPatenteById($idPa);

        if ($patente === null) {
            throw new \InvalidArgumentException(
                "Patente non trovata."
            );
        }


        /*
         * --------------------------------------------------------
         * Nome
         * --------------------------------------------------------
         */

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

        /*
         * --------------------------------------------------------
         * Cognome
         * --------------------------------------------------------
         */

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
        /*
         * --------------------------------------------------------
         * Data di nascita
         * --------------------------------------------------------
         */

        if (!$this->isDataValida($dataNascita)) {
            throw new \InvalidArgumentException(
                "Data di nascita non valida."
            );
        }

        if (!$this->isIdoneoPatente($dataNascita)) {
            throw new \InvalidArgumentException(
                "Iscrizione non valida: l'età minima per l'iscrizione è 16 anni."
            );
        }

        /*
         * --------------------------------------------------------
         * Codice fiscale
         * --------------------------------------------------------
         */

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


        /*
         * --------------------------------------------------------
         * Username
         * --------------------------------------------------------
         */

        if (trim($username) === '') {
            throw new \InvalidArgumentException(
                "Lo username non può essere nullo o vuoto."
            );
        }

        $usernamePulito = trim($username);

        if ($this->fIscritto->findByUsername($usernamePulito) !== null) {
            throw new \RuntimeException(
                "Username non valido o già utilizzato."
            );
        }


        /*
         * --------------------------------------------------------
         * Password
         * --------------------------------------------------------
         */

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


        /*
         * --------------------------------------------------------
         * Telefono
         * --------------------------------------------------------
         */

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


        /*
         * --------------------------------------------------------
         * Email
         * --------------------------------------------------------
         */

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

        if ($this->fIscritto->findByEmail($emailPulita) !== null) {
            throw new \RuntimeException(
                "L'email inserita è già associata a un allievo."
            );
        }


        /*
         * --------------------------------------------------------
         * Indirizzo
         * --------------------------------------------------------
         */

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


        /*
         * --------------------------------------------------------
         * Luogo di nascita
         * --------------------------------------------------------
         */

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


        /*
         * --------------------------------------------------------
         * Creazione dell'entità
         * --------------------------------------------------------
         */

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


    /*
     * ============================================================
     * 4. CONFERMA ISCRIZIONE
     * ============================================================
     */

    /**
     * Salva definitivamente l'iscritto nel database.
     */
        /**
     * Esegue il salvataggio in modo atomico (Stile esplicito).
     */
    public function confermaIscrizione(EIscritto $iscritto): void
    {
        $this->em->beginTransaction();
        try {
            $this->fIscritto->save($iscritto);
            $this->em->commit();
            
        } catch (\Throwable $e) {
           
            $this->em->rollback();
            throw $e; 
        }
    }


    /*
     * ============================================================
     * 5. METODI DI VALIDAZIONE
     * ============================================================
     */

    private function isNomeValido(string $valore): bool
    {
        return mb_strlen($valore) >= 2
            && mb_strlen($valore) <= 50
            && preg_match(
                "/^[\p{L}' -]+$/u",
                $valore
            );
    }


    private function isIdoneoPatente(
        DateTimeImmutable $data
    ): bool {
        $dataMinima = new DateTimeImmutable(
            '-16 years'
        );

        return $data <= $dataMinima;
    }


    private function isDataValida(
        DateTimeImmutable $data
    ): bool {
        $oggi = new DateTimeImmutable('today');

        return $data <= $oggi;
    }

    private function isTelefonoValido(
        string $telefono
    ): bool {
        return strlen($telefono) >= 9
            && strlen($telefono) <= 15
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

        return preg_match('/[A-Z]/', $password)
            && preg_match('/[a-z]/', $password)
            && preg_match('/[0-9]/', $password)
            && preg_match('/[^A-Za-z0-9]/', $password);
    }
}
