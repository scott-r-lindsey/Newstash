<?php
declare(strict_types=1);

namespace App\Controller;

use App\Repository\RatingRepository;
use App\Repository\WorkRepository;
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
    public function getListsAction(
        RatingRepository $ratingRepository,
        WorkRepository $workRepository,
        Request $request,
        $lists,
        UserInterface $user = null
    ) {

        if (!$user) {
            return new JsonResponse('');
        }

        $return = [];

        $return['ratings']      = [];
        $return['reviews']      = [];
        $return['comments']     = [];
        $return['readit']       = [];

        foreach (explode(',', $lists) as $list){
            if ('readit' == $list){
            }
            else if ('ratings' == $list){
                $ratings = $ratingRepository->findArrayByUser($user);

                foreach ($ratings as $r){
                    $return['ratings'][$r['work_id']] = $r['stars'];
                }
            }
            else if ('reviews' == $list){
            }
            else if ('comments' == $list){
            }
            else{
                $list = intval($list);
                // FIXME
                // stash fetching not implemented
            }
        }





        $response = new JsonResponse('');
        return $response;



/*

        list($fail, $response, $em, $user, $work) = $this->setup();
        if ($fail){
            return $response;
        }

        $ret = array();

        foreach (explode(',', $lists) as $list){
            if ('readit' == $list){
                $query = $em->getRepository('ScottDataBundle:Readit')
                    ->createQueryBuilder('r')
                    ->where('r.user = :user')
                    ->setParameter('user', $user)
                    ->getQuery()
                    ->setHint(\Doctrine\ORM\Query::HINT_INCLUDE_META_COLUMNS, true);
                $readit = $query->getArrayResult();

                $ar = array();
                foreach ($readit as $r){
                    $ar[$r['work_id']] = $r['status'];
                }
                $ret['readit'] = $ar;

            }
            else if ('ratings' == $list){
                $ret['ratings'] = $this->getRatings();
            }
            else if ('reviews' == $list){
                $ret['reviews'] = $this->getReviews($user);
            }
            else if ('comments' == $list){
                $ret['comments'] = $this->getComments($user);
            }
            else{
                $list = intval($list);
                // FIXME
                // stash fetching not implemented
            }
        }
        $prefs = $user->getDisplayPrefs();

        $response->setData(array(
            'error'     => 0,
            'result'    => 'Data',
            'id'        => $user->getId(),
            'lists'     => $ret,
            'prefs'     => $prefs
        ));
        return $response;

        */

    }




    /**
     * @Route("/user/displayprefs", name="user_displayprefs_save", methods={"POST"}))
     *
     * Save user display prefs
     */
    public function putUserDisplayPrefs(Request $request)
    {

        // FIXME

/*

        list($fail, $response, $em, $user, $work) = $this->setup();
        if ($fail){
            return $response;
        }

        $hide    = ($request->request->get('hide'));
        $prefs = $user->getDisplayPrefs();
        $prefs['hide'] = explode(',', $hide);
        if ('' == $prefs['hide'][0]){
            $prefs['hide'] = array();
        }

        $user->setDisplayPrefs($prefs);
        $em->flush();

        $prefs = $user->getDisplayPrefs();

        $response->setData(array(
            'error'     => 0,
            'result'    => 'Data',
            'id'        => $user->getId(),
            'lists'     => [],
            'prefs'     => $prefs
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
