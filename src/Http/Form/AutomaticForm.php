<?php

namespace App\Http\Form;

use App\Domain\Attachment\Entity\Attachment;
use App\Domain\Attachment\Type\AttachmentType;
use App\Domain\Auth\Entity\User;
use App\Http\Admin\Form\ChaptersForm;
use App\Http\Admin\Form\Field\TechnologyChoiceType;
use App\Http\Admin\Form\Field\UserChoiceType;
use App\Http\Type\ChoiceMultipleType;
use App\Http\Type\DateTimeType;
use App\Http\Type\PriceType;
use App\Http\Type\SwitchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Génère un formulaire de manière automatique en lisant les propriétés d'un objet.
 */
class AutomaticForm extends AbstractType
{
    final public const TYPES = [
        'string' => TextType::class,
        'bool' => SwitchType::class,
        'int' => NumberType::class,
        'float' => NumberType::class,
        'array' => ChoiceType::class,
        UploadedFile::class => FileType::class,
        User::class => UserChoiceType::class,
        Attachment::class => AttachmentType::class,
        \DateTimeInterface::class =>  DateTimeType::class,
    ];

    final public const NAMES = [
        'content' => TextareaType::class,
        'description' => TextareaType::class,
        'mainTechnologies' => TechnologyChoiceType::class,
        'secondaryTechnologies' => TechnologyChoiceType::class,
        'requirements' => TechnologyChoiceType::class,
        'chapters' => ChaptersForm::class,
        'short' => TextareaType::class,
        'color' => ColorType::class,
        'links' => TextareaType::class,
        'price' => PriceType::class
    ];

    public function buildForm( FormBuilderInterface $builder, array $options ) : void
    {
        $data = $options['data'];
        $refClass = new \ReflectionClass( $data );
        $classProperties = $refClass->getProperties( \ReflectionProperty::IS_PUBLIC );
        foreach ( $classProperties as $property ) {
            $name = $property->getName();
            /** @var \ReflectionNamedType|null $type */
            $type = $property->getType();
            if ( null === $type ) {
                return;
            }

            if ( $name === 'roles' ) {
                $builder->add( $name, ChoiceMultipleType::class, [
                    'label' => 'Rôles',
                    'choices' => [
                        'Utilisateur' => 'ROLE_USER',
                        'Admin' => 'ROLE_ADMIN',
                        'Super Admin' => 'ROLE_SUPER_ADMIN',
                    ],
                    'multiple' => true,
                    'expanded' => true,
                    'required' => false,
                    'label_attr' => [
                        'class' => 'label',
                    ],
                ] );
                continue;
            }

            if( $name === 'dateOfBirthday' ) {
                $builder->add( $name, DateType::class, [
                    'label' => 'Date de naissance',
                    'widget' => 'single_text',
                    'html5' => false,
                    'label_attr' => [
                        'class' => 'label',
                    ],
                    'attr' => [
                        'class' => 'flatpickr-date-birthday',
                        'data-input' => 'true'
                    ],
                ] );
                continue;
            }

            if ( array_key_exists( $name, self::NAMES ) ) {
                $options = [
                    'required' => false,
                ];
                if (self::NAMES[$name] === TextareaType::class) {
                    $options['attr'] = [
                        'rows' => 10,
                    ];
                }
                $builder->add( $name, self::NAMES[$name], $options );
            } elseif ( array_key_exists( $type->getName(), self::TYPES ) ) {
                dump($type->getName());
                $builder->add( $name, self::TYPES[$type->getName()], [
                    'required' => !$type->allowsNull() && 'bool' !== $type->getName(),
                    'label' => $name,
                ] );
            } else {
                throw new \RuntimeException( sprintf( 'Impossible de trouver le champs associé au type %s dans %s::%s', $type->getName(), $data::class, $name ) );
            }
        }
    }
}