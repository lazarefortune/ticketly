<?php
namespace App\Domain\Coupon\Voter;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Event\Entity\Event;
use App\Domain\Event\Entity\EventCollaborator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CouponVoter extends Voter
{
    public const COUPON_MANAGE = 'COUPON_MANAGE';

    protected function supports(string $attribute, $subject): bool
    {
        return $attribute === self::COUPON_MANAGE && $subject instanceof Event;
    }

    protected function voteOnAttribute( string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Event $event */
        $event = $subject;

        // Si l'utilisateur est l'organisateur
        if ($event->getOrganizer() === $user) {
            return true;
        }

        // Si l'utilisateur est un collaborateur avec le rôle nécessaire
        /** @var EventCollaborator $collaborator */
        foreach ( $event->getCollaborators() as $collaborator) {
            if ($collaborator->getCollaborator() === $user && $collaborator->hasRole(EventCollaborator::ROLE_COUPONS)) {
                return true;
            }
        }

        return false;
    }
}
