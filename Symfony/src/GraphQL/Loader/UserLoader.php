<?php

namespace App\GraphQL\Loader;

use App\GraphQL\Loader\AbstractSortingLoader;
use App\Repository\UserRepository;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;

class UserLoader extends AbstractSortingLoader
{

    public function __construct(
        PromiseAdapter $promiseAdapter,
        UserRepository $repository
    )
    {
        $this->promiseAdapter = $promiseAdapter;
        $this->repository = $repository;
    }

    public function __invoke(array $ids): Promise
    {
        $users = $this->repository->findById($ids);

        return $this->promiseAdapter->all(
            $this->sortByIds($ids, $users)
        );
    }
}
