<?php

namespace App\GraphQL\Loader;

abstract class AbstractSortingLoader
{
    protected $promiseAdapter;
    protected $repository;

    protected function sortByAsin(
        array $asins,
        array $entities
    ): array
    {

        $byAsin     = [];
        $sorted     = [];

        foreach ($entities as $e) {
            $byAsin[$e->getAsin()] = $e;
        }
        foreach ($asins as $asin) {
            $sorted[] = $byAsin[$asin];
        }

        return $sorted;
    }

    protected function sortByIds(
        array $ids,
        array $entities
    ): array
    {

        $byId       = [];
        $sorted     = [];

        foreach ($entities as $e) {
            $byId[$e->getId()] = $e;
        }
        foreach ($ids as $id) {
            $sorted[] = $byId[$id];
        }

        return $sorted;
    }
}
