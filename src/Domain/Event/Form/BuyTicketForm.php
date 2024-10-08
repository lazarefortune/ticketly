<?php

namespace App\Domain\Event\Form;

use App\Domain\Contact\Dto\ContactData;
use App\Domain\Event\Dto\TicketDto;
use App\Http\Type\CaptchaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BuyTicketForm extends AbstractType
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
                        'Cash' => 'cash',
                    ],
                    'mapped' => false,
                ] )
        ;
    }

    public function configureOptions( OptionsResolver $resolver ) : void
    {
        $resolver->setDefaults( [
            'data_class' => TicketDto::class,
        ] );
    }
}
