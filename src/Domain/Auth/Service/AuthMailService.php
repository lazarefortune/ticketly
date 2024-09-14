<?php

namespace App\Domain\Auth\Service;

use App\Domain\Auth\EmailVerifier;
use App\Domain\Auth\Entity\User;
use App\Infrastructure\Mailing\MailService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class AuthMailService
{
    public function __construct(
        private readonly MailService           $mailService,
        private readonly EmailVerifier         $emailVerifier,
        private readonly UrlGeneratorInterface $urlGenerator
    )
    {
    }

    /**
     * Send a welcome email to a new user
     *
     * @param User $user
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function sendWelcomeEmail( User $user ) : void
    {
        $signatureComponents = $this->emailVerifier->generateSignature( $user );

        $data = [
            'user' => $user,
            'signedUrl' => $signatureComponents->getSignedUrl(),
            'expiresAtMessageKey' => $signatureComponents->getExpirationMessageKey(),
            'expiresAtMessageData' => $signatureComponents->getExpirationMessageData()
        ];

        $email = $this->mailService->prepareEmail(
            $user->getEmail(),
            'Bienvenue sur ' . $_ENV['APP_NAME'],
            'mails/auth/welcome.twig',
            $data
        );

        $this->mailService->send( $email );
    }

    /**
     * Send an email to confirm the user's email address
     *
     * @param User $user
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function sendVerificationEmail( User $user ) : void
    {
        $signatureComponents = $this->emailVerifier->generateSignature( $user );

        $data = [
            'user' => $user,
            'signedUrl' => $signatureComponents->getSignedUrl(),
            'expiresAtMessageKey' => $signatureComponents->getExpirationMessageKey(),
            'expiresAtMessageData' => $signatureComponents->getExpirationMessageData()
        ];

        $email = $this->mailService->prepareEmail(
            $user->getEmail(),
            'Veuillez confirmer votre adresse email',
            'mails/auth/confirm-request.twig',
            $data
        );

        $this->mailService->send( $email );
    }

    /**
     * Send an email to confirm the user's email address
     *
     * @param User $user
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function sendVerificationSuccessEmail( User $user ) : void
    {

        $data = [
            'user' => $user,
            'loginUrl' => $this->urlGenerator->generate( 'app_login', [], UrlGeneratorInterface::ABSOLUTE_URL )
        ];

        $email = $this->mailService->prepareEmail(
            $user->getEmail(),
            'Votre compte est activé',
            'mails/auth/confirm-success.twig',
            $data
        );

        $this->mailService->send( $email );
    }

    /**
     * Send an email to reset the user's password
     *
     * @param User $user
     * @param string $token
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function sendResetPasswordEmail( User $user, string $token ) : void
    {
        $data = [
            'user' => $user,
            'resetUrl' => $this->urlGenerator->generate( 'app_reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL )
        ];

        $email = $this->mailService->prepareEmail(
            $user->getEmail(),
            'Réinitialisation de votre mot de passe',
            'mails/auth/reset-password.twig',
            $data
        );

        $this->mailService->send( $email );
    }
}