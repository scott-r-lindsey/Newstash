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

    // ------------------------------------------------------------------------

    public function title(Edition $edition): ?string
    {
        return $edition->getTitle();
    }
    public function isbn(Edition $edition): ?string
    {
        return $edition->getIsbn();
    }
    public function amzn_updated_at(Edition $edition): ?string
    {
        return $edition->getAmznUpdatedAt()->format('Y-m-d\TH:i:s.u\Z');
    }
    public function url(Edition $edition): ?string
    {
        return $edition->getUrl();
    }
    public function amzn_authordisplay(Edition $edition): ?string
    {
        return $edition->getAmznAuthordisplay();
    }
    public function amzn_authorlist(Edition $edition): ?string
    {
        return json_encode($edition->getAmznAuthorlist());
    }
    public function amzn_small_cover(Edition $edition): ?string
    {
        return $edition->getAmznSmallCover();
    }
    public function amzn_small_cover_x(Edition $edition): ?string
    {
        return $edition->getAmznSmallCoverX();
    }
    public function amzn_small_cover_y(Edition $edition): ?string
    {
        return $edition->getAmznSmallCoverY();
    }
    public function amzn_medium_cover(Edition $edition): ?string
    {
        return $edition->getAmznMediumCover();
    }
    public function amzn_medium_cover_x(Edition $edition): ?string
    {
        return $edition->getAmznMediumCoverX();
    }
    public function amzn_medium_cover_y(Edition $edition): ?string
    {
        return $edition->getAmznMediumCoverY();
    }
    public function amzn_large_cover(Edition $edition): ?string
    {
        return $edition->getAmznLargeCover();
    }
    public function amzn_large_cover_x(Edition $edition): ?string
    {
        return $edition->getAmznLargeCoverX();
    }
    public function amzn_large_cover_y(Edition $edition): ?string
    {
        return $edition->getAmznLargeCoverY();
    }
    public function list_price(Edition $edition): ?float
    {
        return $edition->getListPrice();
    }
    public function amzn_price(Edition $edition): ?float
    {
        return $edition->getAmznPrice();
    }
    public function amzn_publisher(Edition $edition): ?string
    {
        return $edition->getAmznPublisher();
    }
    public function amzn_salesrank(Edition $edition): ?string
    {
        return $edition->getAmznSalesrank();
    }
    public function amzn_format(Edition $edition): ?string
    {
        return $edition->getAmznFormat();
    }
    public function pages(Edition $edition): ?int
    {
        return $edition->getPages();
    }
    public function publication_date(Edition $edition): ?string
    {
        return $edition->getPublicationDate()->format('Y-m-d\TH:i:s.u\Z');
    }
    public function release_date(Edition $edition): ?string
    {
        return $edition->getReleaseDate()->format('Y-m-d\TH:i:s.u\Z');
    }
    public function description(Edition $edition): ?string
    {
        return $edition->getDescription();
    }
    public function amzn_editorial_review_source(Edition $edition): ?string
    {
        return $edition->getAmznEditorialReviewSource();
    }
    public function amzn_editorial_review(Edition $edition): ?string
    {
        return $edition->getAmznEditorialReview();
    }
    public function amzn_edition(Edition $edition): ?string
    {
        return $edition->getAmznEdition();
    }
    public function amzn_manufacturer(Edition $edition): ?string
    {
        return $edition->getAmznManufacturer();
    }
    public function amzn_brand(Edition $edition): ?string
    {
        return $edition->getAmznBrand();
    }
    public function apparent_amzn_osi(Edition $edition): ?bool
    {
        return $edition->getApparentAmznOsi();
    }
    public function slug(Edition $edition): ?string
    {
        return $edition->getSlug();
    }
}
