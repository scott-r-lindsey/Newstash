<?php
namespace App\GraphQL\Resolver;

use App\Entity\Work;
use Doctrine\ORM\EntityManagerInterface;
use GraphQL\Type\Definition\ResolveInfo;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Relay\Connection\Output\Connection;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;

class WorkResolver implements ResolverInterface {

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

    public function resolve(int $id)
    {
        return $this->em->find(Work::class, $id);
    }

    public function id(Work $work): int
    {
        return $work->getId();
    }
    public function title(Work $work): string
    {
        return $work->getTitle();
    }

}
