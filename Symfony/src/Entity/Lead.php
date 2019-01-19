<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="App\Repository\LeadRepository")
 * @ORM\Table(
 *      name="xlead",
 *      indexes={
 *          @ORM\Index(name="idx_created_at", columns={"created_at"}),
 *          @ORM\Index(name="idx_updated_at", columns={"updated_at"}),
 *          @ORM\Index(name="idx_new", columns={"new"}),
 *          @ORM\Index(name="idx_rejected", columns={"rejected"}),
 *      }
 * )
 */

class Lead{

    /**
     * @ORM\Id
     * @ORM\Column(type="string", nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id;

    /** @ORM\Column(type="datetime") */
    private $created_at;

    /** @ORM\Column(type="datetime") */
    private $updated_at;

    /** @ORM\Column(type="boolean") */
    private $new = true;

    /** @ORM\Column(type="boolean") */
    private $rejected = false;

    /** @ORM\Column(type="string", length=255, nullable=true) */
    private $amzn_format;

    //-------------------------------------------------------------------------------

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function updatedTimestamps(): void
    {
        $this->setUpdatedAt(new \DateTime());

        if(null == $this->getCreatedAt()) {
            $this->setCreatedAt(new \DateTime());
        }
    }

    //-------------------------------------------------------------------------------
    // ./bin/console make:entity --regenerate
    //-------------------------------------------------------------------------------

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getNew(): ?bool
    {
        return $this->new;
    }

    public function setNew(bool $new): self
    {
        $this->new = $new;

        return $this;
    }

    public function getRejected(): ?bool
    {
        return $this->rejected;
    }

    public function setRejected(bool $rejected): self
    {
        $this->rejected = $rejected;

        return $this;
    }

    public function getAmznFormat(): ?string
    {
        return $this->amzn_format;
    }

    public function setAmznFormat(?string $amzn_format): self
    {
        $this->amzn_format = $amzn_format;

        return $this;
    }
}
