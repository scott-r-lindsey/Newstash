<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StashRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(
 *      name="stash",
 *      indexes={
 *          @ORM\Index(name="idx_special", columns={"special"}),
 *          @ORM\Index(name="idx_description", columns={"description"}),
 *      }
 * )
 */

class Stash{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="stashs")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="StashWork", mappedBy="stash")
     */
    private $stash_works;

    //-------------------------------------------------------------------------------

    /** @ORM\Column(type="string", length=255, nullable=false) */
    private $description;

    /** @ORM\Column(type="datetime") */
    private $created_at;

    /** @ORM\Column(type="datetime") */
    private $updated_at;

    /** @ORM\Column(type="boolean") */
    private $special = false;

    public function __construct()
    {
        $this->stash_works = new ArrayCollection();
    }

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

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

    public function getSpecial(): ?bool
    {
        return $this->special;
    }

    public function setSpecial(bool $special): self
    {
        $this->special = $special;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection|StashWork[]
     */
    public function getStashWorks(): Collection
    {
        return $this->stash_works;
    }

    public function addStashWork(StashWork $stashWork): self
    {
        if (!$this->stash_works->contains($stashWork)) {
            $this->stash_works[] = $stashWork;
            $stashWork->setStash($this);
        }

        return $this;
    }

    public function removeStashWork(StashWork $stashWork): self
    {
        if ($this->stash_works->contains($stashWork)) {
            $this->stash_works->removeElement($stashWork);
            // set the owning side to null (unless already changed)
            if ($stashWork->getStash() === $this) {
                $stashWork->setStash(null);
            }
        }

        return $this;
    }

    //-------------------------------------------------------------------------------


}
