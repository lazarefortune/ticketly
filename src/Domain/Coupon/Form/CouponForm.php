<?php

namespace App\Domain\Coupon\Form;

use App\Domain\Coupon\Entity\Coupon;
use App\Http\Type\PriceType;
use App\Http\Type\SwitchType;
use App\Validator\Unique;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CouponForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'Code du coupon',
                'required' => true,
                'constraints' => [
                    new Assert\Length(['min' => 5]),
                ],
            ])
            ->add('typeCoupon', ChoiceType::class, [
                'label' => 'Type de coupon',
                'choices' => [
                    'Pourcentage' => Coupon::TYPE_PERCENTAGE,
                    'Fixe' => Coupon::TYPE_FIXED,
                ],
                'required' => true,
            ])
            ->add('isActive', SwitchType::class, [
                'label' => 'Actif ?',
                'required' => true,
            ])
            ->add('expiresAt', DateTimeType::class, [
                'label' => 'Date d\'expiration',
                'required' => true,
                'widget' => 'single_text',
                'html5' => false,
                'attr' => [
                    'class' => 'flatpickr-datetime',
                ],
                'label_attr' => [
                    'class' => 'label'
                ],
            ]);

        // Add dynamic adjustment of 'valueCoupon' field based on 'typeCoupon'
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $form = $event->getForm();
                $coupon = $event->getData();

                $this->addValueField($form, $coupon ? $coupon->getTypeCoupon() : null);
            }
        );

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();

                $this->addValueField($form, $data['typeCoupon'] ?? null);
            }
        );
    }

    private function addValueField($form, $typeCoupon) : void
    {
        if ($typeCoupon === Coupon::TYPE_FIXED) {
            $form->add('valueCoupon', PriceType::class, [
                'label' => 'Montant de réduction (€)',
                'required' => true,
            ]);
        } else {
            $form->add('valueCoupon', NumberType::class, [
                'label' => 'Pourcentage de réduction (%)',
                'required' => true,
                'attr' => [
                    'min' => 0,
                    'max' => 100,
                    'step' => 1,
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Coupon::class,
        ]);
    }
}
