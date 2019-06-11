<?php

namespace App\Form\Bookkeeping\Tag;

use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Bookkeeping\Tag\Tag;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class TagType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('settlementType', IntegerType::class)
            ->add('backgroundColor', TextType::class)
            ->add('fontColor', TextType::class)
            ->add('includeInBalance', CheckboxType::class)
            ->add('includeInBalanceChart', CheckboxType::class)
            ->add('includeInRealCost', CheckboxType::class)
            ->add('bankStatementPhrases', CollectionType::class, [
                'entry_type' => TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Tag::class
        ]);
    }

    public function getName()
    {
        return 'tag';
    }
}