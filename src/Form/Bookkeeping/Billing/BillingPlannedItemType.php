<?php

namespace App\Form\Bookkeeping\Billing;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Bookkeeping\Billing\BillingPlannedItem;
use App\Entity\Bookkeeping\Contractor\Contractor;
use App\Form\Bookkeeping\Billing\BillingPlannedItemPositionType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Bookkeeping\Tag\Tag;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class BillingPlannedItemType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('dateFrom', DateType::class, ['widget' => 'single_text'])
            ->add('dateTo', DateType::class, ['widget' => 'single_text'])
            ->add('type', IntegerType::class)
            ->add('date', TextType::class)
            ->add('dateOfService', TextType::class)
            ->add('dateOfPayment', TextType::class)
            ->add('dateOfPaid', TextType::class)
            ->add('invoiceNumber', TextType::class)
            ->add('contractor', EntityType::class, ['class' => Contractor::class])
            ->add('description', TextType::class)
            ->add('amountNet', IntegerType::class)
            ->add('amountGross', IntegerType::class)
            ->add('taxValue', IntegerType::class)
            ->add('taxPercent', IntegerType::class)    
            ->add('paymentMethod', TextType::class)
            ->add('billingPlannedItemPositions', CollectionType::class, [
                'entry_type' => BillingPlannedItemPositionType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'by_reference' => false,
                'empty_data' => new ArrayCollection(),
            ])
            ->add('tags', EntityType::class, [
                'class' => Tag::class,
                'multiple' => true,
                'by_reference' => false,
            ])
            ->add('onlyAsPattern', CheckboxType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BillingPlannedItem::class
        ]);
    }

    public function getName()
    {
        return 'billing_planned_item';
    }
}