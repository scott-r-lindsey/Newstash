<?php

namespace App\GraphQL\Loader;

use App\Repository\WorkRepository;
use GraphQL\Executor\Promise\PromiseAdapter;
use Overblog\DataLoader\DataLoader;
use Overblog\DataLoader\Options;

class WorkLoader
{
    private $promiseAdapter;
    private $repository;


    public function __construct(
        PromiseAdapter $promiseAdapter,
        WorkRepository $repository
    )
    {
        $this->promiseAdapter = $promiseAdapter;
        $this->repository = $repository;
    }

    public function __invoke(array $ids)
    {
        // FIXME must return in same order
        $works = $this->repository->getWorks($ids);

        return $this->promiseAdapter->all($works);
    }
}
