<?php
declare(strict_types=1);

namespace App\Service\Mongo;

use App\Service\Mongo;
use Psr\Log\LoggerInterface;

class BrowseNode
{
    private $em;
    private $mongodb;

    public function __construct(
        LoggerInterface $logger,
        Mongo $mongo
    )
    {
        $this->logger               = $logger;
        $this->mongo                = $mongo;
    }

    public function findNodesById(
        array $node_ids,
        string $table = 'nodes'
    ): array
    {
        $mongodb            = $this->mongo->getDb();
        $nodesCollection    = $mongodb->$table;

        $cursor = $nodesCollection->find(
        [
            'id' => ['$in' => $node_ids]
        ]);

        $nodes_by_id    = [];
        foreach ($cursor as $node) {
            $nodes_by_id[(string)$node['id']] = $node;
        }

        $nodes          = [];
        foreach ($node_ids as $node_id){
            $nodes[] = $nodes_by_id[(string)$node_id];
        }

        return $nodes;
    }
}

