<?php

namespace App\Http\Form;

use App\Http\Type\IncrementalType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PreBookEvent extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('quantity', IncrementalType::class, [
                'min' => 0,
                'max' => $options['remaining_spaces'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
        $resolver->setRequired(['remaining_spaces']);
        $resolver->setDefined(['remaining_spaces']);
    }
}
