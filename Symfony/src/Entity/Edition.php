<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(
 *      name="edition"
 * )
 */

class Edition{

    /**
     * @ORM\Id
     * @ORM\Column(type="string", unique=true, length=20, nullable=false)
     */
    private $asin;

    // ------------------------------------------------------------------------

    /**
     * @ORM\ManyToMany(targetEntity="BrowseNode", inversedBy="editions")
     * @ORM\JoinTable(name="browsenode_edition",
     *      joinColumns={@ORM\JoinColumn(name="edition_asin", referencedColumnName="asin")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="browsenode_id", referencedColumnName="id")}
     * )
     */
    private $browse_nodes;

    /**
     * @ORM\ManyToMany(targetEntity="BrowseNode", inversedBy="editions_primary")
     * @ORM\JoinTable(name="primary_browsenode_edition",
     *      joinColumns={@ORM\JoinColumn(name="edition_asin", referencedColumnName="asin")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="browsenode_id", referencedColumnName="id")}
     * )
     */
    private $primary_browse_nodes;

    /**
     * @ORM\ManyToOne(targetEntity="Format", inversedBy="editions")
     * @ORM\JoinColumn(name="format_id", referencedColumnName="id", nullable=true)
     */
    private $format;

    // ------------------------------------------------------------------------

    /** @ORM\Column(type="bigint", unique=false, nullable=true) */
    private $isbn;

    /** @ORM\Column(type="string", length=255, nullable=true) */
    private $title;

    /** @ORM\Column(type="string", length=255, nullable=true) */
    private $pub_title;

    /** @ORM\Column(type="string", length=255, nullable=true) */
    private $pub_subtitle;

    /** @ORM\Column(type="datetime") */
    private $created_at;

    /** @ORM\Column(type="datetime") */
    private $updated_at;

    /** @ORM\Column(type="datetime", nullable=true) */
    private $publisher_scraped_at;

    /** @ORM\Column(type="datetime", nullable=true) */
    private $amzn_updated_at;

    /** @ORM\Column(type="datetime", nullable=true) */
    private $amzn_scraped_at;

    /** @ORM\Column(type="string", length=255, nullable=true) */
    private $url;

    /** @ORM\Column(type="string", length=255, nullable=true) */
    private $amzn_authordisplay;

    /** @ORM\Column(type="array", nullable=true) */
    private $amzn_authorlist;

    /** @ORM\Column(type="string", length=255, nullable=true) */
    private $amzn_small_cover;

    /** @ORM\Column(type="integer", nullable=true) */
    private $amzn_small_cover_x;

    /** @ORM\Column(type="integer", nullable=true) */
    private $amzn_small_cover_y;

    /** @ORM\Column(type="string", length=255, nullable=true) */
    private $amzn_medium_cover;

    /** @ORM\Column(type="integer", nullable=true) */
    private $amzn_medium_cover_x;

    /** @ORM\Column(type="integer", nullable=true) */
    private $amzn_medium_cover_y;

    /** @ORM\Column(type="string", length=255, nullable=true) */
    private $amzn_large_cover;

    /** @ORM\Column(type="integer", nullable=true) */
    private $amzn_large_cover_x;

    /** @ORM\Column(type="integer", nullable=true) */
    private $amzn_large_cover_y;

    /** @ORM\Column(type="decimal", scale=2, precision=7, nullable=true) */
    protected $list_price;

    /** @ORM\Column(type="decimal", scale=2, precision=7, nullable=true) */
    protected $amzn_price;

    /** @ORM\Column(type="string", length=255, nullable=true) */
    protected $amzn_publisher;

    /** @ORM\Column(type="integer", nullable=true) */
    private $amzn_salesrank;

    /** @ORM\Column(type="string", length=255, nullable=true) */
    private $amzn_format;

    /** @ORM\Column(type="smallint", nullable=true) */
    protected $pages;

    /** @ORM\Column(type="datetime", nullable=true) */
    protected $publication_date;

    /** @ORM\Column(type="datetime", nullable=true) */
    protected $release_date;

    /** @ORM\Column(type="text", nullable=true) */
    protected $description;

    /** @ORM\Column(type="string", length=255, nullable=true) */
    protected $amzn_editorial_review_source;

    /** @ORM\Column(type="text", nullable=true) */
    protected $amzn_editorial_review;

    /** @ORM\Column(type="text", nullable=true) */
    protected $amzn_alternatives;

