<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Processor\Processing\Pseudonymize;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Waldhacker\Pseudify\Core\Faker\Faker;
use Waldhacker\Pseudify\Core\Processor\Processing\Pseudonymize\DataManipulatorContext;

class DataManipulatorContextTest extends TestCase
{
    use ProphecyTrait;

    public function testFakeReturnsFakerWithDefaultScope(): void
    {
        $processedData = 'foo';
        $fakerMock = $this->createPartialMock(Faker::class, ['withScope', 'withSource', '__call']);

        $fakerMock->expects($this->once())->method('withScope')->with(Faker::DEFAULT_SCOPE)->will($this->returnSelf());
        $fakerMock->expects($this->once())->method('withSource')->with($processedData)->will($this->returnSelf());

        $dataManipulatorContext = new DataManipulatorContext($fakerMock, null, null, [], $processedData);

        $this->assertInstanceOf(
            Faker::class,
            $dataManipulatorContext->fake()
        );
    }

    public function testFakeReturnsFakerWithCustomScope(): void
    {
        $scope = 'bar';
        $processedData = 'foo';
        $fakerMock = $this->createPartialMock(Faker::class, ['withScope', 'withSource', '__call']);

        $fakerMock->expects($this->once())->method('withScope')->with($scope)->will($this->returnSelf());
        $fakerMock->expects($this->once())->method('withSource')->with($processedData)->will($this->returnSelf());

        $dataManipulatorContext = new DataManipulatorContext($fakerMock, null, null, [], $processedData);

        $this->assertInstanceOf(
            Faker::class,
            $dataManipulatorContext->fake(scope: $scope)
        );
    }

    public function testFakeReturnsFakerWithCustomScopeForSpecialSource(): void
    {
        $scope = 'bar';
        $source = 'baz';
        $processedData = 'foo';

        $fakerMock = $this->createPartialMock(Faker::class, ['withScope', 'withSource', '__call']);

        $fakerMock->expects($this->once())->method('withScope')->with($scope)->will($this->returnSelf());
        $fakerMock->expects($this->once())->method('withSource')->with($source)->will($this->returnSelf());

        $dataManipulatorContext = new DataManipulatorContext($fakerMock, null, null, [], $processedData);

        $this->assertInstanceOf(
            Faker::class,
            $dataManipulatorContext->fake(scope: $scope, source: $source)
        );
    }

    public function testGetRawDataReturnsRawData(): void
    {
        $rawData = ['bar'];

        $fakerProphecy = $this->prophesize(Faker::class);
        $dataManipulatorContext = new DataManipulatorContext($fakerProphecy->reveal(), $rawData, null, []);

        $this->assertEquals(
            $rawData,
            $dataManipulatorContext->getRawData()
        );
    }

    public function testGetDecodedDataReturnsDecodedData(): void
    {
        $decodedData = ['bar'];

        $fakerProphecy = $this->prophesize(Faker::class);
        $dataManipulatorContext = new DataManipulatorContext($fakerProphecy->reveal(), null, $decodedData, []);

        $this->assertEquals(
            $decodedData,
            $dataManipulatorContext->getDecodedData()
        );
    }

    public function testGetDatebaseRowReturnsDatebaseRow(): void
    {
        $databaseRow = ['uid' => 1, 'title' => 'foo'];

        $fakerProphecy = $this->prophesize(Faker::class);
        $dataManipulatorContext = new DataManipulatorContext($fakerProphecy->reveal(), null, null, $databaseRow);

        $this->assertEquals(
            $databaseRow,
            $dataManipulatorContext->getDatebaseRow()
        );
    }

    public function testGetProcessedDataReturnsProcessedData(): void
    {
        $processedData = 'foo';

        $fakerProphecy = $this->prophesize(Faker::class);
        $dataManipulatorContext = new DataManipulatorContext($fakerProphecy->reveal(), null, null, [], $processedData);

        $this->assertEquals(
            $processedData,
            $dataManipulatorContext->getProcessedData()
        );
    }

    public function testWithProcessedDataReturnsNewInstanceWithProcessedData(): void
    {
        $processedData = 'foo';

        $fakerProphecy = $this->prophesize(Faker::class);
        $dataManipulatorContext = new DataManipulatorContext($fakerProphecy->reveal(), 'foo', 'bar', ['uid' => 1], 'baz');

        $this->assertEquals(
            'baz',
            $dataManipulatorContext->getProcessedData()
        );

        $dataManipulatorContext = $dataManipulatorContext->withProcessedData('custom');

        $this->assertEquals(
            'custom',
            $dataManipulatorContext->getProcessedData()
        );

        $this->assertEquals(
            'foo',
            $dataManipulatorContext->getRawData()
        );

        $this->assertEquals(
            'bar',
            $dataManipulatorContext->getDecodedData()
        );

        $this->assertEquals(
            ['uid' => 1],
            $dataManipulatorContext->getDatebaseRow()
        );
    }
}
