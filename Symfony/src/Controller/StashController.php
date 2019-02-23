<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\BaseApiController;
use App\Entity\Flag;
use App\Entity\Review;
use App\Entity\Work;
use App\Repository\CommentRepository;
use App\Repository\RatingRepository;
use App\Repository\ReaditRepository;
use App\Repository\ReasonRepository;
use App\Repository\ReviewLikeRepository;
use App\Repository\ReviewRepository;
use App\Repository\WorkRepository;
use App\Service\FlagManager;
use App\Service\Mongo\News;
use App\Service\RatingManager;
use App\Service\ReaditManager;
use App\Service\ReviewLikeManager;
use App\Service\ReviewManager;
use App\Service\ScoreManager;
use Doctrine\ORM\EntityManagerInterface;
use HTMLPurifier;
use HTMLPurifier_Config;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class StashController extends BaseApiController
{

    /**
     * @Route("/user/get/{lists}", methods={"GET"})
     *
     * Returns various lists, used for initial population of UI
     */
    public function getLists(
        CommentRepository $commentRepository,
        RatingRepository $ratingRepository,
        ReaditRepository $readitRepository,
        ReviewRepository $reviewRepository,
        WorkRepository $workRepository,
        Request $request,
        $lists,
        UserInterface $user
    ) {

        $ret = [];

        $ret['prefs']        = $user->getDisplayPrefs();

        foreach (explode(',', $lists) as $list){
            if ('readit' == $list){
                $ret['readit'] = $this->getReadit($readitRepository, $user);
            }
            else if ('ratings' == $list){
                $ret['ratings'] = $this->getRatings($ratingRepository, $user);
            }
            else if ('reviews' == $list){
                $ret['reviews'] = $this->getReviews($reviewRepository, $user);
            }
            else if ('comments' == $list){
                $ret['comments'] = $commentRepository->findCommentCountByUser($user);
            }
            else{
                $list = intval($list);
            }
        }

        return $this->legacyResponse([
            'error'     => 0,
            'result'    => 'Data',
            'id'        => $user->getId(),
            'lists'     => $ret,
            'prefs'     => $user->getDisplayPrefs()
        ]);
    }

    /**
     * @Route("/user/displayprefs", name="user_displayprefs_save", methods={"POST"}))
     *
     * Save user display prefs
     */
    public function putUserDisplayPrefs(
        EntityManagerInterface $em,
        Request $request,
        UserInterface $user
    ): JsonResponse
    {
        $hide           = $request->request->get('hide');

        $prefs          = $user->getDisplayPrefs();
        $prefs['hide']  = explode(',', $hide);

        $user->setDisplayPrefs($prefs);
        $em->flush();

        return $this->legacyResponse([
            'error'     => 0,
            'result'    => 'Data',
            'id'        => $user->getId(),
            'lists'     => $ret,
            'prefs'     => $user->getDisplayPrefs()
        ]);
    }

    /**
     * @Route("/user/readit/{work}", requirements={"work" = "^\d+$"}, name="user_readit_save"), methods={"POST"}))
     *
     * Save read-it/reading/to-read state
     */
    public function putReadit(
        ReaditManager $readitManager,
        Request $request,
        Work $work,
        UserInterface $user
    ): JsonResponse
    {
        $readitManager->setUserWorkReadit(
            $user,
            $work,
            intval($request->request->get('status')),
            $request->server->get('REMOTE_ADDR'),
            $request->server->get('HTTP_USER_AGENT')
        );

        return $this->forward('App\Controller\StashController::getLists', [
            'lists' => 'readit,rated'
        ]);
    }

    /**
     * @Route("/user/rating/{work}", requirements={"work" = "^\d+$"}, name="user_rating_save"), methods={"POST"}))
     *
     * save user's rating for book and returns ratings summary html
     */
    public function putRating(
        ReviewRepository $reviewRepository,
        RatingRepository $ratingRepository,
        ScoreManager $scoreManager,
        RatingManager $ratingManager,
        Request $request,
        Work $work,
        UserInterface $user
    ): JsonResponse
    {
        $stars          = intval($request->request->get('stars'));
        $work_id        = $work->getId();

        if (($stars < 0) or ($stars > 5)){
            throw new BadRequestHttpException('Stars should be from zero to five');
        }

        list ($rating, $score, $rating_count) = $ratingManager->setUserWorkRating(
            $user,
            $work,
            intval($request->request->get('stars')),
            $request->server->get('REMOTE_ADDR'),
            $request->server->get('HTTP_USER_AGENT')
        );

        $review_count       = $reviewRepository->count([
            'work'      => $work_id,
            'deleted'   => 0
        ]);

        $rhtml = $this->renderView(
            'work/ratings.html.twig',
            compact('score', 'review_count', 'work_id')
        );

        $ratings = $this->getRatings($ratingRepository, $user);

        return $this->legacyResponse([
            'error'     => 0,
            'result'    => 'Data',
            'lists'     => ['ratings'  => $ratings],
            'html'      => $rhtml,
        ]);
    }


    /**
     * @Route("/user/review/{work}", requirements={"work" = "^\d+$"}, methods={"GET"}))
     *
     * Returns user's review for a book, if it exists
     */
    public function getReview(
        ReviewRepository $reviewRepository,
        Work $work,
        UserInterface $user
    ): JsonResponse
    {

        $review = $reviewRepository->findOneBy([
            'work'  => $work,
            'user'  => $user
        ]);


        $started    = $review->getStartedReadingAt();
        $finished   = $review->getFinishedReadingAt();

        return $this->legacyResponse([
            'exists'        => $review ? true : false,
            'error'         => 0,
            'result'        => 'Data',
            'lists'         => [],
            'review'        => $review ? $review->getText() : '',
            'title'         => $review ? $review->getTitle() : '',
            'started_at'    => $started ? $started->format('M d, Y') : '',
            'finished_at'   => $finished ? $finished->format('M d, Y') : ''
        ]);
    }

    /**
     * @Route("/user/review/delete/{work}", requirements={"work" = "^\d+$"}, name="user_delete_review"), methods={"POST"})
     *
     * Deletes a user's review for a book, if it exists
     */
    public function deleteReview(
        ReviewManager $reviewManager,
        ReviewRepository $reviewRepository,
        Request $request,
        Work $work,
        UserInterface $user
    ): JsonResponse
    {

        $reviewManager->deleteUserWorkReview($user, $work);

        $reviews = $this->getReviews($reviewRepository, $user);

        return $this->legacyResponse([
            'error'             => 0,
            'result'            => 'Data',
            'lists'             => compact('reviews'),
            'work_review_count' => count($reviews)
        ]);
    }

    /**
     * @Route("/user/review/{work}", requirements={"work" = "^\d+$"}, methods={"POST"})
     *
     * Saves a user's review for a book
     */
    public function putReview(
        ReviewManager $reviewManager,
        ReviewRepository $reviewRepository,
        Request $request,
        Work $work,
        UserInterface $user,
        string $projectDir
    ): JsonResponse
    {

        // enforce 20/20000 limits
        $title          = $request->request->get('title');
        $text           = $request->request->get('text');
        $started        = $request->request->get('started');
        $finished       = $request->request->get('finished');

        $startedDate = $finishedDate = null;

        if ($started = strtotime($started)){
            $startedDate = new \DateTime('@' . $started);
        }
        if ($finished = strtotime($finished)){
            $finishedDate = new \DateTime('@' . $finished);
        }

        if (20 > strlen($text)) {
            return $this->legacyResponse([
                'error'     => 5,
                'result'    => 'Text is too short.',
                'lists'     => []
            ]);
            return $response;
        }
        else if (20000 < strlen($text)) {
            return $this->legacyResponse([
                'error'     => 5,
                'result'    => 'Text is too long.',
                'lists'     => []
            ]);
            return $response;
        }

        // --------------------------------------------------------------------

        $config = HTMLPurifier_Config::createDefault();

        $config->set('Cache.SerializerPath', "$projectDir/var/htmlPureDefCache");
        $config->set('HTML.AllowedElements', ['b', 'i', 'strike', 'u', 'p']);

        $purifier = new \HTMLPurifier($config);
        $text = $purifier->purify($text);

        // --------------------------------------------------------------------

        $review = $reviewManager->setUserWorkReview(
            $user,
            $work,
            $title,
            $text,
            $request->server->get('REMOTE_ADDR'),
            $request->server->get('HTTP_USER_AGENT'),
            $startedDate,
            $finishedDate
        );

        $reviews = $this->getReviews($reviewRepository, $user);

        return $this->legacyResponse([
            'error'             => 0,
            'result'            => 'Data',
            'lists'             => compact('reviews'),
            'review'            => $text,
            'work_review_count' => count($reviews),
        ]);
    }

    /**
     * @Route("/user/reviewlike/{work}/{review}", requirements={"work" = "^\d+$", "review" = "^\d+$"}, name="stash_user_review_like", methods={"POST"})
     *
     * Toggles user's like for a given review
     */
    public function putReviewLikeAction(
        Request $request,
        ReviewLikeRepository $reviewLikeRepository,
        ReviewLikeManager $reviewLikeManager,
        Work $work,
        Review $review,
        UserInterface $user
    ) {

        // This is a toggle.  That seems like a strange choice in retrospect.

        if ($user->getId() == $review->getUser()->getId()){
            return $this->legacyResponse([
                'error'     => 3,
                'result'    => 'Can not like your own review.'
            ]);
        }

        $reviewLike = $reviewLikeRepository->findOneBy([
            'review'    => $review,
            'user'      => $user
        ]);


        if ($reviewLike) {
            $reviewLikeManager->deleteUserReviewLike(
                $user,
                $review
            );
        }
        else{
            $reviewLikeManager->createUserReviewLike(
                $user,
                $review,
                $request->server->get('REMOTE_ADDR'),
                $request->server->get('HTTP_USER_AGENT')
            );
        }

        return $this->legacyResponse([
            'error'             => 0,
            'result'            => $reviewLike ? 'unliked' : 'liked',
            'likes'             => $review->getLikes(),
        ]);
    }

    /**
     * @Route("/user/reviewflag", name="stash_user_review_flag", methods={"POST"})
     *
     * Creates a flag on a given review
     */
    public function putReviewFlag(
        Request $request,
        FlagManager $flagManager,
        ReasonRepository $reasonRepository,
        ReviewRepository $reviewRepository,
        UserInterface $user
    ): JsonResponse
    {

        $message        = $request->request->get('message');
        $review_id      = $request->request->get('review_id');
        $reason_id      = $request->request->get('reason_id');

        $reason = null;

        if ($reason_id) {
            $reason = $reasonRepository->findOneById($reason_id);

            if (!$reason) {
                return $this->legacyResponse([
                    'error'     => 1,
                    'result'    => 'The reason does not exist'
                ]);
            }
        }

        $review         = $reviewRepository->findOneById($review_id);
        if (!$review){
            return $this->legacyResponse([
                'error'     => 2,
                'result'    => 'Review not found.'
            ]);
        }

        if ($user->getId() == $review->getUser()->getId()){
            return $this->legacyResponse([
                'error'     => 3,
                'result'    => 'Can not flag your own review.'
            ]);
        }

        $flag = $flagManager->createUserReviewFlag(
            $user,
            $review,
            $message,
            $request->server->get('HTTP_USER_AGENT'),
            $request->server->get('REMOTE_ADDR'),
            $reason
        );

        return $this->legacyResponse([
            'error'     => 0,
            'result'    => 'Flag accepted'
        ]);
    }

    /**
     * @Route("/user/tabcontent/{type}", name="user_tabcontent", methods={"GET"});
     * @Template()
     *
     * User's tab bar
     */
    public function getTabContentAction(
        Request $request,
        string $type
    ): array
    {


/*
        list($fail, $response, $em, $user, $work) = $this->setup();
        if ($fail){
            return $response;
        }

        $page       = $request->query->get('page', 1);
        $sort       = $request->query->get('sort', 'added');
        $reverse    = $request->query->get('reverse', false);
        $reverse    = $reverse ? 1 : 0;
        $perpage    = 20;

        // toread/reading/readit reviewed rated
        $statuses = array(
            'toread'        => 1,
            'reading'       => 2,
            'readit'        => 3 );

        $descriptions = array(
            'toread'        => 'To Read',
            'reading'       => 'Reading',
            'readit'        => 'Read It',
            'reviews'       => 'Reviewed',
            'ratings'       => 'Rated');

        $description = $descriptions[$type];

        $params = array( 'user'  => $user);
        $added = '';

        if (in_array($type, array_keys($statuses))){
            $dql = '
                FROM Scott\DataBundle\Entity\Work w
                JOIN w.readit r
                JOIN w.front_edition e
                WHERE
                    r.user = :user AND
                    w.deleted = 0 AND
                    r.status = :status';

            $params['status'] = $statuses[$type];
            $added = 'r.created_at';
        }
        else if ('reviews' == $type){
            $dql = '
                FROM Scott\DataBundle\Entity\Work w
                JOIN w.reviews r
                JOIN w.front_edition e
                WHERE
                    r.user = :user AND
                    w.deleted = 0 AND
                    r.deleted = 0';
            $added = 'r.created_at';
        }
        else if ('ratings' == $type){
            $dql = '
                FROM Scott\DataBundle\Entity\Work w
                JOIN w.ratings r
                JOIN w.front_edition e
                WHERE
                    r.user = :user AND
                    w.deleted = 0';
            $added = 'r.created_at';
        }
        else{
            throw $this->createNotFoundException('This list does not exist');
        }


        // --------------------------------------------------------------------
        // get count

        $query = $em->createQuery('SELECT count(w.id) ' . $dql);
        foreach ($params as $k => $v){
            $query->setParameter($k, $v);
        };

        $total = $query->getSingleScalarResult();

        // --------------------------------------------------------------------
        // get works

        if ('alpha' == $sort){
            $ord = $reverse ? 'DESC' : 'ASC';
            $order_by = " ORDER BY w.title $ord";
        }
        else if ('bestseller' == $sort){
            $ord = $reverse ? 'DESC' : 'ASC';
            $order_by = "  ORDER BY e.amzn_salesrank $ord";
        }
        else if ('added' == $sort){
            $ord = $reverse ? 'ASC' : 'DESC';
            $order_by = "  ORDER BY $added $ord";
        }
        else if ('pubdate' == $sort){
            $ord = $reverse ? 'ASC' : 'DESC';
            $order_by = "  ORDER BY e.publication_date $ord";
        }

        $query = $em->createQuery('SELECT w, e ' . $dql . $order_by);

        // workaround for poor one-to-one query performance on Score
        $query->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);

        foreach ($params as $k => $v){
            $query->setParameter($k, $v);
        };

        $query->setMaxResults($perpage)
            ->setFirstResult($perpage * ($page-1));


        $works = $query->getResult();
*/

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

    // ------------------------------------------------------------------------

    private function getReadit(
        ReaditRepository $readitRepository,
        UserInterface $user
    ): array
    {
        $ret = [];
        foreach ( $readitRepository->findArrayByUser($user) as $r) {
            $ret[$r['work_id']] = $r['status'];
        }

        return $ret;
    }

    private function getRatings(
        RatingRepository $ratingRepository,
        UserInterface $user
    ): array
    {
        $ret = [];
        foreach ( $ratingRepository->findArrayByUser($user) as $r) {
            $ret[$r['work_id']] = $r['stars'];
        }

        return $ret;
    }

    private function getReviews(
        ReviewRepository $reviewRepository,
        UserInterface $user
    ): array
    {
        $ret = [];
        foreach ($reviewRepository->findArrayByUser($user) as $r) {
            $ret[$r['work_id']] = 1;
        }

        return $ret;
    }
}
