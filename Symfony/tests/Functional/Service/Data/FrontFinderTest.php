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
 * @covers App\Service\Data\FrontFinder
 */
class FrontFinderTest extends BaseTest
{
    protected $DBSetup = true;

    // much assert. so overkill
    public function testFormats(): void
    {

        $ff    = self::$container->get('test.App\Service\Data\FrontFinder');

        // paperback
        $format = $this->getFormatByName('Paperback');
        $this->assertTrue(  $ff->isPaper($format->getId()));
        $this->assertFalse( $ff->isE($format->getId()));
        $this->assertFalse( $ff->isAudio($format->getId()));

        // hardcover
        $format = $this->getFormatByName('Hardcover');
        $this->assertTrue(  $ff->isPaper($format->getId()));
        $this->assertFalse( $ff->isE($format->getId()));
        $this->assertFalse( $ff->isAudio($format->getId()));

        // ebook
        $format = $this->getFormatByName('eBook');
        $this->assertFalse( $ff->isPaper($format->getId()));
        $this->assertTrue(  $ff->isE($format->getId()));
        $this->assertFalse( $ff->isAudio($format->getId()));

        // kindle
        $format = $this->getFormatByName('Kindle eBook');
        $this->assertFalse( $ff->isPaper($format->getId()));
        $this->assertTrue(  $ff->isE($format->getId()));
        $this->assertFalse( $ff->isAudio($format->getId()));

        // audio cd
        $format = $this->getFormatByName('Audio CD');
        $this->assertFalse( $ff->isPaper($format->getId()));
        $this->assertFalse( $ff->isE($format->getId()));
        $this->assertTrue(  $ff->isAudio($format->getId()));

        // downloadable
        $format = $this->getFormatByName('Downloadable Audio');
        $this->assertFalse( $ff->isPaper($format->getId()));
        $this->assertFalse( $ff->isE($format->getId()));
        $this->assertTrue(  $ff->isAudio($format->getId()));

        // kindle enhanced
        $format = $this->getFormatByName('Kindle Edition with Audio/Video');
        $this->assertFalse( $ff->isPaper($format->getId()));
        $this->assertTrue(  $ff->isE($format->getId()));
        $this->assertFalse( $ff->isAudio($format->getId()));

        // ebook
        $format = $this->getFormatByName('MP3 CD');
        $this->assertFalse( $ff->isPaper($format->getId()));
        $this->assertFalse( $ff->isE($format->getId()));
        $this->assertTrue(  $ff->isAudio($format->getId()));

        $this->assertTrue(true);
    }

    public function testCmp(): void
    {
        $now        = ['releaseDate' => new \DateTime('now')];
        $later      = ['releaseDate' => new \DateTime('next week')];
        $before     = ['releaseDate' => new \DateTime('last week')];

        $this->assertEquals(0, FrontFinder::cmp($now, $now));
        $this->assertEquals(-1, FrontFinder::cmp($now, $before));
        $this->assertEquals(1, FrontFinder::cmp($now, $later));
    }

