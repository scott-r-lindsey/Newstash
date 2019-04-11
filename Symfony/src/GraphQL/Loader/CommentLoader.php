<?php

namespace App\GraphQL\Loader;

use App\Repository\CommentRepository;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;

class CommentLoader extends AbstractSortingLoader
{

    public function __construct(
        PromiseAdapter $promiseAdapter,
        CommentRepository $repository
    )
    {
        $this->promiseAdapter = $promiseAdapter;
        $this->repository = $repository;
    }

    public function __invoke(array $ids): Promise
    {
        $works = $this->repository->findById($ids);

        return $this->promiseAdapter->all(
            $this->sortByIds($ids, $works)
        );
    }
}
