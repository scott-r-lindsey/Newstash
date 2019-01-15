<?php
declare(strict_types=1);

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{

    const NODE_ROOT     = 283155;

    /**
     * @Route("/test/node", methods={"GET"})
     * @Route("/test/node/{n1}", methods={"GET"})
     * @Route("/test/node/{n1}/{n2}", methods={"GET"})
     * @Route("/test/node/{n1}/{n2}/{n3}", methods={"GET"})
     * @Route("/test/node/{n1}/{n2}/{n3}/{n4}", methods={"GET"})
     * @Route("/test/node/{n1}/{n2}/{n3}/{n4}/{n5}", methods={"GET"})
     * @Route("/test/node/{n1}/{n2}/{n3}/{n4}/{n5}/{n6}", methods={"GET"})
     * @Route("/test/node/{n1}/{n2}/{n3}/{n4}/{n5}/{n6}/{n7}", methods={"GET"})
     * @Route("/test/node/{n1}/{n2}/{n3}/{n4}/{n5}/{n6}/{n7}/{n8}", methods={"GET"})
     * @Route("/test/node/{n1}/{n2}/{n3}/{n4}/{n5}/{n6}/{n7}/{n8}/{n9}", methods={"GET"})
     * @Route("/test/node/{n1}/{n2}/{n3}/{n4}/{n5}/{n6}/{n7}/{n8}/{n9}/{n10}", methods={"GET"})
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
