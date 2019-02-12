<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\Mongo\BrowseNode;
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
    public function browseCategoryAction(Request $request, $node_id, $slug){
        $workSearcher   = $this->container->get('bookstash.search.works');
        return $workSearcher->doCategorySearch($request, $node_id, $slug);
    }

}
