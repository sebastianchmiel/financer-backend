<?php

namespace App\Form\Saving\Account;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\GreaterThan;

class SavingAccountAmountType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('balance', NumberType::class, [
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('balanceDistributed', NumberType::class, [
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('balanceForDistribution', NumberType::class, [
                'constraints' => [
                    new NotBlank()
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null
        ]);
    }

    public function getName()
    {
        return 'saving_account_amount_type';
    }
}