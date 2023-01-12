<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Faker;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Waldhacker\Pseudify\Core\Faker\PseudifyProvider;

class PseudifyProviderTest extends TestCase
{
    use ProphecyTrait;

    public function testBcryptPassword()
    {
        $pseudifyProvider = $this->createPartialMock(PseudifyProvider::class, ['password']);
        $pseudifyProvider->expects($this->once())->method('password')->will($this->returnValue('1234'));

        $this->assertTrue(password_verify('1234', $pseudifyProvider->bcryptPassword()));
    }

    public function testArgon2iPassword()
    {
        $pseudifyProvider = $this->createPartialMock(PseudifyProvider::class, ['password']);
        $pseudifyProvider->expects($this->once())->method('password')->will($this->returnValue('1234'));

        $this->assertTrue(password_verify('1234', $pseudifyProvider->argon2iPassword()));
    }

    public function testArgon2idPassword()
    {
        $pseudifyProvider = $this->createPartialMock(PseudifyProvider::class, ['password']);
        $pseudifyProvider->expects($this->once())->method('password')->will($this->returnValue('1234'));

        $this->assertTrue(password_verify('1234', $pseudifyProvider->argon2idPassword()));
    }
}
