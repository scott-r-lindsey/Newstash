<?php
declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Format;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class ReasonFixtures extends Fixture
{
    /**
     * {@inheritDoc}
     */

    public function load(ObjectManager $manager) {

        $descriptions = array(
            'spam', 'troll', 'inappropriate'
        );

        foreach ($descriptions as $d){
            $reason     = new Reason();
            $reason->setDescription($d);
            $manager->persist($reason);
        }

        $manager->flush();
    }
}
