<?php
declare(strict_types=1);

namespace App\Service\Export;

use App\Lib\OutputingService;
use App\Service\Mongo;
use Doctrine\DBAL\Statement;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Work extends OutputingService
{
    private $logger;
    private $em;
    private $mongo;

    const STOP_WORDS = [ 'the', 'of', 'a', 'and', 'to', 'novel', 'in', 'for' ];

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $em,
        Mongo $mongo
    )
    {
        $this->logger               = $logger;
        $this->em                   = $em;
        $this->mongo                = $mongo;
    }

    public function exportWorks(
        string $workCollection = 'works_new',
        string $typeaheadAuthorCollection = 'typeahead_author_new',
        string $typeaheadWorkCollection = 'typeahead_work_new'
    ): void
    {
        $mongodb        = $this->mongo->getDb();
        $sth            = $this->fetchData();

        list($typeahead_work, $typeahead_author)
            = $this->parseAndInsertWorkData($sth, $workCollection);

        //----------------------------------------------------------------------
        $this->writeln( "<comment>inserting typeahead_work records...</comment>");
        foreach ($typeahead_work as $key => $record){
            $mongodb->typeahead_work_new->insert($record);
        }
        $typeahead_work = array(); // memory

        $this->writeln( "<comment>creating typeahead_work_new.text index</comment>");
        $mongodb->typeahead_work_new->createIndex( array('text' => 1), array('unique' => true));

        $this->writeln( "<comment>creating typeahead_work_new.sort_text index</comment>");
        $mongodb->typeahead_work_new->createIndex( array('sort_text' => 1), array('sparse' => true));

        //----------------------------------------------------------------------
        $this->writeln( "<comment>inserting typeahead_author records...</comment>");
        foreach ($typeahead_author as $key => $record){
            $mongodb->typeahead_author_new->insert($record);
        }
        $typeahead_author = array(); // memory

        $this->writeln( "<comment>creating typeahead_author_new.text index</comment>");
        $mongodb->typeahead_author_new->createIndex( array('text' => 1), array('unique' => true));

        $this->writeln( "<info>Work export complete</info>");
    }

    private function parseAndInsertWorkData(
        Statement $sth,
        string $targetCollection
    ): array
    {

        $i                  = 0;
        $chunk              = 100;
        $byEditionAsin      = [];
        $typeahead_work     = [];
        $typeahead_author   = [];

        $this->writeln('<comment>Exporting Works to Mongo...</comment>');
        while ($work = $sth->fetch()){

            $i++;

            $record = [
                'work_id'               => (int)$work['work_id'],
                'title'                 => $work['work_title'],
                'amzn_authordisplay'    => $work['edition_amzn_authordisplay'],
                'slug'                  => $work['edition_slug'],
                'front'     => [
                    'asin'  => $work['edition_asin'],
                    'isbn'  => $work['edition_isbn']
                ],
                'amzn_small_cover'      => $work['edition_amzn_small_cover'],
                'amzn_small_cover_x'    => $work['edition_amzn_small_cover_x'],
                'amzn_small_cover_y'    => $work['edition_amzn_small_cover_y'],
                'amzn_medium_cover'     => $work['edition_amzn_medium_cover'],
                'amzn_medium_cover_x'   => $work['edition_amzn_medium_cover_x'],
                'amzn_medium_cover_y'   => $work['edition_amzn_medium_cover_y'],
                'amzn_large_cover'      => $work['edition_amzn_large_cover'],
                'amzn_large_cover_x'    => $work['edition_amzn_large_cover_x'],
                'amzn_large_cover_y'    => $work['edition_amzn_large_cover_y'],
                'list_price'            => $work['edition_list_price'],
                'amzn_price'            => $work['edition_amzn_price'],
                'amzn_publisher'        => $work['edition_amzn_publisher'],
                'amzn_salesrank'        => (int)$work['edition_amzn_salesrank'],
                'amzn_format'           => $work['edition_amzn_format'],
                'pages'                 => $work['edition_pages'],
                'publication_date'      => $work['edition_publication_date'],
                'release_date'          => $work['edition_release_date'],
                'description'           => $work['edition_description'],
            ];

            if ($work['edition_amzn_authorlist']){
                $auths = unserialize($work['edition_amzn_authorlist']);

                $search_auths = [];

                foreach ($auths as $auth){
                    $search_auth = strtolower(preg_replace('/[^a-zA-Z0-9 ]/', '', trim($auth)));
                    if (($search_auth) and (strlen($search_auth) > 4)){
                        $search_auths[] = $search_auth;

                        $split = explode(' ', $search_auth);
                        $last = $split[count($split)-1];

                        $typeahead_author[$search_auth] = array(
                            'type'      => 'author',
                            'display'   => trim($auth),
                            'text'      => $search_auth,
                            'last'      => $last,
                        );
                    }
                }
                $record['amzn_authorlist'] = $search_auths;
            }
            else{
                $record['amzn_authorlist'] = [];
            }

            $record['browse_node'] = [];

            if (! $record['slug']){
                if ($work['edition_title']){
                    if ($work['edition_amzn_authordisplay']){
                        $record['slug']
                            = Edition::slugify($work['edition_title'], $work['edition_amzn_authordisplay']);
                    }
                    else{
                        $record['slug']
                            = Edition::slugify($work['edition_title']);
                    }
                }
            }

            // ----------------------------------------------------------------------------------
            $words = preg_replace('/[^a-zA-Z0-9 ]/', '', trim($work['work_title']));
            $record['title_words'] = $this->removeStopWords(explode(' ', strtolower($words)));

            // ----------------------------------------------------------------------------------
            $text = trim($work['work_title']);
            $sort_text = '';

            if (    (4 < strlen($text)) and
                    (0 === substr_compare($text, 'the ', 0, 4, true))){
                $sort_text = substr($text, 4);
            }
            else if (   (2 < strlen($text)) and
                    (0 === substr_compare($text, 'a ', 0, 2, true))){
                $sort_text = substr($text, 2);
            }

            $typeahead_record = array(
                'type'      => 'work',
                'display'   => $text,
                'text'      => strtolower($text),
                'sort_text' => strtolower($sort_text),
                'work_id'   => $work['work_id'],
                'slug'      => $record['slug']
            );

            $typeahead_work[strtolower($text)] = $typeahead_record;
            $byEditionAsin[$work['edition_asin']] = $record;

            // ----------------------------------------------------------------------------------
            if (0 == $i % $chunk){

                $this->insertWorkRecords($byEditionAsin, $targetCollection);
                $byEditionAsin = [];

                if (0 == ($i % ($chunk*10))){
                    $this->writeln("<info>$i works processed</info>");
                }
            }
        }
        $this->insertWorkRecords($byEditionAsin, $targetCollection);

        // --------------------------------------------------------------------
        $mongodb        = $this->mongo->getDb();

        $this->writeln("<comment>creating amzn_salesrank index...</comment>");
        $mongodb->works_new->createIndex(['amzn_salesrank' => 1]);

        $this->writeln("<comment>creating browse_node index...</comment>");
        $mongodb->works_new->createIndex(['amzn_authorlist' => 1]);

        $this->writeln("<comment>creating author name index...</comment>");
        $mongodb->works_new->createIndex(['browse_node' => 1]);

        $this->writeln("<comment>creating title_words index...</comment>");
        $mongodb->works_new->createIndex(['title_words' => 1], ['sparse' => true]);

        $this->writeln("<comment>creating title text index...</comment>");
        $mongodb->works_new->createIndex(['title_words' => 'text']);
        // --------------------------------------------------------------------

        return [$typeahead_work, $typeahead_author];
    }

    private function fetchData(): Statement
    {
        $mongodb        = $this->mongo->getDb();
        $em             = $this->em;
        $dbh            = $this->em->getConnection();

        $sql = '
            SELECT
                work.id AS work_id,
                work.title AS work_title,
                edition.asin AS edition_asin,
                edition.work_id AS edition_work_id,
                edition.format_id AS edition_format_id,
                edition.isbn AS edition_isbn,
                edition.title AS edition_title,
                edition.pub_title AS edition_pub_title,
                edition.pub_subtitle AS edition_pub_subtitle,
                edition.created_at AS edition_created_at,
                edition.updated_at AS edition_updated_at,
                edition.publisher_scraped_at AS edition_publisher_scraped_at,
                edition.amzn_updated_at AS edition_amzn_updated_at,
                edition.amzn_scraped_at AS edition_amzn_scraped_at,
                edition.url AS edition_url,
                edition.amzn_authordisplay AS edition_amzn_authordisplay,
                edition.amzn_authorlist AS edition_amzn_authorlist,
                edition.amzn_small_cover AS edition_amzn_small_cover,
                edition.amzn_small_cover_x AS edition_amzn_small_cover_x,
                edition.amzn_small_cover_y AS edition_amzn_small_cover_y,
                edition.amzn_medium_cover AS edition_amzn_medium_cover,
                edition.amzn_medium_cover_x AS edition_amzn_medium_cover_x,
                edition.amzn_medium_cover_y AS edition_amzn_medium_cover_y,
                edition.amzn_large_cover AS edition_amzn_large_cover,
                edition.amzn_large_cover_x AS edition_amzn_large_cover_x,
                edition.amzn_large_cover_y AS edition_amzn_large_cover_y,
                edition.list_price AS edition_list_price,
                edition.amzn_price AS edition_amzn_price,
                edition.amzn_publisher AS edition_amzn_publisher,
                edition.amzn_salesrank AS edition_amzn_salesrank,
                edition.amzn_format AS edition_amzn_format,
                edition.pages AS edition_pages,
                edition.publication_date AS edition_publication_date,
                edition.release_date AS edition_release_date,
                edition.description AS edition_description,
                edition.amzn_editorial_review_source AS edition_amzn_editorial_review_source,
                edition.amzn_editorial_review AS edition_amzn_editorial_review,
                edition.amzn_alternatives AS edition_amzn_alternatives,
                edition.sig AS edition_sig,
                edition.active AS edition_active,
                edition.deleted AS edition_deleted,
                edition.amzn_edition AS edition_amzn_edition,
                edition.amzn_manufacturer AS edition_amzn_manufacturer,
                edition.amzn_brand AS edition_amzn_brand,
                edition.apparent_amzn_osi AS edition_apparent_amzn_osi,
                edition.rejected AS edition_rejected,
                edition.slug AS edition_slug
            FROM
                edition, work
            WHERE
                work.deleted = 0 AND
                work.front_edition_asin = edition.asin AND
                edition.active = 1 AND
                work.superseding_id IS NULL AND
                edition.deleted = 0 AND
                edition.rejected = 0';

        $sth = $dbh->prepare($sql);

        $sth->execute();

        return $sth;
    }

    private function insertWorkRecords(
        array $byEditionAsin,
        string $targetCollection
    ): void
    {
        $mongodb        = $this->mongo->getDb();
        $dbh            = $this->em->getConnection();

        $inputList = substr(str_repeat(',?', count($byEditionAsin)), 1);

        $sql = "
            SELECT
                edition_asin, browsenode_id
            FROM
                browsenode_edition
            WHERE
                edition_asin in ($inputList)";

        $sth = $dbh->prepare($sql);
        $sth->execute(array_keys($byEditionAsin));

        while ($result = $sth->fetch()){
            $byEditionAsin[$result['edition_asin']]['browse_node'][] = (int)$result['browsenode_id'];
        }

        foreach ($byEditionAsin as $id => $record){
            $mongodb->$targetCollection->insert($record);
        }
    }

    private function removeStopWords(array $words): array
    {
        $clean = [];

        foreach ($words as $w){
            if ($w){
                if (!in_array($w, self::STOP_WORDS)){
                    $clean[] = $w;
                }
            }
        }
        return $clean;
    }
}
