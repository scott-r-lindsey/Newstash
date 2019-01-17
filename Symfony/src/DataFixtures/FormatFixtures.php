<?php
declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Format;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class FormatFixtures extends Fixture
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager) {

        // name, paper, e, audio
        $fix = [
            ['Paperback', true, false, false, false],
            ['Hardcover', true, false, false, false],
            ['eBook', false, true, false, false],
            ['Kindle eBook', false, true, false, true],
            ['Audio CD', false, false, true, false],
            ['Downloadable Audio', false, false, true, false],
            ['Kindle Edition with Audio/Video', false, true, false, true],
        ];

        foreach ($fix as $f){
            $format      = new Format();
            $format->setDescription(    $f[0]);
            $format->setPaper(          $f[1]);
            $format->setE(              $f[2]);
            $format->setAudio(          $f[3]);
            $format->setNoprice(        $f[4]);
            $manager->persist(          $format);
        }

        $manager->flush();
    }
}
