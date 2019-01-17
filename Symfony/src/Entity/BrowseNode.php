<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(
 *      name="browsenode"
 * )
 */

class BrowseNode{

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint")
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity="BrowseNode", mappedBy="children")
     */
    private $parents;

    /**
     * @ORM\ManyToMany(targetEntity="BrowseNode", inversedBy="parents")
     * @ORM\JoinTable(name="browsenode_browsenode",
     *      joinColumns={@ORM\JoinColumn(name="parent_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="child_id", referencedColumnName="id")}
     * )
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $children;

    /**
     * @ORM\ManyToMany(targetEntity="Edition", mappedBy="browse_nodes")
     */
    private $editions;

    /**
     * @ORM\ManyToMany(targetEntity="Edition", mappedBy="primary_browse_nodes")
     */
    private $editions_primary;


    // ------------------------------------------------------------------------

    /** @ORM\Column(type="string", length=80) */
    private $name;

    /** @ORM\Column(type="text", nullable=true) */
    protected $description;

    /** @ORM\Column(type="array", nullable=true) */
    protected $pathdata;

    /** @ORM\Column(type="string", length=255, nullable=true) */
    protected $slug;

    /** @ORM\Column(type="boolean") */
    private $root = false;

    //-------------------------------------------------------------------------------

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): ?string
    {
        return (string)$this->id;
    }


    //-------------------------------------------------------------------------------
    // ./bin/console make:entity --regenerate
    //-------------------------------------------------------------------------------

    public function __construct()
    {
        $this->parents = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->editions = new ArrayCollection();
        $this->editions_primary = new ArrayCollection();
    }

    public function upsertChild(BrowseNode $child): void
    {
        $children = $this->getChildren();

        foreach ($children as $c){
            if ($c->getId() == $child->getId()){
                return;
            }
        }

        $this->addChild($child);
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPathdata(): ?array
    {
        return $this->pathdata;
    }

    public function setPathdata(?array $pathdata): self
    {
        $this->pathdata = $pathdata;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getRoot(): ?bool
    {
        return $this->root;
    }

    public function setRoot(bool $root): self
    {
        $this->root = $root;

        return $this;
    }

    /**
     * @return Collection|BrowseNode[]
     */
    public function getParents(): Collection
    {
        return $this->parents;
    }

    public function addParent(BrowseNode $parent): self
    {
        if (!$this->parents->contains($parent)) {
            $this->parents[] = $parent;
            $parent->addChild($this);
        }

        return $this;
    }

    public function removeParent(BrowseNode $parent): self
    {
        if ($this->parents->contains($parent)) {
            $this->parents->removeElement($parent);
            $parent->removeChild($this);
        }

        return $this;
    }

    /**
     * @return Collection|BrowseNode[]
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(BrowseNode $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
        }

        return $this;
    }

    public function removeChild(BrowseNode $child): self
    {
        if ($this->children->contains($child)) {
            $this->children->removeElement($child);
        }

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
            $edition->addBrowseNode($this);
        }

        return $this;
    }

    public function removeEdition(Edition $edition): self
    {
        if ($this->editions->contains($edition)) {
            $this->editions->removeElement($edition);
            $edition->removeBrowseNode($this);
        }

        return $this;
    }

    /**
     * @return Collection|Edition[]
     */
    public function getEditionsPrimary(): Collection
    {
        return $this->editions_primary;
    }

    public function addEditionsPrimary(Edition $editionsPrimary): self
    {
        if (!$this->editions_primary->contains($editionsPrimary)) {
            $this->editions_primary[] = $editionsPrimary;
            $editionsPrimary->addPrimaryBrowseNode($this);
        }

        return $this;
    }

    public function removeEditionsPrimary(Edition $editionsPrimary): self
    {
        if ($this->editions_primary->contains($editionsPrimary)) {
            $this->editions_primary->removeElement($editionsPrimary);
            $editionsPrimary->removePrimaryBrowseNode($this);
        }

        return $this;
    }


}
