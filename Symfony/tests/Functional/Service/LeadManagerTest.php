<?php

namespace App\Tests\Functional\Service\Apa;

use App\Entity\Edition;
use App\Entity\Lead;
use App\Entity\BrowseNode;
use App\Tests\Lib\BaseTest;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @covers App\Service\LeadManager
 */
class LeadManagerTest extends BaseTest
{
    protected $DBSetup = true;

    public function testBasic(): void
    {
        $em             = self::$container->get('doctrine')->getManager();
        $dbh            = $em->getConnection();
        $leadManager    = self::$container->get('test.App\Service\LeadManager');

        $asin           = '0674979850';

        $leadManager->newLeads([$asin]);

        // validate lead count
        $count = $dbh->query('SELECT COUNT(*) as c FROM xlead')->fetchAll()[0]['c'];

        $this->assertEquals(
            1,
            $count
        );

        // validate similar edition count
        $count = $dbh->query('SELECT COUNT(*) as c FROM edition')->fetchAll()[0]['c'];

        $this->assertEquals(
            1,
            $count
        );
    }

    public function testLeadFollowed(): void
    {
        $em             = self::$container->get('doctrine')->getManager();
        $dbh            = $em->getConnection();
        $leadManager    = self::$container->get('test.App\Service\LeadManager');

        $asin           = '0674979850';

        $leadManager->newLeads([$asin]);

        // validate lead count
        $count = $dbh->query('SELECT COUNT(*) as c FROM xlead')
                ->fetchAll()[0]['c'];

        $this->assertEquals(
            1,
            $count
        );

        // validate lead count
        $count = $dbh->query('SELECT COUNT(*) as c FROM xlead where new = 1 ')
                ->fetchAll()[0]['c'];

        $this->assertEquals(
            1,
            $count
        );

        $leadManager->leadFollowed($asin);

        // validate lead count
        $count = $dbh->query('SELECT COUNT(*) as c FROM xlead where new = 1 ')
                ->fetchAll()[0]['c'];

        $this->assertEquals(
            0,
            $count
        );


    }
}
