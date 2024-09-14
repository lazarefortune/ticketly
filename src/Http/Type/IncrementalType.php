<?php

namespace App\Http\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IncrementalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder->add('incremental', TextType::class, [
            'attr' => [
                'min' => $options['min'],
                'max' => $options['max'],
                'class' => 'bg-gray-50 border-x-0 border-gray-300 h-11 text-center text-gray-900 text-sm focus:ring-blue-500 focus:border-blue-500 block w-full py-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500',
                'data-input-counter' => true,
            ],
            'label' => false,
            'label_attr' => [
                'class' => 'label',
            ],
            'required' => true,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults([
            'min' => 0,
            'max' => 99999,
            'compound' => true,
        ]);
    }

    public function getParent() : string
    {
        return TextType::class;
    }

    public function getBlockPrefix() : string
    {
        return 'incremental';
    }
}
