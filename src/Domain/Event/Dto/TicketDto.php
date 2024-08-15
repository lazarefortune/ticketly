<?php

namespace App\Domain\Event\Dto;

use App\Domain\Event\Entity\Ticket;
use Symfony\Component\Validator\Constraints as Assert;

class TicketDto
{

    public string $name;

    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email;

    #[Assert\NotBlank]
    #[Assert\Length(min: 10, max: 10)]
    public string $phoneNumber;

    public function __construct(Ticket $ticket = null)
    {
        if ($ticket) {
            $this->name = $ticket->getName();
            $this->email = $ticket->getEmail();
            $this->phoneNumber = $ticket->getPhoneNumber();
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }
}