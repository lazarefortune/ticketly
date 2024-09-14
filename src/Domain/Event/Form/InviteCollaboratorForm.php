<?php

namespace App\Domain\Event\Form;

use App\Domain\Event\Entity\EventCollaborator;
use App\Http\Type\ChoiceMultipleType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class InviteCollaboratorForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email du collaborateur',
                'label_attr' => [
                    'class' => 'label',
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Email(),
                ],
                'attr' => [
                    'class' => 'form-input',
                ],
            ])
            ->add('roles', ChoiceMultipleType::class, [
                'label' => 'Rôles',
                'choices' => [
                    'Gestion des réservations' => EventCollaborator::ROLE_RESERVATIONS,
                    'Gestion des coupons' => EventCollaborator::ROLE_COUPONS,
                    'Gestion des finances' => EventCollaborator::ROLE_FINANCE,
                    'Gérer les collaborateurs' => EventCollaborator::ROLE_MANAGER,
                    'Gérer les billets' => EventCollaborator::ROLE_TICKETS,
                    'Gérer l\'événement' => EventCollaborator::ROLE_MANAGE_EVENT,
                ],
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'label_attr' => [
                    'class' => 'label',
                ],
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
