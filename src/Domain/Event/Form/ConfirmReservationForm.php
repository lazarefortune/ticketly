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
                    'attr' => [
                        'class' => 'form-input',
                    ],
                ] )
            ->add( 'email', EmailType::class,
                [
                    'label' => 'Email',
                    'label_attr' => [
                        'class' => 'label',
                    ],
                    'attr' => [
                        'class' => 'form-input',
                    ],
                ] )
            ->add( 'phoneNumber', TextType::class,
                [
                    'label' => 'Téléphone',
                    'label_attr' => [
                        'class' => 'label',
                    ],
                    'attr' => [
                        'class' => 'form-input',
                    ],
                ] )
            ->add( 'paymentMethod', ChoiceType::class,
                [
                    'label' => 'Moyen de paiement',
                    'label_attr' => [
                        'class' => 'label',
                    ],
                    'attr' => [
                        'class' => 'form-input',
                    ],
                    'choices' => [
                        'Carte bancaire' => 'stripe',
                    ],
                    'mapped' => false,
                ] );

        if (!$options['hide_coupon']) {
            $builder->add( 'couponCode', TextType::class, [
                'label' => 'Code de coupon',
                'label_attr' => [
                    'class' => 'label',
                ],
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'placeholder' => 'Entrez votre code promo ici',
                    'class' => 'form-input',
                ]
            ] );
        }
    }

    public function configureOptions( OptionsResolver $resolver ) : void
    {
        $resolver->setDefaults([
            'hide_coupon' => false,
        ]);
    }
}