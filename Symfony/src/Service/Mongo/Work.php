<?php
declare(strict_types=1);

namespace App\Service\Mongo;

use App\Service\Mongo;
use App\Repository\WorkRepository;
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

    public function titleSearch(
        ?int $work_id,
        string $query_raw,
        int $count,
        int $page = 1
    ): array
    {
        $query = preg_replace( '/[^a-zA-Z0-9 ]/', '', $query_raw);

        $page--;
        list ($works, $matches) = $this->floatingWorkSearch($query, $page, $count, $work_id);

        $work = false;
        if ($work_id){
            foreach ($works as $w){
                if ($work_id == $w['work_id']){
                    $work = $w;
                    continue;
                }
            }
        }
        $page++;

        $hasmore = true;
        if (($page * ($count +1)) >= $matches){
            $hasmore = false;
        }

        return compact(
            'work_id',
            'work',
            'works',
            'query',
            'query_raw',
            'count',
            'page',
            'hasmore',
            'matches'
        );
    }

    public function authorSearch(
            $query_raw,
            $count,
            $page = 1
        )
        {

        $mongodb            = $this->mongo->getDb();
        $worksdb            = $mongodb->works;

        $query = preg_replace(
            '/[^a-zA-Z0-9 ]/', '',
            strtolower(trim($query_raw)));

        $page--;

        $cursor = $worksdb->find(
            array('amzn_authorlist'     => $query)
        );

        $cursor->sort(array('publication_date'  => -1));
        $cursor->limit($count);
        $cursor->skip($page * $count);

        foreach ($cursor as $work) {
            $works[] = $work;
        }
        $page++;

        $hasmore = true;
        $matches = $cursor->count();
        if (($page * ($count +1)) >= $cursor->count()){
            $hasmore = false;
        }

        return compact(
            'works',
            'hasmore',
            'page',
            'query',
            'query_raw',
            'matches'
        );
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

    private function floatingWorkSearch(
        string $query,
        int $page,
        int $count,
        int $work_id = null
    ): array
    {
        // does a search, and if work_id is in the results
        // float it to the top of the list

        $mongodb            = $this->mongo->getDb();
        $worksdb            = $mongodb->works;

        $works              = [];

        if (0 == $page){
            $count--;
            $cursor = $worksdb->find( ['work_id' => (int)$work_id]);
            $cursor->limit(1);
            foreach ($cursor as $work) {
                $works[] = $work;
            }
        }

        $cursor = $worksdb->find(
            array('$text' => array('$search'    => $query)),
            array('score' => array('$meta'      => 'textScore'))
        );

        $cursor->sort(array('score' =>  array('$meta' => 'textScore')));
        $cursor->limit($count);
        $cursor->skip($page * $count);

        foreach ($cursor as $work) {
            if ($work['work_id'] != $work_id){
                $works[] = $work;
            }
        }

        return [$works, 1+$cursor->count()];
    }
}
