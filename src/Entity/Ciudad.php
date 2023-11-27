<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ciudad
 *
 * @ORM\Table(name="ciudad", indexes={@ORM\Index(name="index_ciudad", columns={"id_provincia"})})
 * @ORM\Entity
 */
class Ciudad
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="descripcion", type="string", length=255, nullable=false)
     */
    private $descripcion;


     /**
      * @var Provincia
      *
      * @ORM\ManyToOne(targetEntity="App\Entity\Provincia")
      * @ORM\JoinColumn(nullable=false)
      */
    private $provincia;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(string $descripcion): self
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getProvincia(): ?Provincia
    {
        return $this->provincia;
    }

    public function setProvincia(Provincia $provincia): self
    {
        $this->provincia = $provincia;

        return $this;
    }


}
