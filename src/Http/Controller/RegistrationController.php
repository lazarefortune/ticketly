<?php

namespace App\Http\Controller;

use App\Domain\Auth\Dto\NewUserData;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Form\RegistrationForm;
use App\Domain\Login\Service\LoginService;
use App\Domain\Registration\Service\RegistrationService;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{

    public function __construct(
        private readonly RegistrationService        $registrationService,
        private readonly LoginService               $loginService,
    )
    {
    }

    #[Route( '/inscription', name: 'register' )]
    public function registerUser( Request $request ) : Response
    {
        if ( $this->getUser() ) {
            return $this->redirectToRoute( 'app_home' );
        }
        
        $form = $this->createForm( RegistrationForm::class, new NewUserData( new User() ) );
        $form->handleRequest( $request );

        if ( $form->isSubmitted() && $form->isValid() ) {
            $user = $this->registrationService->createUser( $form->getData() );
            try {
                return $this->loginService->authenticateUser( $user, $request );
            } catch ( Exception $e ) {
                $this->addFlash( 'danger', $e->getMessage() );
                return $this->redirectToRoute( 'app_login' );
            }
        }

        return $this->render( 'pages/auth/register.html.twig', [
            'registrationForm' => $form->createView(),
        ] );
    }

    #[Route( '/email/validation', name: 'verify_email' )]
    public function validateEmail( Request $request ) : Response
    {
        $userId = $request->get( 'id' );
        $uri = $request->getUri();

        try {
            $this->registrationService->validateUser( $userId , $uri );
        } catch ( Exception $e ) {
            $this->addFlash( 'danger', $e->getMessage() );
            return $this->redirectToRoute( 'app_register' );
        } catch ( VerifyEmailExceptionInterface $e ) {
            $this->addFlash( 'danger', $e->getReason() );
            return $this->redirectToRoute( 'app_register' );
        }

        $this->addFlash('info', 'Votre compte a été activé avec succès');
        return $this->redirectToRoute('app_login');
    }

}

