<?php
declare(strict_types=1);

namespace App\Service\Mongo;

use App\Service\Mongo;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use MongoRegex;

class Typeahead
{
    private $em;
    private $logger;
    private $mongo;

    const SEARCH_LIMIT = 25;

    public function __construct(
        EntityManagerInterface $em,
        LoggerInterface $logger,
        Mongo $mongo
    )
    {
        $this->em                   = $em;
        $this->logger               = $logger;
        $this->mongo                = $mongo;
    }

    public function findSuggestions(
        string $query,
        $limit = null
    ): Array
    {
        $work_suggestions       = $this->workSuggestions($query, $limit);
        $author_suggestions     = $this->authorSuggestions($query, $limit);

        $suggestions = array_merge($author_suggestions, $work_suggestions);

        usort($suggestions, function($a, $b) {
            return strnatcmp( $a['value'], $b['value']);
        });

        return array_slice($suggestions, 0, $limit);
    }

    public function authorSuggestions(
        string $query,
        $limit = null
    ): array
    {
        ($limit) || ($limit = 20);
        $minlen             = 3;
        $mongodb            = $this->mongo->getDb();
        $typeahead_author   = $mongodb->typeahead_author;


        $regex = new MongoRegex("/^$query/");

        $author_suggestions = array();
        if (strlen($query) >= $minlen){

            $cursor = $typeahead_author->find(
                [
                    '$or' => [
                        ['text'     => $regex],
                        ['last'     => $regex]
                    ]
                ]
            );
            $cursor->limit($limit);

            foreach ($cursor as $doc) {
                $author_suggestions[] = [
                    'type'      => 'author',
                    'value'     => $doc['display'],
                    'text'      => $doc['text'],
                ];
            }
        }
        return $author_suggestions;
    }

    public function workSuggestions(
        string $query,
        $limit = null
    ): array
    {

        ($limit) || ($limit = 20);
        $mongodb            = $this->mongo->getDb();
        $typeahead_work     = $mongodb->typeahead_work;

        $regex = new MongoRegex("/^$query/");

        $cursor = $typeahead_work->find(
            [
                '$or' => [
                    ['text'          => $regex],
                    ['sort_text'     => $regex]
                ]
            ]
        );
        $cursor->limit($limit);

        $work_suggestions       = [];
        foreach ($cursor as $doc) {
            $work_suggestions[] = [
                'value'     => $doc['display'],
                'data'      => ['work_id' => $doc['work_id']],
                'type'      => 'book'
            ];
        }
        return $work_suggestions;
    }
}
