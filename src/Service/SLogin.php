<?php

namespace CamassoMedelago\DriveMeSafely\Service;

use CamassoMedelago\DriveMeSafely\Entity\EUtenteRegistrato;
use CamassoMedelago\DriveMeSafely\Foundation\FUtenteRegistrato;
use CamassoMedelago\DriveMeSafely\Utils\PasswordUtil;
use Doctrine\ORM\EntityManagerInterface;

class SLogin
{
    private FUtenteRegistrato $fUtente;

    public function __construct(EntityManagerInterface $em)
    {
        $this->fUtente = new FUtenteRegistrato($em);
    }

    public function autentica(
        string $username,
        string $password
    ): EUtenteRegistrato {
        $utente = $this->fUtente->getByUsername(trim($username));

        if (
            $utente === null
            || !$utente->isStatoUtente()
            || !PasswordUtil::verifyPassword(
                $password,
                $utente->getPassword()
            )
        ) {
            throw new \InvalidArgumentException(
                'Username o password errati, oppure account disattivato.'
            );
        }

        return $utente;
    }
}
