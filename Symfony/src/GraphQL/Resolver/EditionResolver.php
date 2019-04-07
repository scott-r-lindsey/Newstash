<?php
namespace App\GraphQL\Resolver;

use App\Entity\Edition;
use Doctrine\ORM\EntityManagerInterface;
use GraphQL\Type\Definition\ResolveInfo;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Relay\Connection\Output\Connection;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use DateTime;

class EditionResolver implements ResolverInterface {

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function __invoke(ResolveInfo $info, $value, Argument $args)
    {
        $method = $info->fieldName;
        return $this->$method($value, $args);
    }

    public function resolve(string $asin)
    {
        return $this->em->find(Edition::class, $asin);
    }

    public function asin(Edition $edition): string
    {
        return $edition->getAsin();
    }
    public function title(Edition $edition): string
    {
        return $edition->getTitle();
    }
    public function isbn(Edition $edition): string
    {
        return $edition->getIsbn();
    }
    public function amzn_updated_at(Edition $edition): DateTime
    {
        return $edition->getAmznUpdateAt();
    }



#    /** @ORM\Column(type="string", length=255, nullable=true) */
#    private $url;
#
#    /** @ORM\Column(type="string", length=255, nullable=true) */
#    private $amzn_authordisplay;
#
#    /** @ORM\Column(type="array", nullable=true) */
#    private $amzn_authorlist;
#
#    /** @ORM\Column(type="string", length=255, nullable=true) */
#    private $amzn_small_cover;
#
#    /** @ORM\Column(type="integer", nullable=true) */
#    private $amzn_small_cover_x;
#
#    /** @ORM\Column(type="integer", nullable=true) */
#    private $amzn_small_cover_y;
#
#    /** @ORM\Column(type="string", length=255, nullable=true) */
#    private $amzn_medium_cover;
#
#    /** @ORM\Column(type="integer", nullable=true) */
#    private $amzn_medium_cover_x;
#
#    /** @ORM\Column(type="integer", nullable=true) */
#    private $amzn_medium_cover_y;
#
#    /** @ORM\Column(type="string", length=255, nullable=true) */
#    private $amzn_large_cover;
#
#    /** @ORM\Column(type="integer", nullable=true) */
#    private $amzn_large_cover_x;
#
#    /** @ORM\Column(type="integer", nullable=true) */
#    private $amzn_large_cover_y;
#
#    /** @ORM\Column(type="decimal", scale=2, precision=9, nullable=true) */
#    protected $list_price;
#
#    /** @ORM\Column(type="decimal", scale=2, precision=9, nullable=true) */
#    protected $amzn_price;
#
#    /** @ORM\Column(type="string", length=255, nullable=true) */
#    protected $amzn_publisher;
#
#    /** @ORM\Column(type="integer", nullable=true) */
#    private $amzn_salesrank;
#
#    /** @ORM\Column(type="string", length=255, nullable=true) */
#    private $amzn_format;
#
#    /** @ORM\Column(type="integer", nullable=true) */
#    protected $pages;
#
#    /** @ORM\Column(type="datetime", nullable=true) */
#    protected $publication_date;
#
#    /** @ORM\Column(type="datetime", nullable=true) */
#    protected $release_date;
#
#    /** @ORM\Column(type="text", nullable=true) */
#    protected $description;
#
#    /** @ORM\Column(type="string", length=255, nullable=true) */
#    protected $amzn_editorial_review_source;
#
#    /** @ORM\Column(type="text", nullable=true) */
#    protected $amzn_editorial_review;
#
#    /** @ORM\Column(type="string", length=255, nullable=true) */
#    private $amzn_edition;
#
#    /** @ORM\Column(type="string", length=255, nullable=true) */
#    private $amzn_manufacturer;
#
#    /** @ORM\Column(type="string", length=255, nullable=true) */
#    private $amzn_brand;
#
#    /** @ORM\Column(type="boolean", options={"default":false}) */
#    private $apparent_amzn_osi = false;
#
#    /** @ORM\Column(type="string", length=255, nullable=true) */
#    private $slug;


}
