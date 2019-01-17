<?php
declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use App\Tests\Lib\BaseTest;
use App\Entity\Edition;
use App\Entity\Format;

/**
 * @covers App\Entity\Edition
 */
class EntityEditionTest extends BaseTest{

    function testEvaluateActiveLogic(){

        $edition = new Edition();

        // Normal Book
        $this->assertTrue(
            $edition->evaluateActiveLogic(
                false,
                1,
                9.99,
                false,
                false,
                new \DateTime('now'),
                new \DateTime('now')));

        // deleted book
        $this->assertFalse(
            $edition->evaluateActiveLogic(
                false,
                1,
                9.99,
                true,
                false,
                new \DateTime('now'),
                new \DateTime('now')));

        // far flung future book
        $this->assertFalse(
            $edition->evaluateActiveLogic(
                false,
                1,
                9.99,
                false,
                false,
                new \DateTime('now'),
                new \DateTime('+5 years')));

        // deleted book
        $this->assertFalse(
            $edition->evaluateActiveLogic(
                false,
                1,
                9.99,
                true,
                false,
                new \DateTime('now'),
                new \DateTime('now')));

        // priceless book
        $this->assertFalse(
            $edition->evaluateActiveLogic(
                false,
                1,
                0,
                false,
                false,
                new \DateTime('now'),
                new \DateTime('now')));

        // priceless book, but we don't expect a price
        $this->assertTrue(
            $edition->evaluateActiveLogic(
                true,
                1,
                0,
                false,
                false,
                new \DateTime('now'),
                new \DateTime('now')));

        // Book w/out pub date
        $this->assertFalse(
            $edition->evaluateActiveLogic(
                false,
                1,
                9.99,
                false,
                false,
                new \DateTime('now'),
                null));

        // Book w/out Amzn scrape data
        $this->assertFalse(
            $edition->evaluateActiveLogic(
                false,
                1,
                9.99,
                false,
                false,
                null,
                new \DateTime('now')));

        // Rejected Book
        $this->assertFalse(
            $edition->evaluateActiveLogic(
                false,
                1,
                9.99,
                false,
                true,
                new \DateTime('now'),
                new \DateTime('now')));

    }
    function testEvaluateActive(){

        // Normal Book
        $edition = new Edition();
        $edition->setPublicationDate(new \DateTime('now'));
        $edition->setAmznUpdatedAt(new \DateTime('now'));
        $f = new Format();
        $f->setNoprice(false)->testSetId(1);
        $edition->setFormat($f);
        $edition->setAmznPrice(9.99);
        $edition->setDeleted(false);
        $edition->evaluateActive();
        $this->assertTrue($edition->getActive());

        // deleted book
        $edition = new Edition();
        $edition->setPublicationDate(new \DateTime('now'));
        $edition->setAmznUpdatedAt(new \DateTime('now'));
        $f = new Format();
        $f->setNoprice(false)->testSetId(1);
        $edition->setFormat($f);
        $edition->setAmznPrice(9.99);
        $edition->setDeleted(true);
        $edition->evaluateActive();
        $this->assertFalse($edition->getActive());

        // far flung future book
        $edition = new Edition();
        $edition->setPublicationDate(new \DateTime('+5 years'));
        $edition->setAmznUpdatedAt(new \DateTime('now'));
        $f = new Format();
        $f->setNoprice(false)->testSetId(1);
        $edition->setFormat($f);
        $edition->setAmznPrice(9.99);
        $edition->setDeleted(false);
        $edition->evaluateActive();
        $this->assertFalse($edition->getActive());

        // deleted book
        $edition = new Edition();
        $edition->setPublicationDate(new \DateTime('now'));
        $edition->setAmznUpdatedAt(new \DateTime('now'));
        $f = new Format();
        $f->setNoprice(false)->testSetId(1);
        $edition->setFormat($f);
        $edition->setAmznPrice(9.99);
        $edition->setDeleted(true);
        $edition->evaluateActive();
        $this->assertFalse($edition->getActive());

        // priceless book
        $edition = new Edition();
        $edition->setPublicationDate(new \DateTime('now'));
        $edition->setAmznUpdatedAt(new \DateTime('now'));
        $f = new Format();
        $f->setNoprice(false)->testSetId(1);
        $edition->setFormat($f);
        $edition->setAmznPrice(0);
        $edition->setDeleted(false);
        $edition->evaluateActive();
        $this->assertFalse($edition->getActive());

        // priceless book, but we don't expect a price
        $edition = new Edition();
        $edition->setPublicationDate(new \DateTime('now'));
        $edition->setAmznUpdatedAt(new \DateTime('now'));
        $f = new Format();
        $f->setNoprice(true)->testSetId(1);
        $edition->setFormat($f);
        $edition->setAmznPrice(0);
        $edition->setDeleted(false);
        $edition->evaluateActive();
        $this->assertTrue($edition->getActive());

    }
}
