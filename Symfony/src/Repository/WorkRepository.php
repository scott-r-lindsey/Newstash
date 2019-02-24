<?php

namespace App\Repository;

use App\Entity\Work;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Work|null find($id, $lockMode = null, $lockVersion = null)
 * @method Work|null findOneBy(array $criteria, array $orderBy = null)
 * @method Work[]    findAll()
 * @method Work[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WorkRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Work::class);
    }

    public function getWork(int $work_id)
    {
        //$this->getFormats();

        $em = $this->getEntityManager();

        $dql = '
            SELECT w, r, s
            FROM App\Entity\Work w
            LEFT JOIN w.reviews r
            LEFT JOIN w.score s
            WHERE
                w.id = :id AND
                w.deleted = 0
        ';

        $query = $em->createQuery($dql);
        $query->setParameter('id', $work_id);

        return $query->getOneOrNullResult();
    }

    public function getSimilarWorks(int $work_id)
    {
        $em = $this->getEntityManager();

        $dql = '
            SELECT sw, w, e, r
            FROM App\Entity\SimilarWork sw
            LEFT JOIN sw.similar w
            LEFT JOIN w.front_edition e
            LEFT JOIN w.score r
            WHERE
                sw.work = :work
            ORDER BY e.title
        ';

        $query = $em->createQuery($dql);
        $query->setParameter('work', $work_id);
        $similar_works = $query->getResult();

        // remove dupes
        $seen   = [$work_id => 1];
        $ar     = [];
        $i      = 0;

        foreach ($similar_works as $sw) {

            if (isset($seen[$sw->getSimilar()->getId()])){
                continue;
            }

            $seen[$sw->getSimilar()->getId()] = 1;
            $ar[] = $sw;

            $i++;
            if ($i > 11){
                break;
            }

        }
        return $ar;
    }

    public function getBrowseNodes(Work $work)
    {
        $em = $this->getEntityManager();
        $dbh = $em->getConnection();

        $ids = [];
        $qs = '';

        foreach ($work->getEditions() as $ed){
            $asins[] = $ed->getAsin();
            if ($qs){
                $qs .= ',';
            }
            $qs .= '?';
        }

        $sql = "
            SELECT
                browsenode.id AS id,
                browsenode.name AS name,
                browsenode.description AS description,
                browsenode.pathdata AS pathdata,
                browsenode.slug AS slug,
                browsenode.root AS root
            FROM
                browsenode, primary_browsenode_edition
            WHERE
                browsenode.id = primary_browsenode_edition.browsenode_id AND
                primary_browsenode_edition.edition_asin IN ($qs)";

        $sth = $dbh->prepare($sql);
        $sth->execute($asins);

        $bnsort         = [];
        $bns_by_id      = [];

        while ($browsenode = $sth->fetch()){
            if (1000 == $browsenode['id']){
                continue;
            }
            $bns_by_id[$browsenode['id']] = $browsenode;

            if (!isset($bnsort[$browsenode['id']])){
                $bnsort[$browsenode['id']] = array(
                    'id'        => $browsenode['id'],
                    'count'     => 0,
                );
            }
            $bnsort[$browsenode['id']]['count']++;
        }
        $bnsort = array_values($bnsort);

        usort ($bnsort, function($a, $b){
            if ($a['count'] == $b['count']) {
                return 0;
            }
            return ($a['count'] > $b['count']) ? -1 : 1;
        });

        $bns = array();
        foreach ($bnsort as $bn){
            $bns_by_id[$bn['id']]['pathdata'] = unserialize($bns_by_id[$bn['id']]['pathdata']);
            $bns[] = $bns_by_id[$bn['id']];
        }

        return $bns;
    }

    public function getActiveEditions($work_id)
    {
        $em = $this->getEntityManager();

        $dql = '
            SELECT e
            FROM App\Entity\Edition e
            LEFT JOIN e.work w
            WHERE
                e.active = 1 AND
                w.id = :id
        ';

        $query = $em->createQuery($dql);
        $query->setParameter('id', $work_id);

        return $query->getResult();
    }

    public function findUserStatusCountAndWorks(
        UserInterface $user,
        string $type,
        int $page,
        int $perpage,
        string $sort,
        bool $reverse
    ): array
    {
        // --------------------------------------------------------------------
        // this exists to efficiently service the user tabs

        $em             = $this->getEntityManager();

        $params         = ['user'  => $user];
        $added          = '';

        $statuses = [
            'toread'        => 1,
            'reading'       => 2,
            'readit'        => 3
        ];

        // find count of books being read
        if (in_array($type, array_keys($statuses))){
            $dql = '
                FROM App\Entity\Work w
                JOIN w.readit r
                JOIN w.front_edition e
                WHERE
                    r.user = :user AND
                    w.deleted = 0 AND
                    r.status = :status';

            $params['status'] = $statuses[$type];
            $added = 'r.created_at';
        }
        // find count of books reviewed
        else if ('reviews' == $type){
            $dql = '
                FROM App\Entity\Work w
                JOIN w.reviews r
                JOIN w.front_edition e
                WHERE
                    r.user = :user AND
                    w.deleted = 0 AND
                    r.deleted = 0';
            $added = 'r.created_at';
        }
        // find count of books rated
        else if ('ratings' == $type){
            $dql = '
                FROM App\Entity\Work w
                JOIN w.ratings r
                JOIN w.front_edition e
                WHERE
                    r.user = :user AND
                    w.deleted = 0';
            $added = 'r.created_at';
        }
        else{
            throw new \Exception('This list does not exist');
        }

        // --------------------------------------------------------------------
        // get count

        $query = $em->createQuery('SELECT count(w.id) ' . $dql);
        foreach ($params as $k => $v){
            $query->setParameter($k, $v);
        };

        $total = $query->getSingleScalarResult();

        // --------------------------------------------------------------------
        // get works associated with above count, paginated

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

        return [$total, $works];
    }
}
