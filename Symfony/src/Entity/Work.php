<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks * @ORM\HasLifecycleCallbacks
 * @ORM\Table(
 *      name="work",
 *      indexes={
 *          @ORM\Index(name="idx_title", columns={"title"}),
 *          @ORM\Index(name="idx_created_at", columns={"created_at"}),
 *          @ORM\Index(name="idx_updated_at", columns={"updated_at"}),
 *          @ORM\Index(name="idx_deleted", columns={"deleted"})
 *      }
 * )
 */

class Work{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    // ------------------------------------------------------------------------

    /**
     * @ORM\OneToMany(targetEntity="Edition", mappedBy="work")
     */
    private $editions;

    /**
     * @ORM\ManyToOne(targetEntity="Edition")
     * @ORM\JoinColumn(name="front_edition_asin", referencedColumnName="asin")
     */
    private $front_edition;

    /**
     * @ORM\ManyToOne(targetEntity="Work", inversedBy="superseded")
     * @ORM\JoinColumn(name="superseding_id", referencedColumnName="id")
     **/
    private $superseding;

    /**
     * @ORM\OneToMany(targetEntity="Work", mappedBy="superseding")
     **/
    private $superseded;

    /**
     * @ORM\OneToMany(targetEntity="SimilarWork", mappedBy="work")
     */
    private $similar_works;

    // ------------------------------------------------------------------------

    /** @ORM\Column(type="boolean", options={"default":false}) */
    private $deleted = false;

    /** @ORM\Column(type="string", length=255) */
    private $title;

    /** @ORM\Column(type="datetime") */
    private $created_at;

    /** @ORM\Column(type="datetime") */
    private $updated_at;

    //-------------------------------------------------------------------------------
    // ./bin/console make:entity --regenerate
    //-------------------------------------------------------------------------------

    public function __construct()
    {
        $this->editions = new ArrayCollection();
        $this->superseded = new ArrayCollection();
        $this->similar_works = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

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
            $edition->setWork($this);
        }

        return $this;
    }

    public function removeEdition(Edition $edition): self
    {
        if ($this->editions->contains($edition)) {
            $this->editions->removeElement($edition);
            // set the owning side to null (unless already changed)
            if ($edition->getWork() === $this) {
                $edition->setWork(null);
            }
        }

        return $this;
    }

    public function getFrontEdition(): ?Edition
    {
        return $this->front_edition;
    }

    public function setFrontEdition(?Edition $front_edition): self
    {
        $this->front_edition = $front_edition;

        return $this;
    }

    public function getSuperseding(): ?self
    {
        return $this->superseding;
    }

    public function setSuperseding(?self $superseding): self
    {
        $this->superseding = $superseding;

        return $this;
    }

    /**
     * @return Collection|Work[]
     */
    public function getSuperseded(): Collection
    {
        return $this->superseded;
    }

    public function addSuperseded(Work $superseded): self
    {
        if (!$this->superseded->contains($superseded)) {
            $this->superseded[] = $superseded;
            $superseded->setSuperseding($this);
        }

        return $this;
    }

    public function removeSuperseded(Work $superseded): self
    {
        if ($this->superseded->contains($superseded)) {
            $this->superseded->removeElement($superseded);
            // set the owning side to null (unless already changed)
            if ($superseded->getSuperseding() === $this) {
                $superseded->setSuperseding(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SimilarWork[]
     */
    public function getSimilarWorks(): Collection
    {
        return $this->similar_works;
    }

    public function addSimilarWork(SimilarWork $similarWork): self
    {
        if (!$this->similar_works->contains($similarWork)) {
            $this->similar_works[] = $similarWork;
            $similarWork->setWork($this);
        }

        return $this;
    }

    public function removeSimilarWork(SimilarWork $similarWork): self
    {
        if ($this->similar_works->contains($similarWork)) {
            $this->similar_works->removeElement($similarWork);
            // set the owning side to null (unless already changed)
            if ($similarWork->getWork() === $this) {
                $similarWork->setWork(null);
            }
        }

        return $this;
    }
}
