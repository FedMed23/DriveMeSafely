<?php

namespace CamassoMedelago\DriveMeSafely\Utils;

class PasswordUtil
{
    private function __construct()
    {
    }
    /**
     * Genera l'hash della password.
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    /**
     * Verifica una password rispetto al suo hash.
     */
    public static function verifyPassword(
        string $password,
        string $hash
    ): bool {
        return password_verify($password, $hash);
    }
}