<?php
declare(strict_types=1);

namespace App\Service\Mongo;

use App\Service\Mongo;
use Psr\Log\LoggerInterface;

class Work
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

    public function byCategory(
        int $node_id,
        int $page = 1
    ): array
    {
        $page--;

        $mongodb            = $this->mongo->getDb();
        $nodesCollection    = $mongodb->nodes;
        $worksCollection    = $mongodb->works;

        $node = false;
        if (0 == $page){
            $cursor     = $nodesCollection->find(['id' => $node_id]);
            $node       = $cursor->getNext();
        }

        $children = $node['children'];

        if ($children){
            usort($children, function($a, $b) {
                return strnatcmp( $a['name'], $b['name']);
            });
        }

        // --------------------------------------------------------------------
        // books in category

        $count = 25;

        $cursor = $worksCollection->find([
            'browse_node' => ['$in' => [$node_id]]
        ]);

        $cursor->sort(['amzn_salesrank' => 1]);
        $cursor->limit($count);
        $cursor->skip($page * $count);

        $works = [];
        foreach ($cursor as $work) {
            $works[] = $work;
        }
        $page++;
        $matches = $cursor->count();

        $hasmore = true;

        if (($page * ($count +1)) >= $matches){
            $hasmore = false;
        }

        return compact('node_id', 'works', 'page', 'node', 'matches', 'hasmore', 'children');
    }
}
