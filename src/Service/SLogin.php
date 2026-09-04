<?php

namespace CamassoMedelago\DriveMeSafely\Service;

use CamassoMedelago\DriveMeSafely\Entity\EUtenteRegistrato;
use CamassoMedelago\DriveMeSafely\Foundation\FUtenteRegistrato;
use CamassoMedelago\DriveMeSafely\Utils\PasswordUtil;
use Doctrine\ORM\EntityManagerInterface;

//Service che implementa la logica di autenticazione per gli utenti registrati
class SLogin
{
    private FUtenteRegistrato $fUtente;

    public function __construct(EntityManagerInterface $em)
    {
        $this->fUtente = new FUtenteRegistrato($em);
    }

    //Metodo che autentica un utente registrato tramite username e password
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
