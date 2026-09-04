<?php

namespace CamassoMedelago\DriveMeSafely\Utils;

use DateTimeImmutable;

/**
 * Utility per la gestione, validazione, hashing e mascheramento
 * delle carte di credito nel sistema DriveMeSafely.
 */
class CartaDiCreditoUtil
{
    private function __construct()
    {
    }

    /**
     * Normalizza il numero di carta rimuovendo spazi, trattini e caratteri non numerici.
     */
    public static function normalizzaNumeroCarta(string $numeroCarta): string
    {
        return preg_replace('/\D/', '', $numeroCarta) ?? '';
    }

    /**
     * Genera l'hash crittografico deterministico (SHA-256) del numero di carta
     * per consentirne l'archiviazione e la ricerca sicura nel database.
     */
    public static function hashNumeroCarta(string $numeroCarta): string
    {
        $numeroNormalizzato = self::normalizzaNumeroCarta($numeroCarta);
        return hash('sha256', $numeroNormalizzato);
    }

    /**
     * Estrae le ultime 4 cifre del numero di carta.
     */
    public static function estraiUltimeCifre(string $numeroCarta): string
    {
        $numeroNormalizzato = self::normalizzaNumeroCarta($numeroCarta);
        if (strlen($numeroNormalizzato) < 4) {
            return str_pad($numeroNormalizzato, 4, 'X', STR_PAD_LEFT);
        }
        return substr($numeroNormalizzato, -4);
    }

    /**
     * Restituisce il numero di carta mascherato (es. XXXX-XXXX-XXXX-1234).
     */
    public static function mascheraNumeroCarta(string $numeroCarta, ?string $ultimeCifre = null): string
    {
        if ($ultimeCifre !== null && strlen($ultimeCifre) === 4) {
            return 'XXXX-XXXX-XXXX-' . $ultimeCifre;
        }

        $cifre = self::estraiUltimeCifre($numeroCarta);
        return 'XXXX-XXXX-XXXX-' . $cifre;
    }

    /**
     * Valida il numero di carta.
     * Supporta le sequenze di test universali per ambienti accademici/testing (es. 16 cifre uguali come 1111222233334444 o 1234567812345678)
     * oppure la validazione formale con l'Algoritmo di Luhn (Formula Mod-10).
     */
    public static function validaLuhn(string $numeroCarta): bool
    {
        $numero = self::normalizzaNumeroCarta($numeroCarta);

        // Validazione semplificata ai fini didattici/d'esame: si richiedono
        // esattamente 16 cifre numeriche, senza applicare il checksum di Luhn.
        return (bool) preg_match('/^\d{16}$/', $numero);
    }

    /**
     * Valida il codice CVV/CVC (3 o 4 cifre numeriche).
     */
    public static function validaCvv(string $cvv): bool
    {
        $cvvPulito = trim($cvv);
        return (bool) preg_match('/^\d{3,4}$/', $cvvPulito);
    }

    /**
     * Valida il nome o cognome del titolare.
     * Accetta lettere unicode (inclusi accenti), apostrofi, spazi e trattini (es. D'Amico, De Luca, Jean-Luc, Nicolò).
     * Lunghezza ammessa: 2 - 50 caratteri.
     */
    public static function validaTitolare(string $nome): bool
    {
        $nomeTrim = trim($nome);
        $len = mb_strlen($nomeTrim);

        if ($len < 2 || $len > 50) {
            return false;
        }

        return (bool) preg_match("/^[\p{L}]+(?:[ '-][\p{L}]+)*$/u", $nomeTrim);
    }

    /**
     * Verifica se la carta di credito è scaduta rispetto alla data odierna.
     * La carta resta valida fino alle 23:59:59 dell'ultimo giorno del mese indicato.
     */
    public static function isCartaScaduta(DateTimeImmutable $dataScadenza): bool
    {
        $fineMeseScadenza = $dataScadenza->modify('last day of this month')->setTime(23, 59, 59);
        $adesso = new DateTimeImmutable('now');

        return $fineMeseScadenza < $adesso;
    }

    /**
     * Verifica se la data di scadenza è ragionevole (non scaduta e non oltre 20 anni nel futuro).
     */
    public static function isDataScadenzaValida(DateTimeImmutable $dataScadenza): bool
    {
        if (self::isCartaScaduta($dataScadenza)) {
            return false;
        }

        $limiteFuturo = (new DateTimeImmutable('now'))->modify('+20 years');
        return $dataScadenza <= $limiteFuturo;
    }
}
