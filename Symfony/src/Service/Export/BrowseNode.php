<?php
declare(strict_types=1);

namespace App\Service\Export;

use App\Lib\OutputingService;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use App\Service\Mongo;

class BrowseNode extends OutputingService
{
    private $logger;
    private $em;
    private $mongo;

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $em,
        Mongo $mongo
    )
    {
        $this->logger               = $logger;
        $this->em                   = $em;
        $this->mongo                = $mongo;
    }

    public function exportNodes(string $targetCollection = 'nodes_new'): array
    {
        $mongodb        = $this->mongo->getDb();
        $em             = $this->em;
        $dbh            = $this->em->getConnection();

        //---------------------------------------------------------------------------
        // get nodes

        $this->writeln( "<comment>Fetching browsenodes from MySQL...</comment>");

        $sql        = 'SELECT id, name, slug, pathdata FROM browsenode';
        $sth        = $dbh->query($sql);
        $nodes      = [];

        while ($result = $sth->fetch()){

            $pathdata = unserialize($result['pathdata']);
            $names = array();

            if (is_array($pathdata)){
                foreach ($pathdata as $p){
                    $names[] = $p['name'];
                }
            }

            $nodes[$result['id']] = array(
                'id'        => (int)$result['id'],
                'name'      => $result['name'],
                'slug'      => $result['slug'],
                'pathdata'  => unserialize($result['pathdata']),
                'children'  => array(),
                'count'     => 0
            );
        }

        //---------------------------------------------------------------------------
        // get category counts

        $sql = '
            SELECT
                browsenode.id AS id, COUNT(*) AS count
            FROM
                browsenode, browsenode_edition, edition, work
            WHERE
                browsenode_edition.edition_asin = edition.asin AND
                edition.work_id = work.id AND
                browsenode.id = browsenode_edition.browsenode_id AND
                work.deleted = 0
            GROUP BY browsenode.id';

        $sth = $dbh->query($sql);
        while ($result = $sth->fetch()){
            $nodes[(int)$result['id']]['count'] = (int)$result['count'];
        }

        //---------------------------------------------------------------------------
        // get children

        $sql = '
            SELECT
                browsenode.id AS id,
                browsenode_browsenode.child_id AS child_id
            FROM
                browsenode, browsenode_browsenode
            WHERE
                browsenode.id = browsenode_browsenode.parent_id';

        $sth = $dbh->query($sql);
        while ($result = $sth->fetch()){
            //$nodes[$result['id']]['children'][] = $result['child_id'];
            //$nodes[$result['id']]['children'][$result['child_id']] = $nodes[$result['child_id']]['count'];
            $nodes[$result['id']]['children'][] = array(
                'id'        => (int)$result['child_id'],
                'name'      => $nodes[(int)$result['child_id']]['name'],
                'slug'      => $nodes[(int)$result['child_id']]['slug'],
                'count'     => $nodes[(int)$result['child_id']]['count'],
            );
        }

        $this->writeln( "<comment>Exporting browseNodes to Mongo...</comment>");
        foreach ($nodes as $node){
            $mongodb->$targetCollection->insert($node);
        }

        $this->writeln( "<info>BrowseNodes export complete</info>");

        return $nodes;
    }
}
