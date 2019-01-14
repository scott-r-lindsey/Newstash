<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
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
    // ./bin/console doctrine:generate:entities Scott/DataBundle/Entity/BrowseNode
    //-------------------------------------------------------------------------------





}
