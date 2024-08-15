<?php

namespace App\Http\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PreBookEvent extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $choices = [];
        for ($i = 0; $i <= $options['remaining_spaces']; $i++) {
            $choices[(string)$i] = $i;
        }

        $builder
            ->add('quantity', ChoiceType::class, [
                'label' => null,
                'choices' => $choices,
            ]);
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
