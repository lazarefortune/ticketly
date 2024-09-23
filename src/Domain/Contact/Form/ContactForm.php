<?php

namespace App\Domain\Contact\Form;

use App\Domain\Contact\Dto\ContactData;
use App\Http\Type\CaptchaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactForm extends AbstractType
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
            ->add( 'subject', ChoiceType::class, [
                'label' => 'C\'est à propos de ?',
                'label_attr' => [
                  'class' => 'label'
                ],
                'attr' => [
                  'class' => 'form-input'
                ],
                'choices' => [
                    'Besoin d\'assistance' => 'help',
                    'Bug' => 'bug',
                    'Autres' => 'others'
                ]
            ])
            ->add( 'message', TextareaType::class,
                [
                    'label' => 'Votre message',
                    'label_attr' => [
                        'class' => 'label',
                    ],
                    'attr' => [
                        'rows' => 7,
                        'class' => 'form-input'
                    ],
                ] )
//            ->add('captcha', CaptchaType::class, [
//                'mapped' => false,
//                'help' => 'Placez la pièce du puzzle pour vérifier que vous n’êtes pas un robot',
//                'route' => 'app_captcha'
//            ])
        ;
    }

    public function configureOptions( OptionsResolver $resolver ) : void
    {
        $resolver->setDefaults( [
            'data_class' => ContactData::class,
        ] );
    }
}
