<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ReviewRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(
 *      name="review"
 * )
 */

class Review{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="reviews")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Work", inversedBy="reviews")
     * @ORM\JoinColumn(name="work_id", referencedColumnName="id", nullable=false)
     */
    private $work;

    /**
     * @ORM\OneToMany(targetEntity="Flag", mappedBy="review")
     */
    private $flags;

    /**
     * @ORM\OneToMany(targetEntity="ReviewLike", mappedBy="review")
     * @ORM\JoinColumn(name="work_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $review_likes;

    //-------------------------------------------------------------------------------

    /** @ORM\Column(type="smallint", nullable=true) */
    private $stars;

    /** @ORM\Column(type="string", length=255, nullable=true) */
    private $title;

    /** @ORM\Column(type="text", length=20000) */
    private $text;

    /** @ORM\Column(type="string", length=16, nullable=false) */
    private $ipaddr;

    /** @ORM\Column(type="string", length=255, nullable=false) */
    private $useragent;

    /** @ORM\Column(type="integer") */
    private $likes = 0;

    /** @ORM\Column(type="datetime") */
    private $created_at;

    /** @ORM\Column(type="datetime") */
    private $updated_at;

    /** @ORM\Column(type="datetime", nullable=true) */
    private $started_reading_at;

    /** @ORM\Column(type="datetime", nullable=true) */
    private $finished_reading_at;

    /** @ORM\Column(type="boolean") */
    private $deleted = false;

    public function __construct()
    {
        $this->flags = new ArrayCollection();
        $this->review_likes = new ArrayCollection();
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

    public function getStars(): ?int
    {
        return $this->stars;
    }

    public function setStars(?int $stars): self
    {
        $this->stars = $stars;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getIpaddr(): ?string
    {
        return $this->ipaddr;
    }

    public function setIpaddr(string $ipaddr): self
    {
        $this->ipaddr = $ipaddr;

        return $this;
    }

    public function getUseragent(): ?string
    {
        return $this->useragent;
    }

    public function setUseragent(string $useragent): self
    {
        $this->useragent = $useragent;

        return $this;
    }

    public function getLikes(): ?int
    {
        return $this->likes;
    }

    public function setLikes(int $likes): self
    {
        $this->likes = $likes;

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

    public function getStartedReadingAt(): ?\DateTimeInterface
    {
        return $this->started_reading_at;
    }

    public function setStartedReadingAt(?\DateTimeInterface $started_reading_at): self
    {
        $this->started_reading_at = $started_reading_at;

        return $this;
    }

    public function getFinishedReadingAt(): ?\DateTimeInterface
    {
        return $this->finished_reading_at;
    }

    public function setFinishedReadingAt(?\DateTimeInterface $finished_reading_at): self
    {
        $this->finished_reading_at = $finished_reading_at;

        return $this;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

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

    /**
     * @return Collection|Flag[]
     */
    public function getFlags(): Collection
    {
        return $this->flags;
    }

    public function addFlag(Flag $flag): self
    {
        if (!$this->flags->contains($flag)) {
            $this->flags[] = $flag;
            $flag->setReview($this);
        }

        return $this;
    }

    public function removeFlag(Flag $flag): self
    {
        if ($this->flags->contains($flag)) {
            $this->flags->removeElement($flag);
            // set the owning side to null (unless already changed)
            if ($flag->getReview() === $this) {
                $flag->setReview(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ReviewLike[]
     */
    public function getReviewLikes(): Collection
    {
        return $this->review_likes;
    }

    public function addReviewLike(ReviewLike $reviewLike): self
    {
        if (!$this->review_likes->contains($reviewLike)) {
            $this->review_likes[] = $reviewLike;
            $reviewLike->setReview($this);
        }

        return $this;
    }

    public function removeReviewLike(ReviewLike $reviewLike): self
    {
        if ($this->review_likes->contains($reviewLike)) {
            $this->review_likes->removeElement($reviewLike);
            // set the owning side to null (unless already changed)
            if ($reviewLike->getReview() === $this) {
                $reviewLike->setReview(null);
            }
        }

        return $this;
    }

    //-------------------------------------------------------------------------------
    // ./bin/console make:entity --regenerate
    //-------------------------------------------------------------------------------



}
