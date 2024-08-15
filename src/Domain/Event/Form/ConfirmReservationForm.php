<?php

namespace App\Domain\Event\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfirmReservationForm extends AbstractType
{
    public function buildForm( FormBuilderInterface $builder, array $options ) : void
    {
        $builder
            ->add( 'name', TextType::class,
                [
                    'label' => 'Nom',
                    'label_attr' => [
                        'class' => 'label',
                    ],
                ] )
            ->add( 'email', EmailType::class,
                [
                    'label' => 'Email',
                    'label_attr' => [
                        'class' => 'label',
                    ],
                ] )
            ->add( 'phoneNumber', TextType::class,
                [
                    'label' => 'Téléphone',
                    'label_attr' => [
                        'class' => 'label',
                    ],
                ] )
            ->add( 'paymentMethod', ChoiceType::class,
                [
                    'label' => 'Moyen de paiement',
                    'label_attr' => [
                        'class' => 'label',
                    ],
                    'choices' => [
                        'Carte bancaire' => 'stripe',
                    ],
                    'mapped' => false,
                ] );
    }

    public function configureOptions( OptionsResolver $resolver )
    {

    }
}