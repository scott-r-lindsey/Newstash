<?php
declare(strict_types=1);

namespace App\Entity;

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

    public function upsertChild($child){
        $children = $this->getChildren();
        foreach ($children as $c){
            if ($c->getId() == $child->getId()){
                return;
            }
        }
        $this->addChildren($child);
    }

    //-------------------------------------------------------------------------------
    // ./app/console doctrine:generate:entities Scott/DataBundle/Entity/BrowseNode
    //-------------------------------------------------------------------------------

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->parents = new \Doctrine\Common\Collections\ArrayCollection();
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Set id
     *
     * @param integer $id
     * @return BrowseNode
     */
    public function setId($id)
    {
        $this->id = $id;
    
        return $this;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return (int)$this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return BrowseNode
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return BrowseNode
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set pathdata
     *
     * @param array $pathdata
     * @return BrowseNode
     */
    public function setPathdata($pathdata)
    {
        $this->pathdata = $pathdata;
    
        return $this;
    }

    /**
     * Get pathdata
     *
     * @return array 
     */
    public function getPathdata()
    {
        return $this->pathdata;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return BrowseNode
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    
        return $this;
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set root
     *
     * @param boolean $root
     * @return BrowseNode
     */
    public function setRoot($root)
    {
        $this->root = $root;
    
        return $this;
    }

    /**
     * Get root
     *
     * @return boolean 
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * Add parents
     *
     * @param \Scott\DataBundle\Entity\BrowseNode $parents
     * @return BrowseNode
     */
    public function addParent(\Scott\DataBundle\Entity\BrowseNode $parents)
    {
        $this->parents[] = $parents;
    
        return $this;
    }

    /**
     * Remove parents
     *
     * @param \Scott\DataBundle\Entity\BrowseNode $parents
     */
    public function removeParent(\Scott\DataBundle\Entity\BrowseNode $parents)
    {
        $this->parents->removeElement($parents);
    }

    /**
     * Get parents
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getParents()
    {
        return $this->parents;
    }

    /**
     * Add children
     *
     * @param \Scott\DataBundle\Entity\BrowseNode $children
     * @return BrowseNode
     */
    public function addChildren(\Scott\DataBundle\Entity\BrowseNode $children)
    {
        $this->children[] = $children;
    
        return $this;
    }

    /**
     * Remove children
     *
     * @param \Scott\DataBundle\Entity\BrowseNode $children
     */
    public function removeChildren(\Scott\DataBundle\Entity\BrowseNode $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getChildren()
    {
        return $this->children;
    }
}
