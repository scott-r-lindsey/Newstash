<?php
namespace App\GraphQL\Resolver;

use App\Entity\Format;
use Doctrine\ORM\EntityManagerInterface;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Type\Definition\ResolveInfo;
use Overblog\DataLoader\DataLoader;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Relay\Connection\Output\Connection;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;

class FormatResolver implements ResolverInterface {

    private $em;

    public function __construct(
        EntityManagerInterface $em
    )
    {
        $this->em           = $em;
    }

    // ------------------------------------------------------------------------

    public function __invoke(ResolveInfo $info, $value, Argument $args)
    {
        $method = $info->fieldName;
        return $this->$method($value, $args);
    }

    // ------------------------------------------------------------------------
    // getters

    public function id(Format $format): int
    {
        return $format->getId();
    }
    public function description(Format $format): string
    {
        return $format->getDescription();
    }
    public function paper(Format $format): bool
    {
        return $format->getPaper();
    }
    public function e(Format $format): bool
    {
        return $format->getE();
    }
    public function audio(Format $format): bool
    {
        return $format->getAudio();
    }
    public function noprice(Format $format): bool
    {
        return $format->getNoprice();
    }

    // ------------------------------------------------------------------------

}
