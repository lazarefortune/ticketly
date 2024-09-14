<?php

namespace App\Domain\Event\Form;

use App\Domain\Event\Entity\EventCollaborator;
use App\Http\Type\ChoiceMultipleType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModifyCollaboratorRolesForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('roles', ChoiceMultipleType::class, [
                'choices' => [
                    'Finance' => EventCollaborator::ROLE_FINANCE,
                    'Coupons' => EventCollaborator::ROLE_COUPONS,
                    'Reservations' => EventCollaborator::ROLE_RESERVATIONS,
                    'Manager' => EventCollaborator::ROLE_MANAGER,
                    'Billets' => EventCollaborator::ROLE_TICKETS,
                    'Gestion de l\'événement' => EventCollaborator::ROLE_MANAGE_EVENT,
                ],
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'label' => 'Rôles du collaborateur',
                'label_attr' => ['class' => 'label'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
