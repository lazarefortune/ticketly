<?php

namespace App\Domain\Auth\Core\Entity;

use App\Domain\Auth\Core\Repository\UserRepository;
use App\Domain\Auth\Registration\Entity\EmailVerification;
use App\Domain\Event\Entity\Event;
use App\Domain\Event\Entity\EventCollaborator;
use App\Domain\Event\Entity\Reservation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity( repositoryClass: UserRepository::class )]
#[ORM\Table( name: 'user' )]
#[Vich\Uploadable]
#[UniqueEntity( fields: ['email'], message: 'There is already an account with this email' )]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const DAYS_FOR_PREVENT_DELETE_UNVERIFIED_USER = 4;
    public const DAYS_BEFORE_DELETE_UNVERIFIED_USER = 7;
    public const DAYS_FOR_PREVENT_DELETE_USER = 3;
    public const DAYS_BEFORE_DELETION = 5;

    public const DELETE_REASON = [
        'REQUESTED' => 'user-request',
        'SPAM' => 'email-unverified',
        'FAKE' => 'fake-account',
        'OTHER' => 'other-reason',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column( length: 180, unique: true )]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column( length: 255, nullable: true )]
    private ?string $fullname = null;

    #[ORM\Column( type: 'boolean' )]
    private bool $isVerified = false;

    #[ORM\Column( length: 255, nullable: true )]
    private ?string $phone = null;

    #[ORM\Column( type: Types::DATE_MUTABLE, nullable: true )]
    private ?\DateTimeInterface $date_of_birthday = null;

    #[ORM\Column( type: Types::STRING, length: 255, nullable: true )]
    private ?string $avatar = null;

    #[Vich\UploadableField( mapping: 'avatar_images', fileNameProperty: 'avatar' )]
    private ?File $avatarFile = null;

    #[ORM\Column( type: Types::DATETIME_MUTABLE, nullable: true )]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column( type: Types::DATETIME_MUTABLE, nullable: true )]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column( type: Types::DATETIME_MUTABLE, nullable: true )]
    private ?\DateTimeInterface $lastLogin = null;

    #[ORM\Column( type: Types::DATE_MUTABLE, nullable: true )]
    private ?\DateTimeInterface $deletedAt = null;

    #[ORM\OneToMany( mappedBy: 'author', targetEntity: EmailVerification::class, orphanRemoval: true )]
    private Collection $emailVerifications;

    #[ORM\Column]
    private ?bool $cgu = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Reservation::class)]
    private Collection $reservations;

    #[ORM\Column( nullable: true )]
    private ?bool $isRequestDelete = null;

    #[ORM\Column( type: Types::TEXT, nullable: true )]
    private ?string $stripeId = null;

    #[ORM\Column( type: Types::STRING, nullable: true )]
    private ?string $stripeAccountId = null;

    #[ORM\Column( type: Types::BOOLEAN )]
    private bool $stripeAccountCompleted = false;

    #[ORM\Column( type: Types::STRING, nullable: true )]
    private ?string $country = 'FR';

    #[ORM\OneToMany(mappedBy: 'organizer', targetEntity: Event::class)]
    private Collection $events;

    #[ORM\OneToMany(mappedBy: 'collaborator', targetEntity: EventCollaborator::class)]
    private Collection $collaborations;

    #[ORM\Column( type: Types::STRING, nullable: true )]
    private ?string $apiKey = null;

    public function __construct()
    {
        $this->fullname = '';
        $this->email = '';
        $this->phone = '';
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->emailVerifications = new ArrayCollection();
        $this->reservations = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->collaborations = new ArrayCollection();
    }

    public function getId() : ?int
    {
        return $this->id;
    }

    public function getEmail() : ?string
    {
        return $this->email;
    }

    public function setEmail( string $email ) : self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier() : string
    {
        return (string)$this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles() : array
    {
        $roles = $this->roles;

        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique( $roles );
    }

    public function setRoles( array $roles ) : self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword() : string
    {
        return $this->password;
    }

    public function setPassword( string $password ) : self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials() : void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFullname() : ?string
    {
        return $this->fullname;
    }

    public function setFullname( ?string $fullname ) : self
    {
        $this->fullname = $fullname;

        return $this;
    }

    public function isVerified() : bool
    {
        return $this->isVerified;
    }

    public function setIsVerified( bool $isVerified ) : self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getPhone() : ?string
    {
        return $this->phone;
    }

    public function setPhone( ?string $phone ) : self
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setOrganizer($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->removeElement($event)) {
            // Set the owning side to null (unless already changed)
            if ($event->getOrganizer() === $this) {
                $event->setOrganizer(null);
            }
        }

        return $this;
    }

    public function getCollaborations(): Collection
    {
        return $this->collaborations;
    }

    public function addCollaboration(EventCollaborator $collaboration): self
    {
        if (!$this->collaborations->contains($collaboration)) {
            $this->collaborations[] = $collaboration;
            $collaboration->setCollaborator($this);
        }

        return $this;
    }

    public function removeCollaboration(EventCollaborator $collaboration): self
    {
        $this->collaborations->removeElement($collaboration);

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getCreatedAt() : ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getUpdatedAt() : ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getDeletedAt() : ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setCreatedAt( ?\DateTimeInterface $createdAt ) : self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function setUpdatedAt( ?\DateTimeInterface $updatedAt ) : self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function setDeletedAt( ?\DateTimeInterface $deletedAt ) : self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function getLastLogin(): ?\DateTimeInterface
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?\DateTimeInterface $lastLogin): self
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }


    public function getDateOfBirthday() : ?\DateTimeInterface
    {
        return $this->date_of_birthday;
    }

    public function setDateOfBirthday( ?\DateTimeInterface $date_of_birthday ) : self
    {
        $this->date_of_birthday = $date_of_birthday;

        return $this;
    }

    public function getAvatar() : ?string
    {
        return $this->avatar;
    }

    public function setAvatar( ?string $avatar ) : self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getAvatarFile() : ?File
    {
        return $this->avatarFile;
    }

    public function setAvatarFile( ?File $avatarFile ) : self
    {
        $this->avatarFile = $avatarFile;

        if ( $avatarFile ) {
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function __sleep() : array
    {
        $vars = get_object_vars( $this );
        $excluded = ['avatarFile']; //propriétés à exclure.
        $result = [];
        foreach ( array_keys( $vars ) as $key ) {
            if ( !in_array( $key, $excluded ) && isset( $this->$key ) ) {
                $result[] = $key;
            }
        }
        return $result;
    }

    public function __wakeup()
    {
        $this->avatarFile = null;
    }

    /**
     * @return Collection<int, EmailVerification>
     */
    public function getEmailVerifications() : Collection
    {
        return $this->emailVerifications;
    }

    public function addEmailVerification( EmailVerification $emailVerification ) : self
    {
        if ( !$this->emailVerifications->contains( $emailVerification ) ) {
            $this->emailVerifications->add( $emailVerification );
            $emailVerification->setAuthor( $this );
        }

        return $this;
    }

    public function removeEmailVerification( EmailVerification $emailVerification ) : self
    {
        if ( $this->emailVerifications->removeElement( $emailVerification ) ) {
            // set the owning side to null (unless already changed)
            if ( $emailVerification->getAuthor() === $this ) {
                $emailVerification->setAuthor( null );
            }
        }

        return $this;
    }

    public function isCgu() : ?bool
    {
        return $this->cgu;
    }

    public function setCgu( bool $cgu ) : static
    {
        $this->cgu = $cgu;

        return $this;
    }

    public function isIsRequestDelete() : ?bool
    {
        return $this->isRequestDelete;
    }

    public function setIsRequestDelete( ?bool $isRequestDelete ) : static
    {
        $this->isRequestDelete = $isRequestDelete;

        return $this;
    }

    public function getStripeId() : ?string
    {
        return $this->stripeId;
    }

    public function setStripeId( ?string $stripeId ) : static
    {
        $this->stripeId = $stripeId;

        return $this;
    }

    public function getStripeAccountId() : ?string
    {
        return $this->stripeAccountId;
    }

    public function setStripeAccountId( ?string $stripeAccountId ) : static
    {
        $this->stripeAccountId = $stripeAccountId;

        return $this;
    }

    public function isStripeAccountCompleted() : bool
    {
        return $this->stripeAccountCompleted;
    }

    public function setStripeAccountCompleted( bool $stripeAccountCompleted ) : static
    {
        $this->stripeAccountCompleted = $stripeAccountCompleted;

        return $this;
    }

    public function getCountry() : ?string
    {
        return $this->country;
    }

    public function setCountry( ?string $country ) : static
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): self
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setUser($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getUser() === $this) {
                $reservation->setUser(null);
            }
        }

        return $this;
    }

    public function getApiKey() : ?string
    {
        return $this->apiKey;
    }

    public function setApiKey( ?string $apiKey ) : User
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    public function isPremium() : bool
    {
        return true;
    }

//    public function __sleep()
//    {
//        return array_diff(array_keys(get_object_vars($this)), ['avatarFile']);
//    }
}
