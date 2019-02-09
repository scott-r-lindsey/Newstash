<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{


    // ------------------------------------------------------------------------
    // non-routable

    /**
     * @Template()
     */
    public function categoryMenuAction($node_ids): array
    {
        // category drop down menu generated from mongo

        if (!is_array($node_ids)){
            $fixed = [];
            foreach (explode(',', $node_ids) as $node_id){
                $fixed[] = (int)$node_id;
            }
            $node_ids = $fixed;
        }

        $mongodb = $this->container->get('bookstash.search.mongodb_factory')->getMongoDB();
        $nodes_collection = $mongodb->nodes;

        $cursor = $nodes_collection->find([
            'id' => array('$in' => $node_ids)
        ]);

        $nodes_by_id = array();
        foreach ($cursor as $node) {
            $nodes_by_id[(string)$node['id']] = $node;
        }

        $nodes = array();
        foreach ($node_ids as $node_id){
            $nodes[] = $nodes_by_id[(string)$node_id];
        }

        return array(
            'nodes' => $nodes
        );
    }
}
