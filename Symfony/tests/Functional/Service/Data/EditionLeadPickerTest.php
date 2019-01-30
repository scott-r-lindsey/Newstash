<?php

namespace App\Tests\Functional\Service\Data;

use App\Entity\Edition;
use App\Entity\Format;
use App\Tests\Lib\BaseTest;
use App\Service\Data\FrontFinder;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @covers App\Service\Data\EditionLeadPicker
 */
class EditionLeadPickerTest extends BaseTest
{
    protected $DBSetup = true;

    public function testBasic(): void
    {

        $elp                = self::$container->get('test.App\Service\Data\EditionLeadPicker');
        $editionManager     = self::$container->get('test.App\Service\EditionManager');

        $editionManager->stubEditions(['A123456789']);

        $asins = $elp->find([]);

        $this->assertEquals(
            ['A123456789'],
            $asins
        );
    }

    public function testLoaded(): void
    {

        $elp            = self::$container->get('test.App\Service\Data\EditionLeadPicker');

        $edition = $this->loadEditionFromXML('product-sample.xml');

        $exclude = [
            '0674504801',
            '1491591617',
            'B00K33AFOK',
            '0674980255',
            '1683783069',
            'B00N4HGRDK',
            '0143036580',
            '0691175802',
            '1944424253',
            'B00RWTSQ4E',
            '0226264211',
            '0804170045',
            '8937834588',
            'B01GML6F86'
        ];

        $asins = $elp->find($exclude, true);

        $target = [
                '0226320618',
                '1491534648',
                '8937834693',
                'B01K3RCRIW',
                '0307719227',
                '1491534656',
                '893783488X',
                'B074DVRW88',
                '0393345068',
                '1491534664',
                '8956608105',
                '067443000X',
                '1491591609',
                '8967351275'
            ];

        // sort because updated_at is only to the second
        sort($asins);
        sort($target);

        $this->assertEquals(
            $target,
            $asins
        );
    }

    public function testOrdered(): void
    {

        // this inserts four asins
        // one gets filtered out explicitly
        // one gets marked as having been read already via amzn_updated_at
        // the other two are returned and we require them in a particular order

        $em                 = self::$container->get('doctrine')->getManager();
        $editionManager     = self::$container->get('test.App\Service\EditionManager');
        $elp                = self::$container->get('test.App\Service\Data\EditionLeadPicker');
        $editionRepo        = $em->getRepository(Edition::class);
        $dbh                = $em->getConnection();

        $a1 = 'A000000001';
        $a2 = 'A000000002';
        $a3 = 'A000000003';
        $a4 = 'A000000004';

        $editionManager->stubEdition($a1);
        $editionManager->stubEdition($a2);
        $editionManager->stubEdition($a3);
        $editionManager->stubEdition($a4);

        $sql = "
            UPDATE edition
            SET updated_at = DATE_SUB(now(), INTERVAL 10 DAY)
            WHERE asin = '$a1'
        ";

        $dbh->query($sql);

        $sql = "
            UPDATE edition
            SET updated_at = DATE_SUB(now(), INTERVAL 4 DAY)
            WHERE asin = '$a4'
        ";

        // set to once updated
        $sql = "
            UPDATE edition
            SET amzn_updated_at = DATE_SUB(now(), INTERVAL 4 DAY)
            WHERE asin = '$a3'
        ";

        $dbh->query($sql);

        $asins = $elp->find([$a2], false);

        $this->assertEquals(
            [
                $a1, $a4
            ],
            $asins
        );
    }
}
