<?php

namespace App\Http\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IncrementalType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver) : void
    {
        parent::configureOptions( $resolver );
        $resolver->setDefaults([
            'attr' => [
                'min' => 0,
                'max' => 99999,
            ],
            'compound' => false,
        ]);
    }

    public function getParent() : string
    {
        return IntegerType::class;
    }

    public function getBlockPrefix() : string
    {
        return 'incremental';
    }
}
