<?php

namespace App\GraphQL\Loader;

use App\Repository\EditionRepository;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;

class EditionLoader extends AbstractSortingLoader
{

    public function __construct(
        PromiseAdapter $promiseAdapter,
        EditionRepository $repository
    )
    {
        $this->promiseAdapter = $promiseAdapter;
        $this->repository = $repository;
    }

    public function __invoke(array $asins): Promise
    {

        $editions = $this->repository->findByAsin($asins);

        return $this->promiseAdapter->all(
            $this->sortByAsin($asins, $editions)
        );
    }
}
