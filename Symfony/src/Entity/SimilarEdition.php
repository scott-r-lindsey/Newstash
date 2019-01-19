<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(
 *      name="similar_edition",
 *      indexes={
 *          @ORM\Index(name="idx_similar_rank", columns={"xrank"}),
 *      }
 * )
 */

class SimilarEdition{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Edition", inversedBy="similar_editions")
     * @ORM\JoinColumn(name="edition_asin", referencedColumnName="asin", nullable=false)
     */
    private $edition;

    /**
     * @ORM\ManyToOne(targetEntity="Edition")
     * @ORM\JoinColumn(name="similar_asin", referencedColumnName="asin", nullable=false)
     */
    private $similar;

    //-------------------------------------------------------------------------------

    /** @ORM\Column(type="smallint", nullable=false, name="xrank") */
    private $rank;

    //-------------------------------------------------------------------------------
    // ./bin/console make:entity --regenerate
    //-------------------------------------------------------------------------------

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

    public function getEdition(): ?Edition
    {
        return $this->edition;
    }

    public function setEdition(?Edition $edition): self
    {
        $this->edition = $edition;

        return $this;
    }

    public function getSimilar(): ?Edition
    {
        return $this->similar;
    }

    public function setSimilar(?Edition $similar): self
    {
        $this->similar = $similar;

        return $this;
    }

}
