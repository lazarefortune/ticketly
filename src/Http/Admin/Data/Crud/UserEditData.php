<?php

namespace App\Http\Admin\Data\Crud;

use App\Domain\Auth\Entity\User;
use App\Http\Admin\Data\AutomaticCrudData;
use App\Validator\Unique;
use Symfony\Component\Validator\Constraints as Assert;

class UserEditData extends AutomaticCrudData
{
    public array $roles = [];

    public function hydrate() : void
    {
        parent::hydrate();
        $this->entity->setUpdatedAt( new \DateTimeImmutable() );
        $this->entity->setRoles( $this->roles );
    }
}