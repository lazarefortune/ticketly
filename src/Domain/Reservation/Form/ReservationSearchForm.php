<?php

namespace App\Domain\Reservation\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ReservationSearchForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('reservationNumber', TextType::class, [
                'label' => 'Numéro de réservation',
                'label_attr' => [
                    'class' => 'label',
                ],
                'attr' => [
                    'class' => 'form-input',
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 3]),
                ],
            ])
            ->add('email', TextType::class, [
                'label' => 'Adresse email',
                'label_attr' => [
                    'class' => 'label',
                ],
                'attr' => [
                    'class' => 'form-input',
                ],
                'required' => true,
            ]);
    }

}