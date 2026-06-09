<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class UserTest extends TestCase
{
    public function testTheAutomaticApiTokenSettingWhenAnUserIsCreated(): void
    {
        $user = new User();
        $this->assertNotNull($user->getApiToken());
    }

    public function testThatAUserHasAtLeastOneRoleUser(): void
    {
        $user = new User();
        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    public function testAnException(): void
    {
        $this->expectException(\TypeError::class);

        $user = new User();
        $user->setFirstName([10]);
    }

    public static function provideFirstName(): \Generator
    {
        yield ['Thomas'];
        yield ['Eric'];
        yield ['Marie'];
    }

    #[DataProvider('provideFirstName')]
    public function testFirstNameSetter(string $name): void
    {
        $user = new User();
        $user->setFirstName($name);

        $this->assertSame($name, $user->getFirstName());
    }

}


