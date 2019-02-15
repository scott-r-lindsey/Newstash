<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\Mongo\BrowseNode;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\Mongo\Work;

class SearchController extends AbstractController
{


    // ------------------------------------------------------------------------
    // non-routable

    /**
     * @Template()
     */
    public function categoryMenu(
        BrowseNode $bn,
        $node_ids
    ): array
    {
        // category drop down menu generated from mongo

        if (!is_array($node_ids)){
            $fixed = [];
            foreach (explode(',', (string)$node_ids) as $node_id){
                $fixed[] = (int)$node_id;
            }
            $node_ids = $fixed;
        }

        $nodes = $bn->findNodesById($node_ids);

        return array(
            'nodes' => $nodes
        );
    }

    /**
     * @Route("/browse/category/{node_id}/{slug}", requirements={"node_id" = "^\d+$"}, name="search_browse_category", methods={"GET"})
     * @Template()
     */
    public function browseCategoryAction(
        Work $workSearcher,
        Request $request,
        $node_id,
        $slug
    ){

        $page           = $request->query->get('page', 1);
        $results        = $workSearcher->byCategory((int)$node_id, (int)$page);

        $results['slug'] = $slug;

        return $results;
    }

}
