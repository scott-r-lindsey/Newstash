<?php
declare(strict_types=1);

namespace App\Tests\Functional\Entity;

use PHPUnit\Framework\TestCase;
use App\Tests\Lib\BaseTest;
use App\Entity\Format;

/**
 * @covers App\Entity\Format
 */
class FormatTest extends BaseTest
{

    protected $DBSetup = true;
    private $em;

    /**
     *
     */
    public function setUp()
    {
        parent::setup();
        $this->em = self::$container->get('doctrine')->getManager();
    }

    public function testCreateFormat(): void
    {
        $description    = 'A description';
        $paper          = (bool)random_int(0, 1);
        $e              = (bool)random_int(0, 1);
        $audio          = (bool)random_int(0, 1);
        $noprice        = (bool)random_int(0, 1);

        $format = new Format();
        $format->setDescription($description)
            ->setPaper($paper)
            ->setE($e)
            ->setAudio($audio)
            ->setNoprice($noprice);

        $this->em->persist($format);
        $this->em->flush();

        $new_format = $this->em->getRepository(Format::class)
            ->findOneById($format->getId());

        $this->assertEquals(
            [
                $description,
                $paper,
                $e,
                $audio,
                $noprice
            ],
            [
                $new_format->getDescription(),
                $new_format->getPaper(),
                $new_format->getE(),
                $new_format->getAudio(),
                $new_format->getNoprice()
            ]
        );
    }
}
