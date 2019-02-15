<?php
declare(strict_types=1);

namespace App\Controller;

use App\Repository\ReviewRepository;
use App\Repository\ScoreRepository;
use App\Repository\WorkRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class WorkController extends AbstractController
{

    /**
     * @Route("/book/{work_id}/{slug}", requirements={"work_id" = "^\d+$"}, name="work",  methods={"GET"})
     * @Template()
     */
    public function work(
        ReviewRepository $reviewRepository,
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
        $review_count   = $reviewRepository->count([
            'work'      => $work_id,
            'deleted'   => 0
        ]);

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

    /**
     * @Route("/book/reviews/{work_id}/{sort}/{page}", requirements={"work_id" = "^\d+$", "sort" = "^new|old|liked$", "page" = "^\d+$"}, name="work_reviews", methods={"GET"})
     * @Route("/book/reviews/{work_id}/{sort}/{page}/{stars}", requirements={"work_id" = "^\d+$", "sort" = "^new|old|liked$", "page" = "^\d+$", "stars" = "^\d+|any$"}, name="work_reviews_bystar", methods={"GET"})
     * @Route("/book/reviews/{work_id}/{sort}/{page}/{stars}/{user_id}", requirements={"work_id" = "^\d+$", "sort" = "^new|old|liked$", "page" = "^\d+$", "stars" = "^\d+|any$", "user_id" = "^\d+$"}, name="work_reviews_byuser", methods={"GET"})
     * @Template()
     */
    public function reviews(
        ReviewRepository $reviewRepository,
        $work_id,
        $sort,
        $page,
        $stars = false,
        $user_id = false
    ){

        $page--;
        $count          = 50;

        //$user_id = (int)$user_id;
        $work_id = (int)$work_id;


        // STUB


/*

        if ($user_id) {
            $user_review    = $reviewRepository->getUserReview($user_id, $work_id);
        }
*/

        //stub
        $reviews = [];
        $review_count = 0;
        $hasmore = false;
        $stars = 0;
        $matches = 0;

        return compact('reviews', 'review_count', 'work_id', 'hasmore', 'matches', 'stars', 'sort', 'page', 'user_id');
    }

    /**
     * @Template()
     */
    public function ratings(
        ReviewRepository $reviewRepository,
        ScoreRepository $scoreRepository,
        $work_id
    )
    {
        $work_id = (int)$work_id;

        $score              = $scoreRepository->findOneByWork($work_id);
        $review_count       = $reviewRepository->count([
            'work'      => $work_id,
            'deleted'   => 0
        ]);

        return compact('score', 'review_count', 'work_id');
    }
}
