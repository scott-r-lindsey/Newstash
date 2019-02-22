<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\BaseApiController;
use App\Entity\Work;
use App\Repository\CommentRepository;
use App\Repository\RatingRepository;
use App\Repository\ReaditRepository;
use App\Repository\ReviewRepository;
use App\Repository\WorkRepository;
use App\Service\Mongo\News;
use App\Service\RatingManager;
use App\Service\ReaditManager;
use App\Service\ScoreManager;
use Doctrine\ORM\EntityManagerInterface;
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
        News $news,
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

        $news->newRating($rating);

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
    public function getReviewAction(
        ReviewRepository $reviewRepository,
        Work $work,
        UserInterface $user
    ): JsonResponse
    {

        $review = $reviewRepository->findOneBy([
            'work'  => $work,
            'user'  => $user
        ]);

        return $this->legacyResponse([
            'exists'        => $review ? true : false,
            'error'         => 0,
            'result'        => 'Data',
            'lists'         => [],
            'review'        => $review ? $review->getText() : '',
            'title'         => $review ? $review->getTitle() : '',
            'started_at'    => $review ? $review->getStartedAt()->format('M d, Y') : '',
            'finished_at'   => $review ? $review->getFinishedAt()->format('M d, Y') : ''
        ]);
    }

    /**
     * @Route("/user/review/delete/{work}", requirements={"work" = "^\d+$"}, name="user_delete_review"), methods={"POST"})
     *
     * Deletes a user's review for a book, if it exists
     */
    public function deleteReviewAction(
        Request $request,
        Work $work
    ) {

/*
        list($fail, $response, $em, $user, $work) = $this->setup($work_id);
        if ($fail){
            return $response;
        }

        $dql = '
            SELECT r
            FROM Scott\DataBundle\Entity\Review r
            WHERE
                r.work = :work_id AND
                r.user = :user
        ';

        $query = $em->createQuery($dql);
        $query->setParameter('work_id', $work_id)
            ->setParameter('user', $user);
        $review = $query->getOneOrNullResult();

        if ($review){
            $em->remove($review);
            $em->flush();
        }

        // --------------------------------------------------------------------
        $newsMaster = $this->container->get('bookster_news_master');
        $newsMaster->removeReview($user, $work);

        // --------------------------------------------------------------------

        $sql = '
            SELECT
                count(*) as count
            FROM
                review
            WHERE
                deleted = 0 AND
                work_id = ?';

        $dbh = $em->getConnection();
        $sth = $dbh->prepare($sql);
        $sth->execute(array($work_id));

        $result = $sth->fetch();
        $count = $result['count'];

        // --------------------------------------------------------------------
        $reviews = $this->getReviews($user);

        $response->setData(array(
            'error'             => 0,
            'result'            => 'Data',
            'lists'             => compact('reviews'),
            'work_review_count' => $count
        ));

        return $response;
*/
    }


    /**
     * @Route("/user/review/{work}", requirements={"work" = "^\d+$"}, methods={"POST"})
     *
     * Saves a user's review for a book
     */
    public function putReviewAction(
        Request $request,
        Work $work
    ) {


/*

        list($fail, $response, $em, $user, $work) = $this->setup($work_id);
        if ($fail){
            return $response;
        }

        // enforce 20/20000 limits
        $text           = $request->request->get('text');
        $title          = $request->request->get('title');
        $started        = $request->request->get('started');
        $finished       = $request->request->get('finished');

        if (20 > strlen($text)){
            $response->setData(array(
                'error'     => 5,
                'result'    => 'Text is too short.',
                'lists'     => []
            ));
            return $response;
        }
        else if (20000 < strlen($text)){
            $response->setData(array(
                'error'     => 5,
                'result'    => 'Text is too long.',
                'lists'     => []
            ));
            return $response;
        }

        $startedDate = $finishedDate = null;

        if ($started = strtotime($started)){
            $startedDate = new \DateTime('@' . $started);
        }
        if ($finished = strtotime($finished)){
            $finishedDate = new \DateTime('@' . $finished);
        }

        // --------------------------------------------------------------------
        $config = \HTMLPurifier_Config::createDefault();

        $config->set('Cache.SerializerPath',
            dirname(dirname($this->get('kernel')->getRootDir())) .'/htmlPureDefCache/');

        $config->set('HTML.AllowedElements',
            array('b', 'i', 'strike', 'u', 'p'));

        $purifier = new \HTMLPurifier($config);
        $text = $purifier->purify($text);

        // --------------------------------------------------------------------
        $dql = '
            SELECT r
            FROM Scott\DataBundle\Entity\Rating r
            WHERE
                r.work = :work_id AND
                r.user = :user
        ';

        $query = $em->createQuery($dql);
        $query->setParameter('work_id', $work_id)
            ->setParameter('user', $user);
        $rating = $query->getOneOrNullResult();

        $stars = false;
        if ($rating){
            $stars = $rating->getStars();
        }


        // --------------------------------------------------------------------

        if (20000 < strlen($text)){
            $response->setData(array(
                'error'     => 10,
                'result'    => 'Text is too long.'
            ));
            return $response;
        }

        $dql = '
            SELECT r
            FROM Scott\DataBundle\Entity\Review r
            WHERE
                r.work = :work_id AND
                r.user = :user
        ';

        $query = $em->createQuery($dql);
        $query->setParameter('work_id', $work_id)
            ->setParameter('user', $user);
        $review = $query->getOneOrNullResult();

        if (!$review){
            $review = new Review();

            if ($stars){
                $review->setStars($stars);
            }
            $review->setUser($user)
                ->setWork($work)
                ->setTitle($title)
                ->setText($text)
                ->setStartedReadingAt($startedDate)
                ->setFinishedReadingAt($finishedDate);
            $this->setWeb($request, $review);
            $em->persist($review);
            $em->flush();

            $newsMaster = $this->container->get('bookster_news_master');
            $newsMaster->newReview($work, $review, $user, $stars);
        }
        else{
            if ($stars){
                $review->setStars($stars);
            }
            $review->setTitle($title)
                ->setStartedReadingAt($startedDate)
                ->setFinishedReadingAt($finishedDate)
                ->setText($text);
            $this->setWeb($request, $review);
            $em->flush();
        }

        // --------------------------------------------------------------------

        $sql = '
            SELECT
                count(*) as count
            FROM
                review
            WHERE
                deleted = 0 AND
                work_id = ?';

        $dbh = $em->getConnection();
        $sth = $dbh->prepare($sql);
        $sth->execute(array($work_id));

        $result = $sth->fetch();
        $count = $result['count'];

        // --------------------------------------------------------------------

        $reviews = $this->getReviews($user);

        $response->setData(array(
            'error'             => 0,
            'result'            => 'Data',
            'lists'             => compact('reviews'),
            'review'            => $text,
            'work_review_count' => $count
        ));
        return $response;
*/
    }








    /**
     * @Route("/user/reviewflag", name="stash_user_review_flag", methods={"POST"})
     *
     * Creates a flag on a given review
     */
    public function putReviewFlagAction(Request $request) {

        // FIXME

/*
        $message        = $request->request->get('message');
        $review_id      = $request->request->get('review_id');
        $reason_id      = $request->request->get('reason_id');

        list($fail, $response, $em, $user, $work) = $this->setup(false);
        if ($fail){
            return $response;
        }

        if (0 == $reason_id){
            $reason = false;
        }
        else{
            $reason = $em->getRepository('ScottDataBundle:Reason')->findOneById($reason_id);

            if (!$reason){
                $response->setData(array(
                    'error'     => 1,
                    'result'    => 'The reason does not exist'
                ));
                return $response;
            }
        }

        $review = $em->getRepository('ScottDataBundle:Review')
            ->findOneById($review_id);

        if (!$review){
            $response->setData(array(
                'error'     => 2,
                'result'    => 'Review not found.'
            ));
            return $response;
        }
        if ($user->getId() == $review->getUser()->getId()){
            $response->setData(array(
                'error'     => 3,
                'result'    => 'Can not flag your own review.'
            ));
            return $response;
        }


        $flag = new Flag();
        $this->setWeb($request, $flag);
        $flag->setReview($review)
            ->setMessage($message);

        if ($reason){
            $flag->setReason($reason);
        }

        $em->persist($flag);
        $em->flush();

        $response->setData(array(
            'error'     => 0,
            'result'    => 'Flag accepted'
        ));
        return $response;
*/

    }

    /**
     * @Route("/user/review/delete/{work_id}", requirements={"work_id" = "^\d+$"}, name="user_delete_review", methods={"POST"})
     *
     * Deletes a user's review for a book, if it exists
     */
    public function deleteReviewAction(Request $request, $work_id)
    {

        // FIXME

        /*

        list($fail, $response, $em, $user, $work) = $this->setup($work_id);
        if ($fail){
            return $response;
        }

        $dql = '
            SELECT r
            FROM Scott\DataBundle\Entity\Review r
            WHERE
                r.work = :work_id AND
                r.user = :user
        ';

        $query = $em->createQuery($dql);
        $query->setParameter('work_id', $work_id)
            ->setParameter('user', $user);
        $review = $query->getOneOrNullResult();

        if ($review){
            $em->remove($review);
            $em->flush();
        }

        // --------------------------------------------------------------------
        $newsMaster = $this->container->get('bookster_news_master');
        $newsMaster->removeReview($user, $work);

        // --------------------------------------------------------------------

        $sql = '
            SELECT
                count(*) as count
            FROM
                review
            WHERE
                deleted = 0 AND
                work_id = ?';

        $dbh = $em->getConnection();
        $sth = $dbh->prepare($sql);
        $sth->execute(array($work_id));

        $result = $sth->fetch();
        $count = $result['count'];

        // --------------------------------------------------------------------
        $reviews = $this->getReviews($user);

        $response->setData(array(
            'error'             => 0,
            'result'            => 'Data',
            'lists'             => compact('reviews'),
            'work_review_count' => $count
        ));

        return $response;

        */
    }

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
