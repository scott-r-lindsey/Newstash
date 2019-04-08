<?php

namespace App\GraphQL\Loader;

use App\Repository\WorkRepository;
use GraphQL\Executor\Promise\PromiseAdapter;

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

    public function all(array $ids)
    {
        $qb = $this->repository->createQueryBuilder('w');
        $qb->add('where', $qb->expr()->in('w.id', ':ids'));
        $qb->setParameter('ids', $ids);
        $works = $qb->getQuery()->getResult();

        return $this->promiseAdapter->all($works);
    }
}
