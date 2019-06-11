<?php

namespace App\Form\Saving\Payment;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use App\Form\Saving\Payment\SavingPaymentAddItemType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\Date;

class SavingPaymentAdd extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', TextType::class)
            ->add('amount', NumberType::class, [
                'constraints' => [
                    new NotBlank(),
                    new GreaterThan(0)
                ]
            ])
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 1])
                ]
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(),
                    new Date()
                ]
            ])
            ->add('sourceItemId', IntegerType::class)
            ->add('items', CollectionType::class, [
                'entry_type' => SavingPaymentAddItemType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'by_reference' => false,
                'empty_data' => new ArrayCollection(),
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
        return 'saving_item_add_type';
    }
}