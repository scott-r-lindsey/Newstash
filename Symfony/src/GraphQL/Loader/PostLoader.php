<?php

namespace App\GraphQL\Loader;

use App\Repository\PostRepository;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;

class PostLoader extends AbstractSortingLoader
{

    public function __construct(
        PromiseAdapter $promiseAdapter,
        PostRepository $repository
    )
    {
        $this->promiseAdapter = $promiseAdapter;
        $this->repository = $repository;
    }

    public function __invoke(array $ids): Promise
    {
        $posts = $this->repository->findById($ids);

        return $this->promiseAdapter->all(
            $this->sortByIds($ids, $posts)
        );
    }
}
