<?php
declare(strict_types=1);

namespace App\Controller;

use App\Repository\WorkRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class WorkController extends AbstractController
{

    /**
     * @Route("/book/{work_id}/{slug}", requirements={"work_id" = "^\d+$"}, name="work",  methods={"GET"})
     * @Template()
     */
    public function workAction(
        WorkRepository $workRepository,
        $work_id,
        $slug = ''
    ): array
    {

        $work_id = (int)$work_id;

        $work = $workRepository->getWork($work_id);

        if (!$work) {
            throw $this->createNotFoundException('The book does not exist');
        }

        $front_edition = $work->getFrontEdition();
        $correct_slug = $front_edition->updateSlug();

        $url = $this->generateUrl('work', array(
            'work_id'   => $work_id,
            'slug'      => $correct_slug));

        if ($slug != $correct_slug){
            return $this->redirect($url, 301);
        }

        $similar_works  = $workRepository->getSimilarWorks($work_id);
        $bns            = $workRepository->getBrowseNodes($work);
        $editions       = $workRepository->getActiveEditions($work_id);
        //$review_count   = $workReviews->getReviewCount($work_id);
        $review_count   = [];

        // --------------------------------------------------------------------
        return compact('work', 'url', 'similar_works', 'bns', 'review_count', 'editions');




/*

        $work = $workManager->getWork($work_id);

        if (!$work) {
            throw $this->createNotFoundException('The book does not exist');
        }

        $front_edition = $work->getFrontEdition();
        $correct_slug = $front_edition->updateSlug();

        $url = $this->generateUrl('work', array(
            'work_id'   => $work_id,
            'slug'      => $correct_slug));

        if ($slug != $correct_slug){
            return $this->redirect($url, 301);
        }

        $similar_works  = $workManager->getSimilarWorks($work_id);
        $bns            = $workManager->getBrowseNodes($work);
        $editions       = $workManager->getActiveEditions($work_id);
        //$review_count   = $workReviews->getReviewCount($work_id);
        $review_count   = [];

        // --------------------------------------------------------------------
        return compact('work', 'url', 'similar_works', 'bns', 'review_count', 'editions');


*/






/*

        $em = $this->get('doctrine.orm.entity_manager');
        $dbh = $em->getConnection();
        $workMaster = $this->get('bookstash.work.master');
        $workReviews = $this->get('bookstash.work.reviews');

        // --------------------------------------------------------------------
        $work = $workMaster->getWork($work_id);

        if (!$work) {
            throw $this->createNotFoundException('The book does not exist');
        }

        $front_edition = $work->getFrontEdition();
        $correct_slug = $front_edition->updateSlug();

        $url = $this->generateUrl('work', array(
            'work_id'   => $work_id,
            'slug'      => $correct_slug));      

        if ($slug != $correct_slug){
            return $this->redirect($url, 301);       
        }

        $similar_works  = $workMaster->getSimilarWorks($work_id);
        $bns            = $workMaster->getBrowseNodes($work);    
        $editions       = $workMaster->getActiveEditions($work_id);
        $review_count   = $workReviews->getReviewCount($work_id);

        // --------------------------------------------------------------------
        return compact('work', 'url', 'similar_works', 'bns', 'review_count', 'editions');

    */
        return [];
    }
}
