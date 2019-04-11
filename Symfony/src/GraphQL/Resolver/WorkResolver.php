<?php
namespace App\GraphQL\Resolver;

use App\Entity\Work;
use Doctrine\ORM\EntityManagerInterface;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Type\Definition\ResolveInfo;
use Overblog\DataLoader\DataLoader;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Relay\Connection\Output\Connection;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;

class WorkResolver implements ResolverInterface {

    private $em;
    private $editionLoader;
    private $workLoader;

    public function __construct(
        EntityManagerInterface $em
    )
    {
        $this->em           = $em;
    }

    public function setWorkLoader(DataLoader $workLoader)
    {
        $this->workLoader = $workLoader;
    }

    public function setEditionLoader(DataLoader $editionLoader)
    {
        $this->editionLoader = $editionLoader;
    }

    // ------------------------------------------------------------------------

    public function __invoke(ResolveInfo $info, $value, Argument $args)
    {
        $method = $info->fieldName;
        return $this->$method($value, $args);
    }

    public function work(int $id)
    {
        return $this->workLoader->load($id);
    }

    public function editions(Work $work, Argument $args)
    {
        $editions = $work->getEditions();
        $paginator = new Paginator(function ($offset, $limit) use ($editions) {
            return $editions->slice($offset, $limit ?? 10);
        });
        return $paginator->auto($args, count($editions));
    }

    public function similar_works(Work $work, Argument $args)
    {
        $similarWorks = $work->getSimilarWorks();
        $paginator = new Paginator(function ($offset, $limit) use ($similarWorks) {
            $slice = $similarWorks->slice($offset, $limit ?? 10);
            return $this->worksFromSimilarWorksPromises($similarWorks);
        });

        return $paginator->auto($args, count($similarWorks));
    }

    public function front_edition(Work $work, Argument $args): Promise
    {
        $edition = $work->getFrontEdition();

        return $this->editionLoader->load($edition->getAsin());
    }

    // ------------------------------------------------------------------------
    // getters

    public function id(Work $work): int
    {
        return $work->getId();
    }
    public function title(Work $work): string
    {
        return $work->getTitle();
    }

    // ------------------------------------------------------------------------

    private function worksFromSimilarWorksPromises($similarWorks): array
    {
        $ret = [];

        foreach ($similarWorks as $sw) {
            $ret[] = $this->workLoader->load($sw->getSimilar()->getId());
        }
        return $ret;
    }
}
