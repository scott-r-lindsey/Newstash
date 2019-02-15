<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PostRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(
 *      name="post",
 *      indexes={
 *          @ORM\Index(name="idx_edition_update", columns={"updated_at"}),
 *          @ORM\Index(name="idx_edition_create", columns={"created_at"}),
 *          @ORM\Index(name="idx_edition_create", columns={"published_at"}),
 *          @ORM\Index(name="idx_year", columns={"year"}),
 *      }
 * )
 */

class Post{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="posts")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="post")
     */
    private $comments;

    //-------------------------------------------------------------------------------

    /** @ORM\Column(type="boolean") */
    private $active = false;

    /** @ORM\Column(type="string", length=255, nullable=false) */
    private $title;

    /** @ORM\Column(type="string", length=255, nullable=false) */
    private $slug;

    /** @ORM\Column(type="integer", nullable=false) */
    private $year;

    /** @ORM\Column(type="string", length=255, nullable=true) */
    private $image;

    /** @ORM\Column(type="integer", nullable=true) */
    private $image_x;

    /** @ORM\Column(type="integer", nullable=true) */
    private $image_y;

    /** @ORM\Column(type="string", length=255, nullable=true) */
    private $description;

    /** @ORM\Column(type="text", name="xlead") */
    private $lead;

    /** @ORM\Column(type="text") */
    private $fold;

    /** @ORM\Column(type="datetime") */
    private $created_at;

    /** @ORM\Column(type="datetime") */
    private $updated_at;

    /** @ORM\Column(type="datetime", nullable=true) */
    private $published_at;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
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
            $this->year = date("Y");
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(int $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getImageX(): ?int
    {
        return $this->image_x;
    }

    public function setImageX(?int $image_x): self
    {
        $this->image_x = $image_x;

        return $this;
    }

    public function getImageY(): ?int
    {
        return $this->image_y;
    }

    public function setImageY(?int $image_y): self
    {
        $this->image_y = $image_y;

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

    public function getLead(): ?string
    {
        return $this->lead;
    }

    public function setLead(string $lead): self
    {
        $this->lead = $lead;

        return $this;
    }

    public function getFold(): ?string
    {
        return $this->fold;
    }

    public function setFold(string $fold): self
    {
        $this->fold = $fold;

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

    public function getPublishedAt(): ?\DateTimeInterface
    {
        return $this->published_at;
    }

    public function setPublishedAt(?\DateTimeInterface $published_at): self
    {
        $this->published_at = $published_at;

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
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setPost($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getPost() === $this) {
                $comment->setPost(null);
            }
        }

        return $this;
    }


}
