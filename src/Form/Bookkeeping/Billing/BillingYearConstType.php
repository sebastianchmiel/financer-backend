<?php

namespace App\Form\Bookkeeping\Billing;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Bookkeeping\Billing\BillingYearConst;

class BillingYearConstType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('year', IntegerType::class)
            ->add('taxFreeAllowanceAmount', IntegerType::class)
            ->add('deductionFromIncomeTaxAmount', IntegerType::class)
            ->add('incomeTaxPercent', IntegerType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BillingYearConst::class
        ]);
    }

    public function getName()
    {
        return 'billing_year_const';
    }
}