    public function testFront()
    {
        $ff         = self::$container->get('test.App\Service\Data\FrontFinder');

        $now        = array( 'releaseDate' => new \DateTime('now'));
        $later      = array( 'releaseDate' => new \DateTime('next week'));
        $before     = array( 'releaseDate' => new \DateTime('last week'));

        // -- date sorting ---------------------------------------------------------------------
        $f = $ff->getFrontLogic(array(
            array(
               'id'             => 'now paperback',
               'format_id'      => 1,
               'has_cover'      => true,
               'active'         => true,
               'releaseDate'    => $now
            ),
            array(
               'id'             => 'new paperback',
               'format_id'      => 1,
               'has_cover'      => true,
               'active'         => true,
               'releaseDate'    => $later
            ),
            array(
               'id'             => 'old paperback',
               'format_id'      => 1,
               'has_cover'      => true,
               'active'         => true,
               'releaseDate'    => $before
            )
        ));
        $this->assertEquals('new paperback', $f['id']);

        // -- non active book should be dropped ------------------------------------------------
        $f = $ff->getFrontLogic(array(
            array(
               'id'             => 'active paperback',
               'format_id'      => 1,
               'has_cover'      => false,
               'active'         => true,
               'releaseDate'    => $now
            ),
            array(
               'id'             => 'inactive paperback',
               'format_id'      => 1,
               'has_cover'      => true,
               'active'         => false,
               'releaseDate'    => $later
            ),
        ));
        $this->assertEquals('active paperback', $f['id']);

        // -- cover over non-cover -------------------------------------------------------------
        $f = $ff->getFrontLogic(array(
            array(
               'id'             => 'coverless paperback',
               'format_id'      => 1,
               'has_cover'      => false,
               'active'         => true,
               'releaseDate'    => $now
            ),
            array(
               'id'             => 'covered paperback',
               'format_id'      => 1,
               'has_cover'      => true,
               'active'         => true,
               'releaseDate'    => $before
            ),
        ));
        $this->assertEquals('covered paperback', $f['id']);

        // -- paper over ebook -----------------------------------------------------------------
        $f = $ff->getFrontLogic(array(
            array(
               'id'             => 'paperback',
               'format_id'      => 1,
               'has_cover'      => true,
               'active'         => true,
               'releaseDate'    => $now
            ),
            array(
               'id'             => 'ebook',
               'format_id'      => 3,
               'has_cover'      => true,
               'active'         => true,
               'releaseDate'    => $now
            ),
        ));
        $this->assertEquals('paperback', $f['id']);
        // -- ebook over audio -----------------------------------------------------------------
        $f = $ff->getFrontLogic(array(
            array(
               'id'             => 'ebook',
               'format_id'      => 3,
               'has_cover'      => true,
               'active'         => true,
               'releaseDate'    => $now
            ),
            array(
               'id'             => 'audio',
               'format_id'      => 5,
               'has_cover'      => true,
               'active'         => true,
               'releaseDate'    => $now
            ),
        ));
        $this->assertEquals('ebook', $f['id']);

        // -- everything -----------------------------------------------------------------------
        $universe = array(
            array(
               'id'             => 'paperWCover',
               'format_id'      => 1,
               'has_cover'      => true,
               'active'         => true,
               'releaseDate'    => $now
            ),
            array(
               'id'             => 'eWCover',
               'format_id'      => 3,
               'has_cover'      => true,
               'active'         => true,
               'releaseDate'    => $now
            ),
            array(
               'id'             => 'audioWCover',
               'format_id'      => 5,
               'has_cover'      => true,
               'active'         => true,
               'releaseDate'    => $now
            ),
            array(
               'id'             => 'paperWOCover',
               'format_id'      => 1,
               'has_cover'      => false,
               'active'         => true,
               'releaseDate'    => $now
            ),
            array(
               'id'             => 'eWOCover',
               'format_id'      => 3,
               'has_cover'      => false,
               'active'         => true,
               'releaseDate'    => $now
            ),
            array(
               'id'             => 'audioWOCover',
               'format_id'      => 5,
               'has_cover'      => false,
               'active'         => true,
               'releaseDate'    => $now
            ),
            array(
               'id'             => 'inactive',
               'format_id'      => 1,
               'has_cover'      => true,
               'active'         => false,
               'releaseDate'    => $later
            )
        );

        $keys = array(
            'paperWCover',
            'eWCover',
            'audioWCover',
            'paperWOCover',
            'eWOCover',
            'audioWOCover' );

        foreach ($keys as $key){
            $f = $ff->getFrontLogic($universe);
            $this->assertEquals($key, $f['id']);

            $rand = $universe;
            shuffle($rand);
            $f = $ff->getFrontLogic($rand);
            $this->assertEquals($key, $f['id']);

            $rand = $universe;
            shuffle($rand);
            $f = $ff->getFrontLogic($rand);
            $this->assertEquals($key, $f['id']);

            array_shift($universe);
        }

        // -- again with entities --------------------------------------------------------------

        $f_pb = $this->getFormatByName('Paperback');
        $f_eb = $this->getFormatByName('eBook');
        $f_au = $this->getFormatByName('Audio CD');

        $now = new \DateTime();

        $universe = [
            $this->makeEdition(1, $f_pb, 'xyz', true, $now),
            $this->makeEdition(2, $f_eb, 'xyz', true, $now),
            $this->makeEdition(3, $f_au, 'xyz', true, $now),
            $this->makeEdition(4, $f_pb, null, true, $now),
            $this->makeEdition(5, $f_eb, null, true, $now),
            $this->makeEdition(6, $f_au, null, true, $now),
            $this->makeEdition(7, $f_pb, 'xyz', false, $now)
        ];

        foreach (range(1, 6) as $key){
            $e = $ff->getFront($universe);
            $this->assertEquals($key, $e->getAsin());

            $rand = $universe;
            shuffle($rand);
            $e = $ff->getFront($rand);
            $this->assertEquals($key, $e->getAsin());

            $rand = $universe;
            shuffle($rand);
            $e = $ff->getFront($rand);
            $this->assertEquals($key, $e->getAsin());

            array_shift($universe);
        }

        // FIXME validate failure condition
    }

    // ------------------------------------------------------------------------

    private function makeEdition(
        $asin,
        $format,
        $cover,
        $active,
        $date
    ): Edition
    {

        $edition = new Edition();
        $edition->setAsin($asin)
            ->setFormat($format)
            ->setAmznLargeCover($cover)
            ->setActive($active)
            ->setReleasedate($date);

        return $edition;
    }

    private function getFormatByName(string $name): Format
    {
        $em             = self::$container->get('doctrine')->getManager();

        return $em->getRepository(Format::class)->findOneByDescription($name);
    }
}
