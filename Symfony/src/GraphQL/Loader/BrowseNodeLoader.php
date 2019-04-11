<?php

namespace App\GraphQL\Loader;

use App\Repository\BrowseNodeRepository;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;

class BrowseNodeLoader extends AbstractSortingLoader
{

    public function __construct(
        PromiseAdapter $promiseAdapter,
        BrowseNodeRepository $repository
    )
    {
        $this->promiseAdapter = $promiseAdapter;
        $this->repository = $repository;
    }

    public function __invoke(array $ids): Promise
    {
        $works = $this->repository->getBrowseNodes($ids);

        return $this->promiseAdapter->all(
            $this->sortByIds($ids, $works)
        );
    }
}
