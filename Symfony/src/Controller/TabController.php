<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Work;
use App\Repository\WorkRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class TabController extends AbstractController
{
    /**
     * @Route("/user/tabcontent/{type}", name="user_tabcontent", methods={"GET"});
     * @Template()
     *
     * User's tab bar
     */
    public function getTabContent(
        Request $request,
        WorkRepository $workRepository,
        string $type,
        UserInterface $user
    ): array
    {
        $descriptions = [
            'toread'        => 'To Read',
            'reading'       => 'Reading',
            'readit'        => 'Read It',
            'reviews'       => 'Reviewed',
            'ratings'       => 'Rated'
        ];

        $sorts = ['added', 'alpha', 'pubdate', 'bestseller'];

        $page       = (int)$request->query->get('page', 1);
        $sort       = $request->query->get('sort', 'added');
        $reverse    = $request->query->get('reverse', false);
        $reverse    = $reverse ? true : false;
        $perpage    = 20;

        if (!in_array($sort, $sorts)) {
            $sort = 'added';
        }

        if (!isset($descriptions[$type])) {
            throw $this->createNotFoundException("Unknown list $type");
        }

        list ($total, $works) = $workRepository->findUserStatusCountAndWorks(
            $user,
            $type,
            $page,
            $perpage,
            $sort,
            $reverse
        );

        $description = $descriptions[$type];

        return compact(
            'total',
            'works',
            'page',
            'perpage',
            'sort',
            'reverse',
            'description',
            'type'
        );
    }
}
