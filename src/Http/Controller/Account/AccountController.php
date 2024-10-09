<?php

namespace App\Http\Controller\Account;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Auth\Core\Exception\TooManyEmailChangeException;
use App\Domain\Auth\Core\Form\DeleteAccountForm;
use App\Domain\Auth\Core\Form\EmailUpdateForm;
use App\Domain\Auth\Core\Form\ProfileUpdateForm;
use App\Domain\Auth\Core\Form\UpdatePasswordForm;
use App\Domain\Auth\Core\Service\AccountService;
use App\Domain\Auth\Core\Service\DeleteAccountService;
use App\Domain\Auth\Core\Service\EmailChangeService;
use App\Http\Controller\AbstractController;
use App\Infrastructure\Payment\Stripe\StripeService;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route( '/mon-compte', name: 'account_' )]
#[IsGranted( 'ROLE_USER' )]
class AccountController extends AbstractController
{
    public function __construct(
        private readonly AccountService              $accountService,
        private readonly EmailChangeService          $emailChangeService,
        private readonly DeleteAccountService        $deleteAccountService,
        private readonly UserPasswordHasherInterface $passwordHasher,
    )
    {
    }

    #[Route( '/', name: 'profile' )]
    #[isGranted( 'IS_AUTHENTICATED_FULLY' )]
    public function index( Request $request ) : Response
    {
        // Profile update form
        $user = $this->getUserOrThrow();

        $formProfile = $this->createForm( ProfileUpdateForm::class , $user );
        $formProfile->handleRequest( $request );
        if ( $formProfile->isSubmitted() && $formProfile->isValid() ) {
            $data = $formProfile->getData();
            $this->accountService->updateProfile( $data );
            $this->addFlash( 'success', 'Informations mises à jour avec succès' );
        }

        // Email update form
        $formEmail = $this->createForm( EmailUpdateForm::class );
        $formEmail->handleRequest( $request );
        if ($formEmail->isSubmitted() && $formEmail->isValid()) {
            $data = $formEmail->getData();
            $newEmail = $data['email'];
            try {
                $this->emailChangeService->requestEmailChange($user, $newEmail);
                $this->addFlash('success', 'Vous allez recevoir un email pour confirmer votre nouvelle adresse email');
            } catch (\LogicException $e) {
                $formEmail->get('email')->addError(new FormError($e->getMessage()));
            } catch (TooManyEmailChangeException) {
                $this->addFlash('danger', 'Vous avez déjà demandé un changement d\'email, veuillez patienter avant de pouvoir en faire un nouveau');
            }
        }

        // latest email change request for the user
        $requestEmailChange = $this->emailChangeService->getLatestValidEmailVerification( $user );

        return $this->render( 'pages/public/account/index.html.twig', [
            'formProfile' => $formProfile->createView(),
            'formEmail'   => $formEmail->createView(),
            'requestEmailChange' => $requestEmailChange,
        ] );
    }

    #[Route( '/securite', name: 'security' )]
    public function security( Request $request ) : Response
    {
        $user = $this->getUserOrThrow();
        $formPassword = $this->createForm( UpdatePasswordForm::class );

        $formPassword->handleRequest( $request );
        if ( $formPassword->isSubmitted() && $formPassword->isValid() ) {
            // Verify current password
            $data = $formPassword->getData();
            if ( !$this->passwordHasher->isPasswordValid( $user, $data['currentPassword'] ) ) {
                $formPassword->get( 'currentPassword' )->addError( new FormError( 'Mot de passe actuel invalide' ) );
            } else {
                $this->accountService->updatePassword( $user, $data['newPassword'] );
                $this->addFlash( 'success', 'Mot de passe mis à jour avec succès' );
            }
        }

        return $this->render( 'pages/public/account/security.html.twig',[
            'formPassword' => $formPassword->createView(),
        ] );
    }

    #[Route( '/modification-mot-de-passe', name: 'change_password' )]
    public function changePassword( Request $request ) : Response
    {
        $user = $this->getUserOrThrow();
        $form = $this->createForm( UpdatePasswordForm::class );

        $form->handleRequest( $request );
        if ( $form->isSubmitted() && $form->isValid() ) {
            // Verify current password
            $data = $form->getData();
            if ( !$this->passwordHasher->isPasswordValid( $user, $data['currentPassword'] ) ) {
                $form->get( 'currentPassword' )->addError( new FormError( 'Mot de passe actuel invalide' ) );
            } else {
                $this->accountService->updatePassword( $user, $data['newPassword'] );
                $this->addFlash( 'success', 'Mot de passe mis à jour avec succès' );
            }
        }

        return $this->render('pages/public/account/change-password.html.twig',[
            'formPassword' => $form->createView(),
        ] );
    }

    #[Route( '/annuler-suppression-compte', name: 'cancel_deletion' )]
    public function cancelAccountDeletion( Request $request ) : Response
    {
        $user = $this->getUserOrThrow();
        $this->deleteAccountService->cancelAccountDeletionRequest( $user );
        $this->addFlash( 'success', 'Votre demande de suppression de compte a bien été annulée' );
        return $this->redirectToRoute( 'app_account_profile' );
    }
}
