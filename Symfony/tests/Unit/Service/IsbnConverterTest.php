<?php

namespace App\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use App\Tests\Lib\BaseTest;
use App\Service\IsbnConverter;

/**
 * @covers App\Service\IsbnConverter
 */
class IsbnConverterTest extends BaseTest
{
    private $isbnConverter;

    public function setUp()
    {
        $this->isbnConverter = new IsbnConverter();
    }

    public function testIsbn10to13(): void
    {
        $this->assertEquals(
            '9780307719218',
            $this->isbnConverter->isbn10to13('0307719219')
        );
    }
    public function testIsbn13to10(): void
    {
        $this->assertEquals(
            '0307719219',
            $this->isbnConverter->isbn13to10('9780307719218')
        );
    }
/*
    public function testIsbnFromSxe(): void
    {
        $this->assertTrue(true);

    }
*/

}
