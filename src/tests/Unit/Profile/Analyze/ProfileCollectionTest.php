<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Profile\Analyze;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Waldhacker\Pseudify\Core\Profile\Analyze\MissingProfileException;
use Waldhacker\Pseudify\Core\Profile\Analyze\ProfileCollection;
use Waldhacker\Pseudify\Core\Profile\Analyze\ProfileInterface;

class ProfileCollectionTest extends TestCase
{
    use ProphecyTrait;

    public function testConstructorFiltersProfileInterfaces(): void
    {
        $profile1 = $this->prophesize(ProfileInterface::class);
        $profile1->getIdentifier()->willReturn('profile-1');
        $profile2 = new \stdClass();
        $profile3 = $this->prophesize(ProfileInterface::class);
        $profile3->getIdentifier()->willReturn('profile-3');

        $profileCollection = new ProfileCollection([$profile1->reveal(), $profile2, $profile3->reveal()]);

        $this->assertEquals(
            ['profile-1', 'profile-3'],
            $profileCollection->getProfileIdentifiers()
        );
    }

    public function testHasProfileReturnsTrue(): void
    {
        $profile1 = $this->prophesize(ProfileInterface::class);
        $profile1->getIdentifier()->willReturn('profile-1');
        $profileCollection = new ProfileCollection([$profile1->reveal()]);

        $this->assertTrue($profileCollection->hasProfile('profile-1'));
    }

    public function testHasProfileReturnsFalse(): void
    {
        $profile1 = $this->prophesize(ProfileInterface::class);
        $profile1->getIdentifier()->willReturn('profile-1');
        $profileCollection = new ProfileCollection([$profile1->reveal()]);

        $this->assertFalse($profileCollection->hasProfile('profile-2'));
    }

    public function testGetProfileReturnsProfile(): void
    {
        $profile1 = $this->prophesize(ProfileInterface::class);
        $profile1->getIdentifier()->willReturn('profile-1');
        $profileCollection = new ProfileCollection([$profile1->reveal()]);

        $this->assertEquals(
            $profile1->reveal(),
            $profileCollection->getProfile('profile-1')
        );
    }

    public function testGetProfileThrowsExceptionIfProfileNotExists(): void
    {
        $this->expectException(MissingProfileException::class);
        $this->expectExceptionCode(1621656966);

        $profile1 = $this->prophesize(ProfileInterface::class);
        $profile1->getIdentifier()->willReturn('profile-1');
        $profileCollection = new ProfileCollection([$profile1->reveal()]);

        $profileCollection->getProfile('profile-2');
    }

    public function testAddProfileAddsProfile(): void
    {
        $profile1 = $this->prophesize(ProfileInterface::class);
        $profile1->getIdentifier()->willReturn('profile-1');
        $profile2 = $this->prophesize(ProfileInterface::class);
        $profile2->getIdentifier()->willReturn('profile-2');

        $profileCollection = new ProfileCollection([$profile1->reveal()]);
        $profileCollection->addProfile($profile2->reveal());

        $this->assertEquals(
            $profile2->reveal(),
            $profileCollection->getProfile('profile-2')
        );
    }

    public function testRemoveProfileRemovesProfile(): void
    {
        $this->expectException(MissingProfileException::class);
        $this->expectExceptionCode(1621656966);

        $profile1 = $this->prophesize(ProfileInterface::class);
        $profile1->getIdentifier()->willReturn('profile-1');
        $profile2 = $this->prophesize(ProfileInterface::class);
        $profile2->getIdentifier()->willReturn('profile-2');

        $profileCollection = new ProfileCollection([$profile1->reveal(), $profile2->reveal()]);
        $profileCollection->removeProfile('profile-2');

        $profileCollection->getProfile('profile-2');
    }
}
