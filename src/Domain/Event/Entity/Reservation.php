<?php

namespace App\Domain\Event\Entity;

use App\Domain\Auth\Entity\User;
use App\Domain\Coupon\Entity\Coupon;
use App\Domain\Event\Repository\ReservationRepository;
use App\Domain\Payment\Entity\Payment;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public const SERVICE_CHARGE_PERCENTAGE = 5; // Frais de service en pourcentage

    public const STATUS_PENDING = 'pending';
    public const STATUS_SUCCESS = 'success';

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $reservationNumber;

    #[ORM\ManyToOne(targetEntity: Event::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $event = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    #[ORM\Column(type: 'integer')]
    private int $quantity;

    #[ORM\Column(type: 'integer')]
    private int $unitPrice;

    #[ORM\Column(type: 'integer')]
    private int $discountAmount = 0;

    #[ORM\Column(type: 'integer')]
    private int $subTotal;

    #[ORM\Column(type: 'integer')]
    private int $serviceCharge;

    #[ORM\Column(type: 'integer')]
    private int $totalAmount;

    #[ORM\Column(type: 'integer')]
    private int $netIncome;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $expiresAt;

    #[ORM\Column(type: 'string', length: 255)]
    private string $status = 'pending';

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: "L'email ne doit pas être vide.")]
    #[Assert\Email(message: "L'email doit être valide.")]
    private ?string $email = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\Length(
        min: 10,
        max: 15,
        minMessage: "Le numéro de téléphone doit comporter au moins {{ limit }} caractères.",
        maxMessage: "Le numéro de téléphone ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $phoneNumber = null;

    #[ORM\Column(type: 'string')]
    #[Assert\NotBlank(message: "Le nom ne doit pas être vide.")]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'reservation', targetEntity: Ticket::class, cascade: ['persist', 'remove'])]
    private Collection $tickets;

    #[ORM\OneToOne(mappedBy: 'reservation', targetEntity: Payment::class, cascade: ['persist', 'remove'])]
    private ?Payment $payment = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTimeInterface $buyAt = null;

    #[ORM\ManyToOne(targetEntity: Coupon::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Coupon $coupon = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTimeInterface $updatedAt = null;

    public function __construct(Event $event, int $quantity, int $unitPrice, int $discountAmount = 0, ?string $email = '', ?string $name = '', ?string $phoneNumber = null)
    {
        $this->event = $event;
        $this->quantity = $quantity;
        $this->unitPrice = $unitPrice;
        $this->discountAmount = $discountAmount;
        $this->email = $email;
        $this->name = $name;
        $this->phoneNumber = $phoneNumber;
        $this->expiresAt = (new \DateTime())->modify('+10 minutes');
        $this->reservationNumber = $this->generateUniqueReservationNumber();
        $this->tickets = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();

        $this->calculateAmounts();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    private function generateUniqueReservationNumber(): string
    {
        $randomString = uniqid(mt_rand(), true);

        $shortHash = substr(hash('sha256', $randomString), 0, 8);

        return sprintf('RSV-%s', strtoupper($shortHash));
    }

    public function getReservationNumber(): string
    {
        return $this->reservationNumber;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getUnitPrice(): int
    {
        return $this->unitPrice;
    }

    public function getDiscountAmount(): int
    {
        return $this->discountAmount;
    }

    public function getSubTotal(): int
    {
        return $this->subTotal;
    }

    public function getServiceCharge(): int
    {
        return $this->serviceCharge;
    }

    public function getTotalAmount(): int
    {
        return $this->totalAmount;
    }

    public function getNetIncome(): int
    {
        return $this->netIncome;
    }

    public function getExpiresAt(): DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function isExpired(): bool
    {
        return new \DateTime() > $this->expiresAt;
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isPaid(): bool
    {
        return $this->status === 'success';
    }

    public function isCancelled(): bool
    {
        return $this->isExpired() && $this->isPending();
    }

    public function isValid(): bool
    {
        return $this->isPaid();
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setPhoneNumber(string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getTickets(): Collection
    {
        return $this->tickets;
    }

    public function addTicket(Ticket $ticket): self
    {
        if (!$this->tickets->contains($ticket)) {
            $this->tickets[] = $ticket;
            $ticket->setReservation($this);
        }

        return $this;
    }

    public function removeTicket(Ticket $ticket): self
    {
        if ($this->tickets->removeElement($ticket)) {
            if ($ticket->getReservation() === $this) {
                $ticket->setReservation(null);
            }
        }

        return $this;
    }

    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    public function setPayment(Payment $payment): self
    {
        if ($this->payment !== $payment) {
            $this->payment = $payment;
            if ($payment->getReservation() !== $this) {
                $payment->setReservation($this);
            }
        }

        return $this;
    }

    public function getBuyAt(): ?DateTimeInterface
    {
        return $this->buyAt;
    }

    public function setBuyAt(DateTimeInterface $buyAt): self
    {
        $this->buyAt = $buyAt;
        return $this;
    }

    public function getCoupon(): ?Coupon
    {
        return $this->coupon;
    }

    public function setCoupon(?Coupon $coupon): self
    {
        $this->coupon = $coupon;
        return $this;
    }

    public function applyCoupon(Coupon $coupon): self
    {
        if ($coupon->isActive() && new \DateTime() <= $coupon->getExpiresAt()) {
            $this->coupon = $coupon;
            $this->calculateAmounts();
        }

        return $this;
    }

    public function removeCoupon(): self
    {
        $this->coupon = null;
        // Remettre le montant de la réduction à zéro
        $this->discountAmount = 0;
        $this->calculateAmounts();
        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    private function calculateAmounts(): void
    {
        $this->subTotal = $this->calculateSubTotal();
        $this->serviceCharge = $this->calculateServiceCharge();

        // Appliquer la réduction si un coupon est présent
        if ($this->coupon) {
            if ($this->coupon->getTypeCoupon() === Coupon::TYPE_PERCENTAGE) {
                $this->discountAmount = (int)($this->subTotal * ($this->coupon->getValueCoupon() / 100));
            } elseif ($this->coupon->getTypeCoupon() === Coupon::TYPE_FIXED) {
                // La réduction fixe ne peut pas dépasser le sous-total
                $this->discountAmount = min($this->coupon->getValueCoupon(), $this->subTotal);
            }
        }

        // Calcul du montant total après application des réductions et frais de service
        $this->totalAmount = $this->subTotal + $this->serviceCharge - $this->discountAmount;

        // Empêcher que le montant total ne soit négatif
        if ($this->totalAmount < 0) {
            $this->totalAmount = 0;
        }

        // Calcul du montant net que l'organisateur perçoit
        $this->netIncome = $this->totalAmount - $this->serviceCharge;
    }


    private function calculateSubTotal(): int
    {
        // Calcule le sous-total avant frais de service et après réduction
        return ($this->quantity * $this->unitPrice) - $this->discountAmount;
    }

    private function calculateServiceCharge(): int
    {
        return $this->subTotal * self::SERVICE_CHARGE_PERCENTAGE / 100;
    }
}
