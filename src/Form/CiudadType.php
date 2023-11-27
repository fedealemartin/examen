<?php

namespace App\Form;

use App\Entity\Ciudad;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Form\DataTransformer\ProvinciaAutocompleteTransformer;
 use App\Repository\ProvinciaRepository;

use App\Entity\Provincia;

class CiudadType extends AbstractType
{
  private $provincia;

  public function __construct(ProvinciaRepository $provincia)
  {
      $this->provincia = $provincia;
  }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('descripcion')
            /*
            ->add('provincia', EntityType::class, [
                  'class' => Provincia::class,
                  'choice_label' => 'descripcion'
              ])
             */
              ->add('provincia', TextType::class, array(
                    'label' => 'Escriba la provincia',
                    //'error_bubbling' => true,
                    'invalid_message' => 'La provincia no se encuentra en la lista',
                  ))
              ->get('provincia')
                   ->addModelTransformer(new ProvinciaAutocompleteTransformer($this->provincia));


            // form events let you modify information or fields at different steps
            // of the form handling process.
            // See https://symfony.com/doc/current/form/events.html
            /*
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {

                $prov = $event->getData();

                if (null !== $provinciaTitle = $prov->getDescripcion()) {
                    $post->setSlug($this->slugger->slug($provinciaTitle)->lower());
                }
            })
            */
        ;
    }



    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ciudad::class,
        ]);
    }
}
