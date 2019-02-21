<?php
declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use App\Tests\Lib\BaseTest;
use App\Entity\User;

/**
 * @covers App\Entity\User
 */
class UserTest extends BaseTest{

    public function testBasic(): void
    {

        $user = new User();

        $user
            ->setEmail('JOE@BLOW')
            ->setEmailCanonical('joe@blow');

        $user->setUsernameFromEmail();
        $user->updatedTimestamps();

        $this->assertEquals(
            'JOE@BLOW',
            $user->getEmail()
        );

        $this->assertEquals(
            'joe@blow',
            $user->getEmailCanonical()
        );

        $this->assertEquals(
            '//www.gravatar.com/avatar/badaf314d6ee982eac7e17411ff0f532?s=80',
            $user->getAvatarUrl()
        );

        $this->assertEquals(
            ['hide' => []],
            $user->getDisplayPrefs()
        );
    }

    public function testFacebookUser(): void
    {

        $user = new User();

        $user
            ->setFacebookId('123')
            ->setEmail('JOE@BLOW')
            ->setEmailCanonical('joe@blow')
            ->setFirstName('joe')
            ->setLastName('blow');

        $this->assertEquals(
            'joe@blow',
            $user->getEmailCanonical()
        );

        $this->assertEquals(
            'https://graph.facebook.com/123/picture?width=80&height=80',
            $user->getAvatarUrl()
        );

        $this->assertEquals(
            'Joe Blow',
            $user->getName()
        );
    }

    public function testGoogleUser(): void
    {

        $user = new User();

        $user
            ->setGoogleId('123')
            ->setEmail('JOE@BLOW')
            ->setEmailCanonical('joe@blow')
            ->setFirstName('joe')
            ->setLastName('blow');

        $this->assertEquals(
            'joe@blow',
            $user->getEmailCanonical()
        );

        $this->assertEquals(
            '//www.gravatar.com/avatar/badaf314d6ee982eac7e17411ff0f532?s=80',
            $user->getAvatarUrl()
        );

        $this->assertEquals(
            'Joe Blow',
            $user->getName()
        );
    }
}
