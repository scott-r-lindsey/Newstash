<?php

namespace App\GraphQL\Loader;

use App\Repository\WorkRepository;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;

class WorkLoader extends AbstractSortingLoader
{

    public function __construct(
        PromiseAdapter $promiseAdapter,
        WorkRepository $repository
    )
    {
        $this->promiseAdapter = $promiseAdapter;
        $this->repository = $repository;
    }

    public function __invoke(array $ids): Promise
    {
        $works = $this->repository->getWorks($ids);

        return $this->promiseAdapter->all(
            $this->sortByIds($ids, $works)
        );
    }
}
