<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Review;
use App\Entity\Work;
use App\Repository\ReviewRepository;
use App\Repository\ScoreRepository;
use App\Repository\WorkRepository;
use App\Service\ReviewManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class WorkController extends AbstractController
{

    /**
     * @Route("/book/{work}/{review}/{slug}", requirements={"work" = "^\d+$", "review" = "^\d+$"}, name="work_review",  methods={"GET"})
     * @Template()
     *
     * // STUB for future work review page
     */
    public function workReview(
        Work $work,
        Review $review,
        string $slug
    ): array
    {

        $correct_slug = $review->getSlug();

        $url = $this->generateUrl('work_review', [
            'work'      => $work->getId(),
            'review'    => $review->getId(),
            'slug'      => $correct_slug
        ]);

        if ($slug != $correct_slug){
            $this->redirect($url, 301);
        }


        return [];
    }

    /**
     * @Route(
     *      "/book/{work}/{slug}",
     *      name="mobile_work",
     *      condition="context.getMethod() in ['GET'] and request.headers.get('dev-only') and request.headers.get('CloudFront-Is-Mobile-Viewer')"
     * )
     * @Template()
     */
    public function mobile_work(
        Work $work
    ): array
    {


        return ['props' => []];
    }




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

        // automatically correct slug if wrong via 301
        $front_edition = $work->getFrontEdition();
        $correct_slug = $front_edition->updateSlug();

        $url = $this->generateUrl('work', array(
            'work_id'   => $work_id,
            'slug'      => $correct_slug));

        if ($slug != $correct_slug){
            $this->redirect($url, 301);
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
    }

    /**
     * @Route("/book/reviews/{work_id}/{sort}/{page}", requirements={"work_id" = "^\d+$", "sort" = "^new|old|liked$", "page" = "^\d+$"}, name="work_reviews", methods={"GET"})
     * @Route("/book/reviews/{work_id}/{sort}/{page}/{stars}", requirements={"work_id" = "^\d+$", "sort" = "^new|old|liked$", "page" = "^\d+$", "stars" = "^\d+|any$"}, name="work_reviews_bystar", methods={"GET"})
     * @Route("/book/reviews/{work_id}/{sort}/{page}/{stars}/{user_id}", requirements={"work_id" = "^\d+$", "sort" = "^new|old|liked$", "page" = "^\d+$", "stars" = "^\d+|any$", "user_id" = "^\d+$"}, name="work_reviews_byuser", methods={"GET"})
     * @Template()
     *
     *
     */
    public function reviews(
        ReviewManager $reviewManager,
        $work_id,
        $sort,
        $page,
        $stars = false,
        $user_id = false
    ){
        if ('any' == $stars){
            $stars = false;
        }
        if ($page < 1){
            $page = 1;
        }
        return $reviewManager->getReviews($work_id, $sort, $page, $stars, $user_id);
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
