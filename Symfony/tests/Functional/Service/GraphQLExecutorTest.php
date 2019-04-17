<?php

namespace App\Tests\Functional\Service\Export;

use App\Tests\Lib\BaseTest;

/**
 * @covers App\Service\GraphQLExecutor
 */
class GraphQLExecutorTest extends BaseTest
{
    protected $DBSetup = true;

    public function testBasic(): void
    {

        // this defeats an error:
        // Error: Class 'Overblog\GraphQLBundle\__DEFINITIONS__\QueryType' not found
        static::$kernel->getContainer()->get('overblog_graphql.cache_compiler')->loadClasses(true);

        // --------------------------------------------------------------------

        $gqle               = self::$container->get('test.App\Service\GraphQLExecutor');
        $workGroomer        = self::$container->get('test.App\Service\Data\WorkGroomer');

        $edition            = $this->loadEditionFromXML('product-sample.xml');
        $workGroomer->workGroomLogic('0674979850');

        $query = '
            {
              work(id: 1) {
                id
                title
              }
            }';

        // --------------------------------------------------------------------

        $result = $gqle->execute($query);

        $this->assertEquals(
            'Capital in the Twenty-First Century',
            $result['data']['work']['title']
        );
    }
}
