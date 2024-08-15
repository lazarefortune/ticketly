<?php

namespace App\Http\Admin\Data\Crud;

use App\Http\Admin\Data\AutomaticCrudData;
use App\Validator\Slug;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class EventCrudData extends AutomaticCrudData
{
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank(message: "Le nom ne doit pas être vide.")]
    public ?string $name = '';

    #[Assert\NotBlank(message: "La description ne doit pas être vide.")]
    public ?string $description = '';

    #[Slug]
    #[Assert\NotBlank]
    public ?string $slug = null;

    public ?bool $isActive = false;

    #[Assert\PositiveOrZero(message: "Le prix doit être un nombre positif ou zéro.")]
    public ?int $price = 0;

    public ?string $location = "";

    #[Assert\Type(\DateTimeInterface::class)]
    public ?\DateTimeInterface $endSaleDate = null;

    #[Assert\Type(\DateTimeInterface::class)]
    public ?\DateTimeInterface $startDate = null;

    #[Assert\Type(\DateTimeInterface::class)]
    public ?\DateTimeInterface $endDate = null;

    #[Assert\Positive(message: "Le nombre de places doit être un nombre positif.")]
    public ?int $maxSpace = null;

    public ?UploadedFile $imageFile = null;

    public function hydrate() : void
    {
        parent::hydrate();
        $this->entity->setUpdatedAt( new \DateTimeImmutable() );
    }


}
