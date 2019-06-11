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
use App\Form\Bookkeeping\Billing\BillingItemPositionType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Bookkeeping\Billing\BillingPlannedItem;
use App\Entity\Bookkeeping\Tag\Tag;

class BillingItemIncomeType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date', DateType::class, ['widget' => 'single_text'])
            ->add('dateOfService', DateType::class, ['widget' => 'single_text'])
            ->add('dateOfPayment', DateType::class, ['widget' => 'single_text'])
            ->add('dateOfPaid', DateType::class, ['widget' => 'single_text'])
            ->add('invoiceNumber', TextType::class)
            ->add('contractor', EntityType::class, ['class' => Contractor::class])
            ->add('description', TextType::class)
            ->add('amountNet', IntegerType::class)
            ->add('amountGross', IntegerType::class)
            ->add('taxValue', IntegerType::class)
            ->add('taxPercent', IntegerType::class)    
            ->add('paymentMethod', TextType::class)
            ->add('billingItemPositions', CollectionType::class, [
                'entry_type' => BillingItemPositionType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'by_reference' => false,
                'empty_data' => new ArrayCollection(),
            ])
            ->add('plannedItem', EntityType::class, ['class' => BillingPlannedItem::class])
            ->add('tags', EntityType::class, [
                'class' => Tag::class,
                'multiple' => true,
                'by_reference' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BillingItem::class,
            'validation_groups' => ['typeIncome'],
        ]);
    }

    public function getName()
    {
        return 'billing_item_income';
    }
}