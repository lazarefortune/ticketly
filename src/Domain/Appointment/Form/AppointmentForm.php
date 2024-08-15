<?php

namespace App\Domain\Appointment\Form;

use App\Domain\Appointment\Entity\Appointment;
use App\Domain\Appointment\Form\Type\SlotChoiceType;
use App\Domain\Appointment\Service\AppointmentService;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Repository\UserRepository;
use App\Domain\Prestation\Entity\Prestation;
use App\Domain\Prestation\PrestationService;
use App\Http\Type\SwitchType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class AppointmentForm extends AbstractType
{

    public function __construct(
        private readonly AppointmentService $appointmentService,
        private readonly PrestationService  $prestationService
    )
    {
    }

    public function buildForm( FormBuilderInterface $builder, array $options ) : void
    {
        $builder
            ->add( 'autoConfirm', SwitchType::class, [
                'label' => 'Confirmer automatiquement',
                'mapped' => false,
            ] )
            ->add('client', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
                'label' => 'Utilisateur',
                'query_builder' => function (UserRepository $userRepository) {
                    return $userRepository->getQueryUsersWithoutRoles( ['ROLE_SUPER_ADMIN'] );
                },
                'attr' => [
                    'class' => 'select2'
                ],
            ])
            ->add( 'prestation', EntityType::class, [
                'class' => Prestation::class,
                'choice_label' => 'name',
                'label' => 'Prestation',
                'label_attr' => [
                    'class' => 'label'
                ],
                'attr' => [
                    'class' => 'select2'
                ],
            ] )
            ->add( 'date', DateType::class, [
                'label' => 'Date du rendez-vous',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'flatpickr-date-input appointment-date-choice'
                ],
                'required' => true,
                'label_attr' => [
                    'class' => 'label'
                ],

            ] )
            ->add( 'slot', SlotChoiceType::class, [
                'choices' => [],
                'label' => 'Créneaux horaires',
                'mapped' => false,
                'expanded' => true,
                'multiple' => false,
                'label_attr' => [
                    'class' => 'label'
                ],
            ] );

        $builder->addEventListener( FormEvents::PRE_SUBMIT, function ( FormEvent $event ) {
            $data = $event->getData();
            $form = $event->getForm();

            $date = new \DateTime( $data['date'] );
            $prestationId = $data['prestation'];
            $prestation = $this->prestationService->getPrestationById( $prestationId );

            $slots = $this->appointmentService->getSlotsString( $date, $prestation );
            if ( $slots ) {
                $form->add( 'slot', SlotChoiceType::class, [
                    'choices' => array_flip( $slots ),
                    'label' => 'Créneaux horaires',
                    'required' => true,
                    'expanded' => true,
                    'multiple' => false,
                    'mapped' => false,
                    'label_attr' => ['class' => 'label'],
                ] );
            }
        } );
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function ( FormEvent $event ) {
                $form = $event->getForm();
                /** @var Appointment $data */
                $data = $event->getData();

                if ( $data && $data->getDate() ) {
                    $prestation = $this->prestationService->getPrestationById( $data->getPrestation()->getId() );
                    $slots = $this->appointmentService->getSlotsString( $data->getDate(), $data->getPrestation() );
                    if ( $slots ) {
                        $form->add( 'slot', SlotChoiceType::class, [
                            'choices' => array_flip( $slots ),
                            'label' => 'Créneaux horaires',
                            'required' => true,
                            'expanded' => true,
                            'multiple' => false,
                            'mapped' => false,
                            'label_attr' => [
                                'class' => 'label'
                            ],
                        ] );
                    }
                }
            }
        );
    }
}