<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\DataTransformer;

use App\Entity\Provincia;
use App\Repository\ProvinciaRepository;
use Symfony\Component\Form\DataTransformerInterface;
use function Symfony\Component\String\u;

/**
 * This data transformer is used to translate the array of tags into a comma separated format
 * that can be displayed and managed by Bootstrap-tagsinput js plugin (and back on submit).
 *
 * See https://symfony.com/doc/current/form/data_transformers.html
 *
 * @author Yonel Ceruto <yonelceruto@gmail.com>
 * @author Jonathan Boyer <contact@grafikart.fr>
 */
class ProvinciaAutocompleteTransformer implements DataTransformerInterface
{
  private $provinciaRepository;

public function __construct(ProvinciaRepository $provinciaRepository)
{
  $this->provinciaRepository = $provinciaRepository;
}

public function transform($provincia)
{
  if (null === $provincia) {
      return '';
  }

  return $provincia->getDescripcion();
}

public function reverseTransform($provinciaName)
{
  if (!$provinciaName) {
      return;
  }
 
  $provincia = $this->provinciaRepository->findOneBy(array('descripcion' => $provinciaName));

  if (null === $provincia) {
      throw new TransformationFailedException(sprintf('There is no "%s" exists',
          $provinciaName
      ));
  }

  return $provincia;
}
}
