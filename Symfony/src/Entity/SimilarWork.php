<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SimilarWorkRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(
 *      name="similar_work",
 *      indexes={
 *      }
 * )
 */

class SimilarWork{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Work", inversedBy="similar_works")
     * @ORM\JoinColumn(name="work_id", referencedColumnName="id", nullable=false)
     */
    private $work;

    /**
     * @ORM\ManyToOne(targetEntity="Work")
     * @ORM\JoinColumn(name="similar_id", referencedColumnName="id", nullable=false)
     */
    private $similar;

    //-------------------------------------------------------------------------------
    // ./bin/console make:entity --regenerate
    //-------------------------------------------------------------------------------

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSimilar(): ?Work
    {
        return $this->similar;
    }

    public function setSimilar(?Work $similar): self
    {
        $this->similar = $similar;

        return $this;
    }
}
