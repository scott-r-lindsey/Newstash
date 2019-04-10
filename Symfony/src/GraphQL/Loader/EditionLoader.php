<?php

namespace App\GraphQL\Loader;

use App\Repository\EditionRepository;
use GraphQL\Executor\Promise\PromiseAdapter;
use Overblog\DataLoader\DataLoader;
use Overblog\DataLoader\Options;

class EditionLoader
{
    private $promiseAdapter;
    private $repository;

    public function __construct(
        PromiseAdapter $promiseAdapter,
        EditionRepository $repository
    )
    {
        $this->promiseAdapter = $promiseAdapter;
        $this->repository = $repository;
    }

    public function __invoke(array $asins)
    {
        // fixme must reutrn in same order

        $qb = $this->repository->createQueryBuilder('e');
        $qb->add('where', $qb->expr()->in('e.asin', ':asins'));
        $qb->setParameter('asins', $asins);
        $editions = $qb->getQuery()->getResult();

        return $this->promiseAdapter->all($editions);
    }
}
