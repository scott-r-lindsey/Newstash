<?php

namespace App\GraphQL\Loader;

use App\Repository\FormatRepository;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;

class FormatLoader extends AbstractSortingLoader
{
    public function __construct(
        PromiseAdapter $promiseAdapter,
        FormatRepository $repository
    )
    {
        $this->promiseAdapter = $promiseAdapter;
        $this->repository = $repository;
    }

    public function __invoke(array $ids): Promise
    {
        $formats = $this->repository->findById($ids);

        return $this->promiseAdapter->all(
            $this->sortByIds($ids, $formats)
        );
    }
}
