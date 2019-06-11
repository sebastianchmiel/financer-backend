<?php

namespace App\Form\Bookkeeping\Billing;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Bookkeeping\Billing\BillingItemPosition;

class BillingItemPositionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('quantity', IntegerType::class)
            ->add('unit', TextType::class)
            ->add('amountNetSingle', IntegerType::class)
            ->add('amountNet', IntegerType::class)
            ->add('amountGross', IntegerType::class)
            ->add('taxValue', IntegerType::class)
            ->add('taxPercent', IntegerType::class)    
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BillingItemPosition::class,
        ]);
    }

    public function getName()
    {
        return 'billing_item_position';
    }
}