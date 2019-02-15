<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ScoreRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(
 *      name="score"
 * )
 */

class Score{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="Work", inversedBy="score")
     * @ORM\JoinColumn(name="work_id", referencedColumnName="id")
     **/
    private $work;

    //-------------------------------------------------------------------------------

    /** @ORM\Column(type="string", length=10) */
    private $score;

    /** @ORM\Column(type="integer") */
    private $total;

    /** @ORM\Column(type="integer") */
    private $ones;

    /** @ORM\Column(type="integer") */
    private $twos;

    /** @ORM\Column(type="integer") */
    private $threes;

    /** @ORM\Column(type="integer") */
    private $fours;

    /** @ORM\Column(type="integer") */
    private $fives;

    /** @ORM\Column(type="datetime") */
    private $created_at;

    /** @ORM\Column(type="datetime") */
    private $updated_at;

    //-------------------------------------------------------------------------------

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updatedTimestamps(){
        $this->setUpdatedAt(new \DateTime());

        if(null == $this->getCreatedAt()) {
            $this->setCreatedAt(new \DateTime());
        }
    }

    //-------------------------------------------------------------------------------
    // ./bin/console make:entity --regenerate
    //-------------------------------------------------------------------------------

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getScore(): ?string
    {
        return $this->score;
    }

    public function setScore(string $score): self
    {
        $this->score = $score;

        return $this;
    }

    public function getTotal(): ?int
    {
        return $this->total;
    }

    public function setTotal(int $total): self
    {
        $this->total = $total;

        return $this;
    }

    public function getOnes(): ?int
    {
        return $this->ones;
    }

    public function setOnes(int $ones): self
    {
        $this->ones = $ones;

        return $this;
    }

    public function getTwos(): ?int
    {
        return $this->twos;
    }

    public function setTwos(int $twos): self
    {
        $this->twos = $twos;

        return $this;
    }

    public function getThrees(): ?int
    {
        return $this->threes;
    }

    public function setThrees(int $threes): self
    {
        $this->threes = $threes;

        return $this;
    }

    public function getFours(): ?int
    {
        return $this->fours;
    }

    public function setFours(int $fours): self
    {
        $this->fours = $fours;

        return $this;
    }

    public function getFives(): ?int
    {
        return $this->fives;
    }

    public function setFives(int $fives): self
    {
        $this->fives = $fives;

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
