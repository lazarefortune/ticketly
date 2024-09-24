<?php

namespace App\Domain\Login\Service;

use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Security\AppAuthenticator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Exception;

class LoginService
{
    public function __construct(
        private readonly UserAuthenticatorInterface $userAuthenticator,
        private readonly AppAuthenticator           $authenticator,
    )
    {
    }

    /**
     * @throws Exception
     */
    public function authenticateUser( User $user, Request $request ) : Response
    {
        $authenticationResponse = $this->userAuthenticator->authenticateUser(
            $user,
            $this->authenticator,
            $request
        );

        if ( !$authenticationResponse instanceof Response ) {
            throw new Exception( "L'authentification de l'utilisateur a échoué." );
        }

        return $authenticationResponse;
    }

    public function logout( Request $request = null ) : void
    {
        $request = $request ?: new Request();
        $request->getSession()->invalidate();
    }
}