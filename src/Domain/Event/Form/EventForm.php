<?php

namespace App\Domain\Event\Form;

use App\Domain\Event\Entity\Event;
use App\Http\Type\PriceType;
use App\Http\Type\SwitchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints as Assert;

class EventForm extends AbstractType
{
    public function buildForm( FormBuilderInterface $builder, array $options ) : void
    {
        $builder->add('name', TextType::class, [
            'label' => 'Nom',
            'label_attr' => [
                'class' => 'label',
            ],
            'attr' => [
                'class' => 'form-input',
            ],
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\Length(['min' => 3]),
            ],
        ])
            ->add('slug', TextType::class, [
                'label' => 'Slug',
                'label_attr' => [
                    'class' => 'label',
                ],
                'attr' => [
                    'readonly' => true,
                    'class' => 'disabled form-input'
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'label_attr' => [
                    'class' => 'label',
                ],
                'attr' => [
                    'rows' => 5,
                    'class' => 'form-input',
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 10]),
                ],
            ])
            ->add('location', TextType::class, [
                'label' => 'Adresse',
                'label_attr' => [
                    'class' => 'label',
                ],
                'attr' => [
                    'class' => 'address-input form-input',
                    'placeholder' => 'Ex: 12 rue de la République, 75001 Paris'
                ],
            ])
            ->add('price', PriceType::class, [
                'label' => 'Prix',
                'label_attr' => [
                    'class' => 'label',
                ],
                'attr' => [
                    'class' => 'form-input',
                ],
                'constraints' => [
                    new Assert\PositiveOrZero(),
                ],
            ])
            ->add('maxSpace', NumberType::class, [
                'label' => 'Nombre de places',
                'label_attr' => [
                    'class' => 'label',
                ],
                'attr' => [
                    'class' => 'form-input',
                ],
                'constraints' => [
                    new Assert\Positive(),
                ],
            ])
            ->add('endSaleDate', DateTimeType::class, [
                'label' => 'Date de fin de vente',
                'label_attr' => [
                    'class' => 'label',
                ],
                'widget' => 'single_text',
                'html5' => false,
                'attr' => [
                    'class' => 'flatpickr-datetime form-input',
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Type(\DateTimeInterface::class),
                ],
            ])
            ->add('startDate', DateTimeType::class, [
                'label' => 'Date de début',
                'label_attr' => [
                    'class' => 'label',
                ],
                'widget' => 'single_text',
                'html5' => false,
                'attr' => [
                    'class' => 'flatpickr-datetime form-input',
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Type(\DateTimeInterface::class),
                ],
            ])
            ->add('endDate', DateTimeType::class, [
                'label' => 'Date de fin',
                'label_attr' => [
                    'class' => 'label',
                ],
                'widget' => 'single_text',
                'html5' => false,
                'attr' => [
                    'class' => 'flatpickr-datetime form-input',
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Type(\DateTimeInterface::class),
                ],
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image',
                'label_attr' => [
                    'class' => 'label',
                ],
                'attr' => [
                    'class' => 'form-input',
                ],
                'required' => false,
            ])
            ->add('isActive', SwitchType::class, [
                'label' => 'Actif',
                'label_attr' => [
                    'class' => 'label',
                ],
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT, $this->autoSlug(...))
        ;
    }

    /**
     * Slugify the name to create a unique slug with timestamp
     * @param PreSubmitEvent $preSubmitEvent
     * @return void
     */
    public function autoSlug( PreSubmitEvent $preSubmitEvent ) : void
    {
        $data = $preSubmitEvent->getData();
        if ( !isset($data['name']) ) {
            return;
        }

        if ( empty($data['slug']) ) {
            $slugger = new AsciiSlugger();
            $data['slug'] = strtolower($slugger->slug($data['name'])) . '-' . uniqid();
        }

        $preSubmitEvent->setData($data);
    }

    public function configureOptions( OptionsResolver $resolver ) : void
    {
        $resolver->setDefaults( [
            'data_class' => Event::class,
        ] );
    }


}