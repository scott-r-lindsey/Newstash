<?php
namespace App\GraphQL\Resolver;

use App\Entity\BrowseNode;
use Doctrine\ORM\EntityManagerInterface;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Type\Definition\ResolveInfo;
use Overblog\DataLoader\DataLoader;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Relay\Connection\Output\Connection;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;

class BrowseNodeResolver implements ResolverInterface {

    private $em;

    public function __construct(
        EntityManagerInterface $em
    )
    {
        $this->em           = $em;
    }

    public function setBrowseNodeLoader(DataLoader $browseNodeLoader)
    {
        $this->browseNodeLoader = $browseNodeLoader;
    }

    // ------------------------------------------------------------------------

    public function __invoke(ResolveInfo $info, $value, Argument $args)
    {
        $method = $info->fieldName;
        return $this->$method($value, $args);
    }

    public function BrowseNode(int $id)
    {
        return $this->browseNodeLoader->load($id);
    }

    public function Children(BrowseNode $BrowseNode)
    {
        $children = $BrowseNode->getChildren();

        $ret = [];
        foreach ($children as $c){
            $ret[] = $this->browseNodeLoader->load($c->getId());
        }

        return $ret;
    }

/*
    // not implemented

    // When we need these, they will be implemented such that they
    // data via App\Service\Mongo\Work::byCategory

    public function Editions(BrowseNode $BrowseNode, Argument $args)
    {


    }
    public function PrimaryEditions(BrowseNode $BrowseNode, Argument $args)
    {

    }
*/

    // ------------------------------------------------------------------------
    // getters

    public function id(BrowseNode $BrowseNode): string
    {
        return (string)$BrowseNode->getId();
    }

    public function name(BrowseNode $browseNode): string
    {
        return $browseNode->getName();
    }
    public function description(BrowseNode $browseNode): string
    {
        return $browseNode->getDescription();
    }
    public function pathdata(BrowseNode $browseNode): string
    {
        return json_encode($browseNode->getPathdata());
    }
    public function slug(BrowseNode $browseNode): string
    {
        return $browseNode->getSlug();
    }
    public function root(BrowseNode $browseNode): bool
    {
        return $browseNode->getRoot();
    }
    public function leaf(BrowseNode $browseNode): bool
    {
        return $browseNode->getLeaf();
    }
}
