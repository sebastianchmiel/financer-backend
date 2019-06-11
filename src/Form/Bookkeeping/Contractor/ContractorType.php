<?php

namespace App\Form\Bookkeeping\Contractor;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Bookkeeping\Contractor\Contractor;

class ContractorType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('fullName', TextType::class)
            ->add('addressStreet', TextType::class)
            ->add('addressCity', TextType::class)
            ->add('addressPostCode', TextType::class)
            ->add('nip', TextType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Contractor::class,
        ]);
    }

    public function getName()
    {
        return 'contractor';
    }
}