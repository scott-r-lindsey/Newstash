<?php

namespace App\Repository;

use App\Entity\Work;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

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

}
