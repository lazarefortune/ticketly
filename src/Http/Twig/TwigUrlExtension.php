<?php

namespace App\Http\Twig;

use App\Domain\Auth\Entity\User;
use App\Domain\Event\Entity\Event;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Vich\UploaderBundle\Templating\Helper\UploaderHelperInterface;

class TwigUrlExtension extends AbstractExtension
{
    public function __construct(
        private readonly UploaderHelperInterface $uploaderHelper
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('avatar', $this->avatarPath(...)),
            new TwigFilter('event_image', $this->eventImagePath(...)),
        ];
    }

    public function avatarPath(User $user): string
    {
        if ($user->getAvatar() === null) {
            return '/images/avatars/default.jpg';
        }

        return $this->uploaderHelper->asset($user, 'avatarFile');
    }

    public function eventImagePath(Event $event): string
    {
        if ($event->getImage() === null) {
            return '/images/events/default.jpg';
        }

        return $this->uploaderHelper->asset($event, 'imageFile');
    }

}