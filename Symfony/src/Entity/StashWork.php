<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StashWorkRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(
 *      name="stash_work",
 *      indexes={
 *      }
 * )
 */

class StashWork{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Stash", inversedBy="stash_works")
     * @ORM\JoinColumn(name="stash_id", referencedColumnName="id", nullable=false)
     */
    private $stash;

    /**
     * @ORM\ManyToOne(targetEntity="Work", inversedBy="stash_works")
     * @ORM\JoinColumn(name="work_id", referencedColumnName="id", nullable=false)
     */
    private $work;

    //-------------------------------------------------------------------------------

    /** @ORM\Column(type="integer", name="xrank") */
    private $rank = 0;

    /** @ORM\Column(type="datetime") */
    private $created_at;

    /** @ORM\Column(type="datetime") */
    private $updated_at;

    //-------------------------------------------------------------------------------

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function updatedTimestamps(){
        $this->setUpdatedAt(new \DateTime());

        if(null == $this->getCreatedAt()) {
            $this->setCreatedAt(new \DateTime());
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function setRank(int $rank): self
    {
        $this->rank = $rank;

        return $this;
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

    public function getStash(): ?Stash
    {
        return $this->stash;
    }

    public function setStash(?Stash $stash): self
    {
        $this->stash = $stash;

        return $this;
    }

    public function getWork(): ?Work
    {
        return $this->work;
    }

    public function setWork(?Work $work): self
    {
        $this->work = $work;

        return $this;
    }


}
