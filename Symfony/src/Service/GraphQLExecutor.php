<?php
declare(strict_types=1);

namespace App\Service;

use Psr\Log\LoggerInterface;
use Overblog\GraphQLBundle\Request\Executor as RequestExecutor;

class GraphQLExecutor
{

    private $logger;
    private $requestExecutor;

    public function __construct(
        LoggerInterface $logger,
        RequestExecutor $requestExecutor
    )
    {
        $this->logger               = $logger;
        $this->requestExecutor      = $requestExecutor;
    }

    public function execute(string $query): array
    {

        $request = [
            'query'             => $query,
            'variables'         => [],
            'operationName'     => null,
        ];

        return $this->requestExecutor
            ->execute(null, $request)
            ->toArray();
    }
}
