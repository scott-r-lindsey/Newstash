<?php

namespace App\Tests\Functional\Service;

use App\Entity\Edition;
use App\Tests\Lib\BaseTest;
use PHPUnit\Framework\TestCase;

class EditionManagerTest extends BaseTest
{
    protected $DBSetup = true;

    public function testStubEdition(): void
    {

        $asin               = '0674979850';
        $isbn               = '9871234567890';

        $em                 = self::$container->get('doctrine')->getManager();
        $editionManager     = self::$container->get('test.App\Service\EditionManager');

        // --------------------------------------------------------------------
        // create a record via an upsert

        $editionManager->stubEditions([$asin]);

        $edition = $em->getRepository(edition::class)
            ->findOneByAsin($asin);

        $this->assertEquals(
            null,
            $edition->getIsbn()
        );

        // --------------------------------------------------------------------

        $edition->setIsbn($isbn);

        $em->flush();

        // --------------------------------------------------------------------
        // prove that an upsert will do nothing in this case

        $editionManager->stubEditions([$asin]);

        $edition = $em->getRepository(edition::class)
            ->findOneByAsin($asin);

        $this->assertEquals(
            $isbn,
            $edition->getIsbn()
        );
    }

    public function testSimilarEditionUpdate(): void
    {
        $em                 = self::$container->get('doctrine')->getManager();
        $dbh                = $em->getConnection();
        $editionManager     = self::$container->get('test.App\Service\EditionManager');

        // --------------------------------------------------------------------
        // stub some records

        $editionManager->stubEditions([
            '0000000000',
            '0000000001',
            '0000000002',
            '0000000003',
            '0000000004',
            '0000000005',
            '0000000006',
            '0000000007',
            '0000000008',
            '0000000009'
        ]);

        $count = $dbh->query('SELECT COUNT(*) as c FROM similar_edition')
            ->fetchAll()[0]['c'];

        $this->assertEquals(
            0,
            $count
        );

        // --------------------------------------------------------------------
        // run the thing

        $editionManager->similarUpdate(
            '0000000000',
            [
                '0000000001',
                '0000000002',
                '0000000003',
                '0000000004',
                '0000000005',
                '0000000006',
                '0000000007',
                '0000000008',
                '0000000009'
            ]
        );

        $count = $dbh->query('SELECT COUNT(*) as c FROM similar_edition')
            ->fetchAll()[0]['c'];

        $this->assertEquals(
            9,
            $count
        );

        // --------------------------------------------------------------------
        // run the update

        $editionManager->similarUpdate(
            '0000000000',
            [
                '0000000001',
                '0000000002',
                '0000000003'
            ]
        );

        $count = $dbh->query('SELECT COUNT(*) as c FROM similar_edition')
            ->fetchAll()[0]['c'];

        $this->assertEquals(
            3,
            $count
        );
    }
}
