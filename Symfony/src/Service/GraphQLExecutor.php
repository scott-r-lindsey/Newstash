<?php
declare(strict_types=1);

namespace App\Service;

use Psr\Log\LoggerInterface;
use Overblog\GraphQLBundle\Request\Executor as RequestExecutor;

class GraphQLExecutor
{

    private $logger;
    private $requestExecutor;
    private $projectDir;

    public function __construct(
        LoggerInterface $logger,
        RequestExecutor $requestExecutor,
        string $projectDir
    )
    {
        $this->logger               = $logger;
        $this->requestExecutor      = $requestExecutor;
        $this->projectDir           = $projectDir;
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

    public function executeQueryByName(
        string $name,
        array $replace
    ): array
    {
        $query      = file_get_contents($this->projectDir ."/graphql/$name.graphql");

        foreach ($replace as $key => $value) {
            $query = str_replace($key, $value, $query);
        }

        return $this->execute($query);
    }


}
