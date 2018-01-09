<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class TestController extends Controller
{

    const NODE_ROOT     = 283155;

    /**
     * @Route("/test/node")
     * @Route("/test/node/{n1}")
     * @Route("/test/node/{n1}/{n2}")
     * @Route("/test/node/{n1}/{n2}/{n3}")
     * @Route("/test/node/{n1}/{n2}/{n3}/{n4}")
     * @Route("/test/node/{n1}/{n2}/{n3}/{n4}/{n5}")
     * @Route("/test/node/{n1}/{n2}/{n3}/{n4}/{n5}/{n6}")
     * @Route("/test/node/{n1}/{n2}/{n3}/{n4}/{n5}/{n6}/{n7}")
     * @Route("/test/node/{n1}/{n2}/{n3}/{n4}/{n5}/{n6}/{n7}/{n8}")
     * @Route("/test/node/{n1}/{n2}/{n3}/{n4}/{n5}/{n6}/{n7}/{n8}/{n9}")
     * @Route("/test/node/{n1}/{n2}/{n3}/{n4}/{n5}/{n6}/{n7}/{n8}/{n9}/{n10}")
     * @Method("GET")
     * @Template()
     */
    public function nodeAction(
        ?int $n1 = null,
        ?int $n2 = null,
        ?int $n3 = null,
        ?int $n4 = null,
        ?int $n5 = null,
        ?int $n6 = null,
        ?int $n7 = null,
        ?int $n8 = null,
        ?int $n9 = null,
        ?int $n10 = null
    ): array{

        $repo = $this->get('doctrine.orm.entity_manager')
            ->getRepository('App:BrowseNode');

        $works      = [];
        $nodes      = [];
        $children   = [];

        $nodes[] = $repo->findOneById(self::NODE_ROOT);

        if ($n1){ $nodes[] = $repo->findOneById($n1); }
        if ($n2){ $nodes[] = $repo->findOneById($n2); }
        if ($n3){ $nodes[] = $repo->findOneById($n3); }
        if ($n4){ $nodes[] = $repo->findOneById($n4); }
        if ($n5){ $nodes[] = $repo->findOneById($n5); }
        if ($n6){ $nodes[] = $repo->findOneById($n6); }
        if ($n7){ $nodes[] = $repo->findOneById($n7); }
        if ($n8){ $nodes[] = $repo->findOneById($n8); }
        if ($n9){ $nodes[] = $repo->findOneById($n9); }
        if ($n10){ $nodes[] = $repo->findOneById($n10); }

        $last = $nodes[count($nodes)-1];
        $children = $last->getChildren();
        array_shift($nodes);

        return compact('works', 'nodes', 'children');

    }
}