    /** @ORM\Column(type="string", length=255, nullable=true) */
    private $sig;

    /** @ORM\Column(type="boolean") */
    private $active = false;

    /** @ORM\Column(type="boolean") */
    private $deleted = false;

    /** @ORM\Column(type="string", length=255, nullable=true) */
    private $amzn_edition;

    /** @ORM\Column(type="string", length=255, nullable=true) */
    private $amzn_manufacturer;

    /** @ORM\Column(type="string", length=255, nullable=true) */
    private $amzn_brand;

    /** @ORM\Column(type="boolean") */
    private $apparent_amzn_osi = false;

    /** @ORM\Column(type="boolean") */
    private $rejected = false;

    /** @ORM\Column(type="string", length=255, nullable=true) */
    private $slug;

    //-------------------------------------------------------------------------------

    public function slugify($title, $author = false): string
    {

        if ($author){
            $slug = $title . ' by ' . $author;
        }
        else{
            $slug = $title;
        }

        $slug = preg_replace('/[^a-zA-Z0-9 ]/', '', $slug);
        $slug = strtolower($slug);
        $slug = str_replace(' ', '-', $slug);

        return $slug;
    }
    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function UpdateSlug(): string
    {
        return $this->slug = $this->slugify($this->title, $this->amzn_authordisplay);
    }

    public function setAsin(?string $asin): self
    {
        $this->asin = $asin;

        return $this;
    }

