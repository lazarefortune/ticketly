<?php

namespace App\Http\Controller;

use App\Domain\Auth\Entity\User;
use App\Domain\Event\Repository\ReservationRepository;
use App\Domain\Password\Form\UpdatePasswordForm;
use App\Domain\Profile\Dto\ProfileUpdateData;
use App\Domain\Profile\Exception\TooManyEmailChangeException;
use App\Domain\Profile\Form\DeleteAccountForm;
use App\Domain\Profile\Form\UserUpdateForm;
use App\Domain\Profile\Service\DeleteAccountService;
use App\Domain\Profile\Service\ProfileService;
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
        private readonly ProfileService              $profileService,
        private readonly DeleteAccountService        $deleteAccountService,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ReservationRepository       $reservationRepository,
        private readonly StripeService               $stripeService,
    )
    {
    }

    #[Route( '/', name: 'profile' )]
    #[isGranted( 'IS_AUTHENTICATED_FULLY' )]
    public function index( Request $request ) : Response
    {
        [$formProfile, $response] = $this->createFormProfile( $request );

        $user = $this->getUserOrThrow();

        if ( $response ) {
            return $response;
        }

        [$formDeleteAccount, $response] = $this->createFormDeleteAccount( $request );

        if ( $response ) {
            return $response;
        }

        // Check if user request email change
        $requestEmailChange = $this->profileService->getLatestValidEmailVerification( $user );

        $reservations = $this->reservationRepository->findBy( ['email' => $user->getEmail()], ['createdAt' => 'DESC'] );

        return $this->render( 'pages/public/account/index.html.twig', [
            'formProfile' => $formProfile->createView(),
            'formDeleteAccount' => $formDeleteAccount->createView(),
            'requestEmailChange' => $requestEmailChange,
            'reservations' => $reservations,
            'invoices' => array(),
        ] );
    }

    #[Route( '/changer-mot-de-passe', name: 'change_password' )]
    public function changePassword( Request $request ) : Response
    {
        [$formPassword, $response] = $this->createFormPassword( $request );

        if ( $response ) {
            return $response;
        }

        return $this->render('pages/public/account/change-password.html.twig',[
            'formPassword' => $formPassword->createView(),
        ] );
    }

    private function createFormProfile( Request $request ) : array
    {
        $user = $this->getUserOrThrow();
        $form = $this->createForm( UserUpdateForm::class, new ProfileUpdateData( $user ) );

        $form->handleRequest( $request );
        try {
            if ( $form->isSubmitted() && $form->isValid() ) {
                $data = $form->getData();
                $this->profileService->updateProfile( $data );

                if ( $data->email !== $user->getEmail() ) {
                    $this->addFlash( 'success', 'Vous allez recevoir un email pour confirmer votre nouvelle adresse email' );
                } else {
                    $this->addFlash( 'success', 'Informations mises à jour avec succès' );
                }

                return [$form, $this->redirectToRoute( 'app_account_profile' )];
            }
        } catch ( TooManyEmailChangeException ) {
            $this->addFlash( 'danger', 'Vous avez déjà demandé un changement d\'email, veuillez patienter avant de pouvoir en faire un nouveau' );
        }

        return [$form, null];
    }

    private function createFormPassword( Request $request ) : array
    {
        $user = $this->getUserOrThrow();
        $form = $this->createForm( UpdatePasswordForm::class );

        $form->handleRequest( $request );
        if ( $form->isSubmitted() && $form->isValid() ) {
            // Verify current password
            $data = $form->getData();
            if ( !$this->passwordHasher->isPasswordValid( $user, $data['currentPassword'] ) ) {
                $form->get( 'currentPassword' )->addError( new FormError( 'Mot de passe actuel invalide' ) );
                return [$form, null];
            }

            $this->profileService->updatePassword( $user, $data['newPassword'] );
            $this->addFlash( 'success', 'Mot de passe mis à jour avec succès' );
            return [$form, $this->redirectToRoute( 'app_account_profile' )];
        }

        return [$form, null];
    }

    private function createFormDeleteAccount( Request $request ) : array
    {
        $user = $this->getUserOrThrow();
        $form = $this->createForm( DeleteAccountForm::class );

        $form->handleRequest( $request );
        if ( $form->isSubmitted() && $form->isValid() ) {

            $data = $form->getData();
            if ( !$this->passwordHasher->isPasswordValid( $user, $data['password'] ) ) {
                $this->addFlash( 'error', 'Impossible de supprimer votre compte, mot de passe invalide' );
                return [$form, $this->redirectToRoute( 'app_account_profile' )];
            }

            try {
                $this->deleteAccountService->deleteAccountRequest( $user, $request );
            } catch ( \LogicException $e ) {
                $this->addFlash( 'error', $e->getMessage() );
                return [$form, $this->redirectToRoute( 'app_account_profile' )];
            }

            $this->addFlash( 'info', 'Votre demande de suppression de compte a bien été prise en compte' );

            return [$form, $this->redirectToRoute( 'app_account_profile' )];
        }

        return [$form, null];
    }

    #[Route( '/annuler-suppression-compte', name: 'cancel_deletion' )]
    public function cancelAccountDeletion( Request $request ) : Response
    {
        $user = $this->getUserOrThrow();
        $this->deleteAccountService->cancelAccountDeletionRequest( $user );
        $this->addFlash( 'success', 'Votre demande de suppression de compte a bien été annulée' );
        return $this->redirectToRoute( 'app_account_profile' );
    }


    #[Route('/stripe/connect', name: 'stripe_connect')]
    public function connect(): RedirectResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        // check if user is verified
        if (!$user->isVerified()) {
            $this->addFlash('error', 'Vous devez vérifier votre adresse email avant de pouvoir connecter votre compte Stripe.');
            return $this->redirectToRoute('app_account_profile');
        }

        if ($user->getStripeAccountId() === null || $user->isStripeAccountCompleted() === false) {
            // Crée le lien de connexion Stripe
            $url = $this->stripeService->createAccountLink( $user );

            return $this->redirect($url);
        }

        // L'utilisateur a déjà un compte Stripe
        return $this->redirectToRoute('app_account_profile');
    }

    #[Route('/stripe/dashboard', name: 'stripe_dashboard')]
    public function stripeDashboard(): RedirectResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user->getStripeAccountId()) {
            $this->addFlash('error', 'Vous n\'avez pas encore connecté votre compte Stripe.');
            return $this->redirectToRoute('account_profile');
        }

        // Crée le lien vers le tableau de bord Stripe
        $dashboardUrl = $this->stripeService->createDashboardLink($user);

        return $this->redirect($dashboardUrl);
    }
}
