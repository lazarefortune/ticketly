<?php

namespace App\Http\Admin\Data\Crud;

use App\Domain\Coupon\Entity\Coupon;
use App\Http\Admin\Data\AutomaticCrudData;
use App\Validator\Unique;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @property Coupon $entity
 */
#[Unique( field: 'code' )]
class CouponCrudData extends AutomaticCrudData
{
    #[Assert\Length(max: 255)]
    public ?string $code = '';

    #[Assert\Choice(choices: [Coupon::TYPE_PERCENTAGE, Coupon::TYPE_FIXED])]
    public ?string $typeCoupon = '';

    #[Assert\PositiveOrZero]
    public ?int $valueCoupon = 0;

    #[Assert\Type( type: \DateTimeInterface::class )]
    public ?\DateTimeInterface $expiresAt = null;

    public ?bool $isActive = false;

    public function hydrate(): void
    {
        parent::hydrate();
    }
}