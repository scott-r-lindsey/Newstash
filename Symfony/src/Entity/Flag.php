<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FlagRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(
 *      name="flag",
 *      indexes={
 *          @ORM\Index(name="idx_sorted", columns={"sorted"})
 *      }
 * )
 */

class Flag{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="flags")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Reason", inversedBy="flags")
     * @ORM\JoinColumn(name="reason_id", referencedColumnName="id", nullable=true)
     */
    private $reason;

    /**
     * @ORM\ManyToOne(targetEntity="Comment", inversedBy="flags")
     * @ORM\JoinColumn(name="comment_id", referencedColumnName="id", nullable=true)
     */
    private $comment;

    /**
     * @ORM\ManyToOne(targetEntity="Review", inversedBy="flags")
     * @ORM\JoinColumn(name="review_id", referencedColumnName="id", nullable=true)
     */
    private $review;

    //-------------------------------------------------------------------------------

    /** @ORM\Column(type="boolean") */
    private $sorted = false;

    /** @ORM\Column(type="string", length=500, nullable=true) */
    private $message;

    /** @ORM\Column(type="string", length=16, nullable=false) */
    private $ipaddr;

    /** @ORM\Column(type="string", length=255, nullable=false) */
    private $useragent;

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

    public function getSorted(): ?bool
    {
        return $this->sorted;
    }

    public function setSorted(bool $sorted): self
    {
        $this->sorted = $sorted;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getReason(): ?Reason
    {
        return $this->reason;
    }

    public function setReason(?Reason $reason): self
    {
        $this->reason = $reason;

        return $this;
    }

    public function getComment(): ?Comment
    {
        return $this->comment;
    }

    public function setComment(?Comment $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getReview(): ?Review
    {
        return $this->review;
    }

    public function setReview(?Review $review): self
    {
        $this->review = $review;

        return $this;
    }

    //-------------------------------------------------------------------------------


}
