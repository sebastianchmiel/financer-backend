<?php

namespace App\Form\Saving\Item;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Saving\Item\SavingItem;

class SavingItemType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('amount', IntegerType::class)
            ->add('amountCollected', IntegerType::class)
            ->add('dynamicAmount', CheckboxType::class)
            ->add('plannedInstallment', IntegerType::class)
            ->add('dateFrom', DateType::class, ['widget' => 'single_text'])
            ->add('dateTo', DateType::class, ['widget' => 'single_text'])
            ->add('frequencyOfPaymentInMonths', IntegerType::class)
            ->add('finished', CheckboxType::class)
            ->add('used', CheckboxType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SavingItem::class,
        ]);
    }

    public function getName()
    {
        return 'saving_item';
    }
}