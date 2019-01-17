<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(
 *      name="format"
 * )
 */

class Format{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="Edition", mappedBy="format")
     */
    private $editions;

    /** @ORM\Column(type="string", length=40) */
    private $description;

    /** @ORM\Column(type="boolean") */
    private $paper = false;

    /** @ORM\Column(type="boolean") */
    private $e = false;

    /** @ORM\Column(type="boolean") */
    private $audio = false;

    /** @ORM\Column(type="boolean") */
    private $noprice = false;

    //-------------------------------------------------------------------------------

    public function testSetId($id)
    {
        $this->id = $id;
        return $this;
    }

    //-------------------------------------------------------------------------------
    // ./bin/console make:entity --regenerate
    //-------------------------------------------------------------------------------

    public function __construct()
    {
        $this->editions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPaper(): ?bool
    {
        return $this->paper;
    }

    public function setPaper(bool $paper): self
    {
        $this->paper = $paper;

        return $this;
    }

    public function getE(): ?bool
    {
        return $this->e;
    }

    public function setE(bool $e): self
    {
        $this->e = $e;

        return $this;
    }

    public function getAudio(): ?bool
    {
        return $this->audio;
    }

    public function setAudio(bool $audio): self
    {
        $this->audio = $audio;

        return $this;
    }

    public function getNoprice(): ?bool
    {
        return $this->noprice;
    }

    public function setNoprice(bool $noprice): self
    {
        $this->noprice = $noprice;

        return $this;
    }

    /**
     * @return Collection|Edition[]
     */
    public function getEditions(): Collection
    {
        return $this->editions;
    }

    public function addEdition(Edition $edition): self
    {
        if (!$this->editions->contains($edition)) {
            $this->editions[] = $edition;
            $edition->setFormat($this);
        }

        return $this;
    }

    public function removeEdition(Edition $edition): self
    {
        if ($this->editions->contains($edition)) {
            $this->editions->removeElement($edition);
            // set the owning side to null (unless already changed)
            if ($edition->getFormat() === $this) {
                $edition->setFormat(null);
            }
        }

        return $this;
    }

}
