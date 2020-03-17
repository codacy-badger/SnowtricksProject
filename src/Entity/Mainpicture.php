<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MainpictureRepository")
 */
class Mainpicture
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $path;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $realPath;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Snowtrick", inversedBy="mainpicture", cascade={"persist"})
     */
    private $snowtrick;

    public function __construct()
    {
        $this->id = uniqid();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of snowtrick
     */
    public function getSnowtrick()
    {
        return $this->snowtrick;
    }

    /**
     * Set the value of snowtrick
     *
     * @return  self
     */
    public function setSnowtrick($snowtrick)
    {
        $this->snowtrick = $snowtrick;

        return $this;
    }

    /**
     * Get the value of realPath
     */
    public function getRealPath()
    {
        return $this->realPath;
    }

    /**
     * Set the value of realPath
     *
     * @return  self
     */
    public function setRealPath($realPath)
    {
        $this->realPath = $realPath;

        return $this;
    }
}
