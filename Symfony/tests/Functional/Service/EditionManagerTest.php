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
}
