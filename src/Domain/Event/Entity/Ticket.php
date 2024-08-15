<?php

namespace App\Domain\Event\Entity;

use App\Domain\Event\Repository\TicketRepository;
use App\Domain\Payment\Entity\Payment;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: TicketRepository::class)]
class Ticket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    #[Assert\NotBlank(message: "Le numéro de ticket ne doit pas être vide.")]
    #[Assert\Length(min: 1, max: 255)]
    private ?string $ticketNumber = null;

    #[ORM\ManyToOne(targetEntity: Reservation::class, inversedBy: 'tickets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Reservation $reservation = null;

    #[ORM\ManyToOne(targetEntity: Event::class, inversedBy: 'tickets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $event = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $qrCodeName = null;

    #[Vich\UploadableField(mapping: 'ticket_qr_codes', fileNameProperty: 'qrCodeName')]
    private ?File $qrCodeFile = null;


    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $buyAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $expiresAt = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isUsed = false;

    public function __construct()
    {
        $this->ticketNumber = $this->generateTicketNumber();
        $this->createdAt = new \DateTime();
    }

    private function generateTicketNumber(): string
    {
        $datePart = (new \DateTime())->format('ymi');

        $uniquePart = strtoupper(substr(base_convert(mt_rand(1000, 99999999), 10, 36), 0, 4));

        return sprintf('TCK-%s-%s', $datePart, $uniquePart);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTicketNumber(): ?string
    {
        return $this->ticketNumber;
    }

    public function setTicketNumber(string $ticketNumber): self
    {
        $this->ticketNumber = $ticketNumber;
        return $this;
    }

    public function getReservation(): ?Reservation
    {
        return $this->reservation;
    }

    public function setReservation(?Reservation $reservation): self
    {
        $this->reservation = $reservation;
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

    public function getBuyAt(): ?DateTimeInterface
    {
        return $this->buyAt;
    }

    public function setBuyAt(?DateTimeInterface $buyAt): self
    {
        $this->buyAt = $buyAt;
        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getExpiresAt(): ?DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?DateTimeInterface $expiresAt): self
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    public function isUsed(): bool
    {
        return $this->isUsed;
    }

    public function setUsed(bool $isUsed): self
    {
        $this->isUsed = $isUsed;
        return $this;
    }

    public function setQrCodeFile(?File $qrCodeFile = null): void
    {
        $this->qrCodeFile = $qrCodeFile;
        if ($qrCodeFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getQrCodeFile(): ?File
    {
        return $this->qrCodeFile;
    }

    public function setQrCodeName(?string $qrCodeName): self
    {
        $this->qrCodeName = $qrCodeName;
        return $this;
    }

    public function getQrCodeName(): ?string
    {
        return $this->qrCodeName;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * Check if the ticket is valid
     * The ticket is valid if:
     * - It has a reservation
     * - The reservation has a payment
     * - The payment status is success
     * - The event is not ended
     * - The ticket is not used
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->getReservation() !== null
            && $this->getReservation()->getPayment() !== null
            && $this->getReservation()->getPayment()->getStatus() === Payment::STATUS_SUCCESS
            && new \DateTime() <= $this->getEvent()->getEndDate()
            && !$this->isUsed();
    }
}
