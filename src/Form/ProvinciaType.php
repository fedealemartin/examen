<?php

namespace App\Form;


use App\Entity\Provincia;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;



class ProvinciaType extends AbstractType
{


  /**
   * {@inheritdoc}
   */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {


        $builder
            ->add('descripcion')
        ;
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Provincia::class,
        ]);
    }
}
