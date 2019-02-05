<?php
// src/Entity/User.php

namespace App\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class User extends BaseUser
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

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

    public function setFirstName(?string $first_name): self
    {
        $this->first_name = $first_name;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(?string $last_name): self
    {
        $this->last_name = $last_name;

        return $this;
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

    public function getDisplayPrefs(): ?array
    {
        return $this->display_prefs;
    }

    public function setDisplayPrefs(?array $display_prefs): self
    {
        $this->display_prefs = $display_prefs;

        return $this;
    }
}
