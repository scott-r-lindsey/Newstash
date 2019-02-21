<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Readit;
use App\Entity\Work;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use App\Repository\RatingRepository;
use Symfony\Component\Security\Core\User\UserInterface;

class RatingManager
{
    private $logger;
    private $em;
    private $repo;

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $em,
        RatingRepository $repo
    )
    {
        $this->logger               = $logger;
        $this->em                   = $em;
        $this->repo                 = $repo;
    }

    public function setUserWorkRating(
        UserInterface $user,
        Work $work,
        int $stars,
        string $ipaddr,
        string $useragent
    ): Readit
    {

        $rating = $this->repo->findOneBy([
            'work'  => $work,
            'user'  => $user
        ]);

        if (null === $rating) {

            $rating = new Rating();
            $rating
                ->setWork($work)
                ->setUser($user)
            ;

            $this->em->persist($rating);
        }




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

}
