<?php
// src/Entity/User.php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(
 *      name="user",
 *      indexes={
 *           @ORM\Index(name="idx_email", columns={"email"}),
 *           @ORM\Index(name="idx_facebookId", columns={"facebook_id"}),
 *           @ORM\Index(name="idx_googleId", columns={"google_id"})
 *      }
 * )
 */
class User extends BaseUser
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="Review", mappedBy="user")
     */
    private $reviews;

    /**
     * @ORM\OneToMany(targetEntity="Rating", mappedBy="user")
     */
    private $ratings;

    /**
     * @ORM\OneToMany(targetEntity="Readit", mappedBy="user")
     */
    private $readit;

    /**
     * @ORM\OneToMany(targetEntity="Post", mappedBy="user")
     */
    private $posts;

    /**
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="user")
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity="Stash", mappedBy="user")
     */
    private $stashs;

    /**
     * @ORM\OneToMany(targetEntity="Flag", mappedBy="user")
     */
    private $flags;

    /**
     * @ORM\OneToMany(targetEntity="ReviewLike", mappedBy="user")
     */
    private $review_likes;

    /**
     * @ORM\OneToMany(targetEntity="Friendship", mappedBy="user")
     **/
    private $friendships;

    //-------------------------------------------------------------------------------

    /** @ORM\Column(type="string", length=255, nullable=true) */
    protected $facebookId;

    /** @ORM\Column(type="string", length=255, nullable=true) */
    protected $facebook_access_token;

    /** @ORM\Column(type="string", length=255, nullable=true) */
    protected $googleId;

    /** @ORM\Column(type="string", length=255, nullable=true) */
    protected $google_access_token;

    /** @ORM\Column(type="string", length=255, nullable=true) */
    protected $google_profile_pic;

    /** @ORM\Column(type="string", length=255, nullable=true) */
    protected $first_name;

    /** @ORM\Column(type="string", length=255, nullable=true) */
    protected $last_name;

    /** @ORM\Column(type="string", length=10, nullable=true) */
    protected $gender;

    /** @ORM\Column(type="string", length=40, nullable=true) */
    protected $locale;

    /** @ORM\Column(type="datetime") */
    private $created_at;

    /** @ORM\Column(type="datetime") */
    private $updated_at;

    /** @ORM\Column(type="integer", nullable=false) */
    private $rating_count = 0;

    /** @ORM\Column(type="integer", nullable=false) */
    private $review_count = 0;

    /** @ORM\Column(type="integer", nullable=false) */
    private $comment_count = 0;

    /** @ORM\Column(type="array", nullable=true) */
    protected $display_prefs = array();

    /** @ORM\Column(type="boolean", options={"default":false}) */
    private $locked = false;

    public function __construct()
    {
        parent::__construct();
        $this->reviews = new ArrayCollection();
        $this->ratings = new ArrayCollection();
        $this->readit = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->stashs = new ArrayCollection();
        $this->flags = new ArrayCollection();
        $this->review_likes = new ArrayCollection();
        $this->friendships = new ArrayCollection();
    }

    //-------------------------------------------------------------------------------

    /**
     * @ORM\PrePersist
     */
    public function setUsernameFromEmail(){
        $this->setUserName(strtolower($this->getEmail()));
    }

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

    //-------------------------------------------------------------------------------
    public function setFirstName($firstName){
        $this->first_name = ucfirst($firstName);

        return $this;
    }

    public function setLastName($lastName){
        $this->last_name = ucfirst($lastName);

        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getEmailCanonical(){
        list($emailCanonical) = array_reverse(explode(':', $this->emailCanonical));
        return $emailCanonical;
    }

    public function setEmailCanonical($emailCanonical)
    {

        $this->emailCanonical = strtolower($emailCanonical);

        return $this;
    }

    public function getName(){
        return $this->getFirstName() . ' ' . $this->getLastName();
    }

    public function getAvatarUrl($size = 80){
        $email = $this->getEmailCanonical();

        if (!is_null($this->getFacebookId())){
            return
                'https://graph.facebook.com/' .
                $this->getFacebookId() .
                "/picture?width=$size&height=$size";
        }
        else{
            $md5 = md5($email);
            return "//www.gravatar.com/avatar/$md5?s=$size";
        }

        // FIXME no google pic?

        return $email;
    }
    /**
     * Get display_prefs
     *
     * @return array
     */
    public function getDisplayPrefs()
    {
        $prefs = $this->display_prefs;

        if (!isset($prefs['hide'])) {
            $prefs['hide'] = array();
        }

        return $prefs;
    }

    //-------------------------------------------------------------------------------
    // ./bin/console make:entity --regenerate
    //-------------------------------------------------------------------------------

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFacebookId(): ?string
    {
        return $this->facebookId;
    }

    public function setFacebookId(?string $facebookId): self
    {
        $this->facebookId = $facebookId;

        return $this;
    }

    public function getFacebookAccessToken(): ?string
    {
        return $this->facebook_access_token;
    }

    public function setFacebookAccessToken(?string $facebook_access_token): self
    {
        $this->facebook_access_token = $facebook_access_token;

        return $this;
    }

    public function getGoogleId(): ?string
    {
        return $this->googleId;
    }

    public function setGoogleId(?string $googleId): self
    {
        $this->googleId = $googleId;

        return $this;
    }

    public function getGoogleAccessToken(): ?string
    {
        return $this->google_access_token;
    }

    public function setGoogleAccessToken(?string $google_access_token): self
    {
        $this->google_access_token = $google_access_token;

        return $this;
    }

    public function getGoogleProfilePic(): ?string
    {
        return $this->google_profile_pic;
    }

    public function setGoogleProfilePic(?string $google_profile_pic): self
    {
        $this->google_profile_pic = $google_profile_pic;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): self
    {
        $this->locale = $locale;

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

    public function getRatingCount(): ?int
    {
        return $this->rating_count;
    }

    public function setRatingCount(int $rating_count): self
    {
        $this->rating_count = $rating_count;

        return $this;
    }

    public function getReviewCount(): ?int
    {
        return $this->review_count;
    }

    public function setReviewCount(int $review_count): self
    {
        $this->review_count = $review_count;

        return $this;
    }

    public function getCommentCount(): ?int
    {
        return $this->comment_count;
    }

    public function setCommentCount(int $comment_count): self
    {
        $this->comment_count = $comment_count;

        return $this;
    }

    public function setDisplayPrefs(?array $display_prefs): self
    {
        $this->display_prefs = $display_prefs;

        return $this;
    }

    /**
     * @return Collection|Review[]
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): self
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews[] = $review;
            $review->setUser($this);
        }

        return $this;
    }

    public function removeReview(Review $review): self
    {
        if ($this->reviews->contains($review)) {
            $this->reviews->removeElement($review);
            // set the owning side to null (unless already changed)
            if ($review->getUser() === $this) {
                $review->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Rating[]
     */
    public function getRatings(): Collection
    {
        return $this->ratings;
    }

    public function addRating(Rating $rating): self
    {
        if (!$this->ratings->contains($rating)) {
            $this->ratings[] = $rating;
            $rating->setUser($this);
        }

        return $this;
    }

    public function removeRating(Rating $rating): self
    {
        if ($this->ratings->contains($rating)) {
            $this->ratings->removeElement($rating);
            // set the owning side to null (unless already changed)
            if ($rating->getUser() === $this) {
                $rating->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Readit[]
     */
    public function getReadit(): Collection
    {
        return $this->readit;
    }

    public function addReadit(Readit $readit): self
    {
        if (!$this->readit->contains($readit)) {
            $this->readit[] = $readit;
            $readit->setUser($this);
        }

        return $this;
    }

    public function removeReadit(Readit $readit): self
    {
        if ($this->readit->contains($readit)) {
            $this->readit->removeElement($readit);
            // set the owning side to null (unless already changed)
            if ($readit->getUser() === $this) {
                $readit->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Post[]
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts[] = $post;
            $post->setUser($this);
        }

        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->contains($post)) {
            $this->posts->removeElement($post);
            // set the owning side to null (unless already changed)
            if ($post->getUser() === $this) {
                $post->setUser(null);
            }
        }

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
            $comment->setUser($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getUser() === $this) {
                $comment->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Stash[]
     */
    public function getStashs(): Collection
    {
        return $this->stashs;
    }

    public function addStash(Stash $stash): self
    {
        if (!$this->stashs->contains($stash)) {
            $this->stashs[] = $stash;
            $stash->setUser($this);
        }

        return $this;
    }

    public function removeStash(Stash $stash): self
    {
        if ($this->stashs->contains($stash)) {
            $this->stashs->removeElement($stash);
            // set the owning side to null (unless already changed)
            if ($stash->getUser() === $this) {
                $stash->setUser(null);
            }
        }

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
            $flag->setUser($this);
        }

        return $this;
    }

    public function removeFlag(Flag $flag): self
    {
        if ($this->flags->contains($flag)) {
            $this->flags->removeElement($flag);
            // set the owning side to null (unless already changed)
            if ($flag->getUser() === $this) {
                $flag->setUser(null);
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
            $reviewLike->setUser($this);
        }

        return $this;
    }

    public function removeReviewLike(ReviewLike $reviewLike): self
    {
        if ($this->review_likes->contains($reviewLike)) {
            $this->review_likes->removeElement($reviewLike);
            // set the owning side to null (unless already changed)
            if ($reviewLike->getUser() === $this) {
                $reviewLike->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Friendship[]
     */
    public function getFriendships(): Collection
    {
        return $this->friendships;
    }

    public function addFriendship(Friendship $friendship): self
    {
        if (!$this->friendships->contains($friendship)) {
            $this->friendships[] = $friendship;
            $friendship->setUser($this);
        }

        return $this;
    }

    public function removeFriendship(Friendship $friendship): self
    {
        if ($this->friendships->contains($friendship)) {
            $this->friendships->removeElement($friendship);
            // set the owning side to null (unless already changed)
            if ($friendship->getUser() === $this) {
                $friendship->setUser(null);
            }
        }

        return $this;
    }

    public function getLocked(): ?bool
    {
        return $this->locked;
    }

    public function setLocked(bool $locked): self
    {
        $this->locked = $locked;

        return $this;
    }
}
