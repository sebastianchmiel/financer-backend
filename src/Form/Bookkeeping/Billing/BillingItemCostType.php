<?php

namespace App\Form\Bookkeeping\Billing;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Bookkeeping\Billing\BillingItem;
use App\Entity\Bookkeeping\Contractor\Contractor;
use App\Entity\Bookkeeping\Billing\BillingPlannedItem;
use App\Entity\Bookkeeping\Tag\Tag;

class BillingItemCostType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date', DateType::class, ['widget' => 'single_text'])
            ->add('invoiceNumber', TextType::class)
            ->add('contractor', EntityType::class, ['class' => Contractor::class])
            ->add('description', TextType::class)
            ->add('amountNet', IntegerType::class)
            ->add('amountGross', IntegerType::class)
            ->add('taxValue', IntegerType::class)
            ->add('taxPercent', IntegerType::class)    
            ->add('plannedItem', EntityType::class, ['class' => BillingPlannedItem::class])
            ->add('tags', EntityType::class, [
                'class' => Tag::class,
                'multiple' => true,
                'required' => false,
                'by_reference' => false,
            ])
            ->add('dateOfPaid', DateType::class, ['widget' => 'single_text'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BillingItem::class,
            'validation_groups' => ['Default', 'typeCost'],
        ]);
    }

    public function getName()
    {
        return 'billing_item_cost';
    }
}