<?php

namespace App\Validator;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Exists extends Constraint
{
    public string $message = 'Aucun enregistrement correspondant à cette valeur {{ value }}';

    /**
     * @var class-string<object>
     */
    public string $class = \stdClass::class;

    #[HasNamedArguments]
    public function __construct(string $class = \stdClass::class, array $groups = null, mixed $payload = null)
    {
        parent::__construct(['class' => $class], $groups, $payload);
    }
}