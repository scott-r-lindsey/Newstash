<?php
declare(strict_types=1);

namespace App\Service\Mongo;

use App\Service\Mongo;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;

class Work
{
    private $em;
    private $logger;
    private $mongo;

    const SEARCH_LIMIT = 25;

    public function __construct(
        EntityManagerInterface $em,
        LoggerInterface $logger,
        Mongo $mongo
    )
    {
        $this->em                   = $em;
        $this->logger               = $logger;
        $this->mongo                = $mongo;
    }

    private function titleSearch(
        int $work_id,
        string $query_raw,
        int $page = 1
    )
    {






    }







    // title search

    // author search

    // isbn search

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


        $cursor = $worksCollection->find([
            'browse_node' => ['$in' => [$node_id]]
        ]);

        $cursor->sort(['amzn_salesrank' => 1]);
        $cursor->limit(self::SEARCH_LIMIT);
        $cursor->skip($page * self::SEARCH_LIMIT);

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
