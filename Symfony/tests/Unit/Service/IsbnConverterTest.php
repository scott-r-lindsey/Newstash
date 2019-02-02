<?php

namespace App\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use App\Tests\Lib\BaseTest;
use App\Service\IsbnConverter;
use SimpleXMLElement;

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

    public function testIsbnFromSxeByEAN(): void
    {
        $fromEanXml = new SimpleXMLElement('
            <item>
                <ItemAttributes>
                    <EAN>9780307719218</EAN>
                </ItemAttributes>
            </item>
        ');

        $this->assertEquals(
            '9780307719218',
            $this->isbnConverter->isbnFromSxe($fromEanXml)
        );
    }

    public function testIsbnFromSxeByIsbn(): void
    {
        $fromEanXml = new SimpleXMLElement('
            <item>
                <ItemAttributes>
                    <ISBN>0307719219</ISBN>
                </ItemAttributes>
            </item>
        ');

        $this->assertEquals(
            '9780307719218',
            $this->isbnConverter->isbnFromSxe($fromEanXml)
        );
    }

    public function testIsbnFromSxeWithBaddlyFormedEAN(): void
    {
        $fromEanXml = new SimpleXMLElement('
            <item>
                <ItemAttributes>
                    <EAN>9780ABCDEFGHI</EAN>
                </ItemAttributes>
            </item>
        ');

        $this->assertEquals(
            null,
            $this->isbnConverter->isbnFromSxe($fromEanXml)
        );
    }


}