    public function getIsbn(): ?string
    {
        return $this->isbn;
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

    public function evaluateActiveLogic(
        $noprice,
        $format_id,
        $price,
        $deleted,
        $rejected,
        \DateTime $amzn_updated_at = null,
        \DateTime $pub_date = null): bool
    {
        // scans a book to see if it should be considered active
        // book must have:
        //  * amznUpdatedAt
        //  * a pubedate less than 4 weeks out
        //  * a format
        //  * either a price or a format that is marked "noprice"
        //  * not marked "deleted"
        //  * not marked "rejected"
        //
        // logic is broken out into separate function to be available
        // when not using the ORM

        if (!$format_id){
            return false;
        }

        $active = false;
        if (    ($amzn_updated_at) &&
                ($pub_date) &&
                ($pub_date < new \DateTime('+4 weeks'))){

            if ($rejected){
                return false;
            }

            if (($price) || ($noprice)){
                $active = true;
            }

            if ($deleted){
                $active = false;
            }
        }
        return $active;
    }

    /**
     * @ORM\PreFlush()
     * @ORM\PreUpdate()
     */
    public function evaluateActive(): void
    {
        $noprice        = false;
        $format_id      = false;

        if ($format = $this->getFormat()){
            $noprice    = $format->getNoprice();
            $format_id  = $format->getId();
        }

        $this->setActive(
            $this->evaluateActiveLogic(
                $noprice,
                $format_id,
                $this->getAmznPrice(),
                $this->getDeleted(),
                $this->getRejected(),
                $this->getAmznUpdatedAt(),
                $this->getPublicationDate()
            )
        );
    }


    //-------------------------------------------------------------------------------
    // ./bin/console make:entity --regenerate
    //-------------------------------------------------------------------------------

    public function __construct()
    {
        $this->browse_nodes = new ArrayCollection();
        $this->primary_browse_nodes = new ArrayCollection();
    }

    public function getAsin(): ?string
    {
        return $this->asin;
    }

    public function setIsbn(?string $isbn): self
    {
        $this->isbn = $isbn;

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

    public function getPubTitle(): ?string
    {
        return $this->pub_title;
    }

    public function setPubTitle(?string $pub_title): self
    {
        $this->pub_title = $pub_title;

        return $this;
    }

    public function getPubSubtitle(): ?string
    {
        return $this->pub_subtitle;
    }

    public function setPubSubtitle(?string $pub_subtitle): self
    {
        $this->pub_subtitle = $pub_subtitle;

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

    public function getPublisherScrapedAt(): ?\DateTimeInterface
    {
        return $this->publisher_scraped_at;
    }

    public function setPublisherScrapedAt(?\DateTimeInterface $publisher_scraped_at): self
    {
        $this->publisher_scraped_at = $publisher_scraped_at;

        return $this;
    }

    public function getAmznUpdatedAt(): ?\DateTimeInterface
    {
        return $this->amzn_updated_at;
    }

    public function setAmznUpdatedAt(?\DateTimeInterface $amzn_updated_at): self
    {
        $this->amzn_updated_at = $amzn_updated_at;

        return $this;
    }

    public function getAmznScrapedAt(): ?\DateTimeInterface
    {
        return $this->amzn_scraped_at;
    }

    public function setAmznScrapedAt(?\DateTimeInterface $amzn_scraped_at): self
    {
        $this->amzn_scraped_at = $amzn_scraped_at;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getAmznAuthordisplay(): ?string
    {
        return $this->amzn_authordisplay;
    }

    public function setAmznAuthordisplay(?string $amzn_authordisplay): self
    {
        $this->amzn_authordisplay = $amzn_authordisplay;

        return $this;
    }

    public function getAmznAuthorlist(): ?array
    {
        return $this->amzn_authorlist;
    }

    public function setAmznAuthorlist(?array $amzn_authorlist): self
    {
        $this->amzn_authorlist = $amzn_authorlist;

        return $this;
    }

    public function getAmznSmallCover(): ?string
    {
        return $this->amzn_small_cover;
    }

    public function setAmznSmallCover(?string $amzn_small_cover): self
    {
        $this->amzn_small_cover = $amzn_small_cover;

        return $this;
    }

    public function getAmznSmallCoverX(): ?int
    {
        return $this->amzn_small_cover_x;
    }

    public function setAmznSmallCoverX(?int $amzn_small_cover_x): self
    {
        $this->amzn_small_cover_x = $amzn_small_cover_x;

        return $this;
    }

    public function getAmznSmallCoverY(): ?int
    {
        return $this->amzn_small_cover_y;
    }

    public function setAmznSmallCoverY(?int $amzn_small_cover_y): self
    {
        $this->amzn_small_cover_y = $amzn_small_cover_y;

        return $this;
    }

    public function getAmznMediumCover(): ?string
    {
        return $this->amzn_medium_cover;
    }

    public function setAmznMediumCover(?string $amzn_medium_cover): self
    {
        $this->amzn_medium_cover = $amzn_medium_cover;

        return $this;
    }

    public function getAmznMediumCoverX(): ?int
    {
        return $this->amzn_medium_cover_x;
    }

    public function setAmznMediumCoverX(?int $amzn_medium_cover_x): self
    {
        $this->amzn_medium_cover_x = $amzn_medium_cover_x;

        return $this;
    }

    public function getAmznMediumCoverY(): ?int
    {
        return $this->amzn_medium_cover_y;
    }

    public function setAmznMediumCoverY(?int $amzn_medium_cover_y): self
    {
        $this->amzn_medium_cover_y = $amzn_medium_cover_y;

        return $this;
    }

    public function getAmznLargeCover(): ?string
    {
        return $this->amzn_large_cover;
    }

    public function setAmznLargeCover(?string $amzn_large_cover): self
    {
        $this->amzn_large_cover = $amzn_large_cover;

        return $this;
    }

    public function getAmznLargeCoverX(): ?int
    {
        return $this->amzn_large_cover_x;
    }

    public function setAmznLargeCoverX(?int $amzn_large_cover_x): self
    {
        $this->amzn_large_cover_x = $amzn_large_cover_x;

        return $this;
    }

    public function getAmznLargeCoverY(): ?int
    {
        return $this->amzn_large_cover_y;
    }

    public function setAmznLargeCoverY(?int $amzn_large_cover_y): self
    {
        $this->amzn_large_cover_y = $amzn_large_cover_y;

        return $this;
    }

    public function getListPrice()
    {
        return $this->list_price;
    }

    public function setListPrice($list_price): self
    {
        $this->list_price = $list_price;

        return $this;
    }

    public function getAmznPrice()
    {
        return $this->amzn_price;
    }

    public function setAmznPrice($amzn_price): self
    {
        $this->amzn_price = $amzn_price;

        return $this;
    }

    public function getAmznPublisher(): ?string
    {
        return $this->amzn_publisher;
    }

    public function setAmznPublisher(?string $amzn_publisher): self
    {
        $this->amzn_publisher = $amzn_publisher;

        return $this;
    }

    public function getAmznSalesrank(): ?int
    {
        return $this->amzn_salesrank;
    }

    public function setAmznSalesrank(?int $amzn_salesrank): self
    {
        $this->amzn_salesrank = $amzn_salesrank;

        return $this;
    }

    public function getAmznFormat(): ?string
    {
        return $this->amzn_format;
    }

    public function setAmznFormat(?string $amzn_format): self
    {
        $this->amzn_format = $amzn_format;

        return $this;
    }

    public function getPages(): ?int
    {
        return $this->pages;
    }

    public function setPages(?int $pages): self
    {
        $this->pages = $pages;

        return $this;
    }

    public function getPublicationDate(): ?\DateTimeInterface
    {
        return $this->publication_date;
    }

    public function setPublicationDate(?\DateTimeInterface $publication_date): self
    {
        $this->publication_date = $publication_date;

        return $this;
    }

    public function getReleaseDate(): ?\DateTimeInterface
    {
        return $this->release_date;
    }

    public function setReleaseDate(?\DateTimeInterface $release_date): self
    {
        $this->release_date = $release_date;

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

    public function getAmznEditorialReviewSource(): ?string
    {
        return $this->amzn_editorial_review_source;
    }

    public function setAmznEditorialReviewSource(?string $amzn_editorial_review_source): self
    {
        $this->amzn_editorial_review_source = $amzn_editorial_review_source;

        return $this;
    }

    public function getAmznEditorialReview(): ?string
    {
        return $this->amzn_editorial_review;
    }

    public function setAmznEditorialReview(?string $amzn_editorial_review): self
    {
        $this->amzn_editorial_review = $amzn_editorial_review;

        return $this;
    }

    public function getAmznAlternatives(): ?string
    {
        return $this->amzn_alternatives;
    }

    public function setAmznAlternatives(?string $amzn_alternatives): self
    {
        $this->amzn_alternatives = $amzn_alternatives;

        return $this;
    }

    public function getSig(): ?string
    {
        return $this->sig;
    }

    public function setSig(?string $sig): self
    {
        $this->sig = $sig;

        return $this;
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

    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getAmznEdition(): ?string
    {
        return $this->amzn_edition;
    }

    public function setAmznEdition(?string $amzn_edition): self
    {
        $this->amzn_edition = $amzn_edition;

        return $this;
    }

    public function getAmznManufacturer(): ?string
    {
        return $this->amzn_manufacturer;
    }

    public function setAmznManufacturer(?string $amzn_manufacturer): self
    {
        $this->amzn_manufacturer = $amzn_manufacturer;

        return $this;
    }

    public function getAmznBrand(): ?string
    {
        return $this->amzn_brand;
    }

    public function setAmznBrand(?string $amzn_brand): self
    {
        $this->amzn_brand = $amzn_brand;

        return $this;
    }

    public function getApparentAmznOsi(): ?bool
    {
        return $this->apparent_amzn_osi;
    }

    public function setApparentAmznOsi(bool $apparent_amzn_osi): self
    {
        $this->apparent_amzn_osi = $apparent_amzn_osi;

        return $this;
    }

    public function getRejected(): ?bool
    {
        return $this->rejected;
    }

    public function setRejected(bool $rejected): self
    {
        $this->rejected = $rejected;

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

    /**
     * @return Collection|BrowseNode[]
     */
    public function getBrowseNodes(): Collection
    {
        return $this->browse_nodes;
    }

    public function addBrowseNode(BrowseNode $browseNode): self
    {
        if (!$this->browse_nodes->contains($browseNode)) {
            $this->browse_nodes[] = $browseNode;
        }

        return $this;
    }

    public function removeBrowseNode(BrowseNode $browseNode): self
    {
        if ($this->browse_nodes->contains($browseNode)) {
            $this->browse_nodes->removeElement($browseNode);
        }

        return $this;
    }

    /**
     * @return Collection|BrowseNode[]
     */
    public function getPrimaryBrowseNodes(): Collection
    {
        return $this->primary_browse_nodes;
    }

    public function addPrimaryBrowseNode(BrowseNode $primaryBrowseNode): self
    {
        if (!$this->primary_browse_nodes->contains($primaryBrowseNode)) {
            $this->primary_browse_nodes[] = $primaryBrowseNode;
        }

        return $this;
    }

    public function removePrimaryBrowseNode(BrowseNode $primaryBrowseNode): self
    {
        if ($this->primary_browse_nodes->contains($primaryBrowseNode)) {
            $this->primary_browse_nodes->removeElement($primaryBrowseNode);
        }

        return $this;
    }

    public function getFormat(): ?Format
    {
        return $this->format;
    }

    public function setFormat(?Format $format): self
    {
        $this->format = $format;

        return $this;
    }
}
