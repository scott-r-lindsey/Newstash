<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\ScoreManager;
use App\Service\RatingManager;
use App\Service\ReaditManager;
use App\Entity\Work;
use App\Repository\CommentRepository;
use App\Repository\RatingRepository;
use App\Repository\ReaditRepository;
use App\Repository\ReviewRepository;
use App\Repository\WorkRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class StashController extends AbstractController
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
        UserInterface $user = null
    ) {

        if (!$user) {
            return new JsonResponse('');
        }

        $ret = [];

        $ret['ratings']      = [];
        $ret['reviews']      = [];
        $ret['comments']     = [];
        $ret['readit']       = [];
        $ret['prefs']        = $user->getDisplayPrefs();

        foreach (explode(',', $lists) as $list){
            if ('readit' == $list){
                foreach ( $readitRepository->findArrayByUser($user) as $r) {
                    $ret['readit'][$r['work_id']] = $r['status'];
                }
            }
            else if ('ratings' == $list){
                foreach ( $ratingRepository->findArrayByUser($user) as $r) {
                    $ret['ratings'][$r['work_id']] = $r['stars'];
                }
            }
            else if ('reviews' == $list){
                foreach ($reviewRepository->findArrayByUser($user) as $r) {
                    $ret['reviews'][$r['work_id']] = 1;
                }
            }
            else if ('comments' == $list){
                $ret['comments'] = $commentRepository->findCommentCountByUser($user);
            }
            else{
                $list = intval($list);
            }
        }

        $response = new JsonResponse();
        $response->setData(array(
            'error'     => 0,
            'result'    => 'Data',
            'id'        => $user->getId(),
            'lists'     => $ret,
            'prefs'     => $user->getDisplayPrefs()
        ));
        return $response;
    }

    /**
     * @Route("/user/displayprefs", name="user_displayprefs_save", methods={"POST"}))
     *
     * Save user display prefs
     */
    public function putUserDisplayPrefs(
        EntityManagerInterface $em,
        Request $request,
        UserInterface $user = null
    ): JsonResponse
    {
        $response = new JsonResponse('');

        if (!$user) {
            return $response;
        }

        $hide           = $request->request->get('hide');

        $prefs          = $user->getDisplayPrefs();
        $prefs['hide']  = explode(',', $hide);

        $user->setDisplayPrefs($prefs);
        $em->flush();

        $response->setData(array(
            'error'     => 0,
            'result'    => 'Data',
            'id'        => $user->getId(),
            'lists'     => [],
            'prefs'     => $user->getDisplayPrefs()
        ));

        return $response;
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
        UserInterface $user = null
    ): JsonResponse
    {

        if (!$user) {
            throw $this->createAccessDeniedException('You are not logged in!');
        }

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
     * @Route("/user/rating/{work_id}", requirements={"work_id" = "^\d+$"}, name="user_rating_save"), methods={"POST"}))
     *
     * save user's rating for book and returns ratings summary html
     */
    public function putRatingAction(
        RatingManager $ratingManager,
        Request $request,
        Work $work,
        UserInterface $user = null
    ): JsonResponse
    {


/*
        list($fail, $response, $em, $user, $work) = $this->setup($work_id);
        if ($fail){
            return $response;
        }

        $scoreMaker     = $this->get('bookster_score_maker');
        $stars          = $request->request->get('stars');

        $dql = '
            DELETE FROM
                Scott\DataBundle\Entity\Rating r
            WHERE
                r.work = :work AND
                r.user = :user';

        $query = $em->createQuery($dql);
        $query->setParameter('work', $work_id);
        $query->setParameter('user', $user);
        $query->execute();

        $stars = intval($stars);
        if (($stars > 0) and ($stars < 6)){
            $rating = new Rating();
            $rating->setWork($work)
                ->setUser($user)
                ->setStars($stars);
            $this->setWeb($request, $rating);

            $em->persist($rating);
            $em->flush();
        }
        else{
            // delete reviews?  let's try laissez faire for a while
        }
        $scoreMaker->score($work);
        $ratings = $this->getRatings();

        // --------------------------------------------------------------------
        // build ratings html -- copied from Work:ratings

        $dbh = $em->getConnection();

        $sql = '
            SELECT
                count(*) as count
            FROM
                review
            WHERE
                work_id = ?
        ';

        $sth = $dbh->prepare($sql);
        $sth->execute(array($work_id));
        $review_count = $sth->fetch();
        $review_count = $review_count['count'];

        $dql = '
            SELECT s
            FROM Scott\DataBundle\Entity\Score s
            WHERE
                s.work = :id
        ';

        $query = $em->createQuery($dql);
        $query->setParameter('id', $work_id);
        $score = $query->getOneOrNullResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);

        $root = dirname($this->get('kernel')->getRootDir());
        $rhtml = $this->get('twig')->render(
            $root . '/src/Scott/DataBundle/Resources/views/Work/ratings.html.twig',
            compact('score', 'review_count', 'work_id'));

        // --------------------------------------------------------------------
        // update review stars

        if (0 == $stars){
            $stars = null;
        }

        $sql = '
            UPDATE
                review
            SET
                stars = ?
            WHERE
                work_id = ? AND
                user_id = ?';

        $sth = $dbh->prepare($sql);
        $sth->execute(array($stars, $work_id, $user->getId()));

        // --------------------------------------------------------------------
        // save news

        $newsMaster = $this->container->get('bookster_news_master');
        $newsMaster->newRating($work, $stars, $user);

        // --------------------------------------------------------------------
        // should return all ratings plus ratings html

        $response->setData(array(
            'error'     => 0,
            'result'    => 'Data',
            'lists'     => array('ratings'  => $ratings),
            'html'      => $rhtml,
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

}
