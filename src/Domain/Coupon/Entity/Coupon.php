<?php

namespace App\Domain\Coupon\Entity;

use App\Domain\Coupon\Repository\CouponRepository;
use App\Domain\Event\Entity\Event;
use App\Domain\Event\Entity\Reservation;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: CouponRepository::class)]
class Coupon
{
    public const TYPE_PERCENTAGE = 'percentage';
    public const TYPE_FIXED = 'fixed';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $code = '';

    #[ORM\Column(type: 'string', length: 20)]
    private string $typeCoupon = '';

    #[ORM\Column(type: 'integer')]
    private int $valueCoupon = 0;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $expiresAt = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;

    #[ORM\OneToMany(mappedBy: 'coupon', targetEntity: Reservation::class)]
    private Collection $reservations;

    #[ORM\ManyToOne(targetEntity: Event::class, inversedBy: 'coupons')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $event = null;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;
        return $this;
    }

    public function getTypeCoupon(): string
    {
        return $this->typeCoupon;
    }

    public function setTypeCoupon(string $typeCoupon): self
    {
        $this->typeCoupon = $typeCoupon;
        return $this;
    }

    public function getValueCoupon(): int
    {
        return $this->valueCoupon;
    }

    public function setValueCoupon(int $valueCoupon): self
    {
        $this->valueCoupon = $valueCoupon;
        return $this;
    }

    public function getExpiresAt(): ?\DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?\DateTimeInterface $expiresAt): self
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): self
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations[] = $reservation;
            $reservation->setCoupon($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getCoupon() === $this) {
                $reservation->setCoupon(null);
            }
        }

        return $this;
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
}
