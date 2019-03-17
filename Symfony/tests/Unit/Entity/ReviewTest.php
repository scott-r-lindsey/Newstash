<?php
declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use App\Tests\Lib\BaseTest;
use App\Entity\Review;

/**
 * @covers App\Entity\Review
 */
class ReviewTest extends BaseTest{

    public function testSlug(): void
    {

        $review     = new Review();
        $title      = 'This is the Title%%%%%%%';

        $review->setTitle($title);

        $this->assertEquals(
            $title,
            $review->getTitle()
        );

        $this->assertEquals(
            'this-is-the-title',
            $review->getSlug()
        );
    }
}
