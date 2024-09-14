<?php
namespace App\Domain\Event\Entity;

use App\Domain\Auth\Entity\User;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class EventCollaborator
{
    public const ROLE_MANAGER = 'manager';
    public const ROLE_FINANCE = 'finance';
    public const ROLE_COUPONS = 'coupons';
    public const ROLE_RESERVATIONS = 'reservations';
    public const ROLE_TICKETS = 'tickets';
    public const ROLE_MANAGE_EVENT = 'manage_event';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Event::class, inversedBy: 'collaborators')]
    private ?Event $event = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'collaborations')]
    private ?User $collaborator = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): self
    {
        $this->event = $event;
        return $this;
    }

    public function getCollaborator(): ?User
    {
        return $this->collaborator;
    }

    public function setCollaborator(?User $collaborator): self
    {
        $this->collaborator = $collaborator;
        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles, true);
    }
}
