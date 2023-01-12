<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Processor\Processing\Analyze;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Waldhacker\Pseudify\Core\Processor\Processing\AmbiguousDataProcessingException;
use Waldhacker\Pseudify\Core\Processor\Processing\Analyze\SourceDataCollector;
use Waldhacker\Pseudify\Core\Processor\Processing\Analyze\SourceDataCollectorContext;
use Waldhacker\Pseudify\Core\Processor\Processing\DataProcessingInterface;
use Waldhacker\Pseudify\Core\Tests\Unit\Processor\Processing\Fixtures\InvalidDataProcessing;

class SourceDataCollectorTest extends TestCase
{
    use ProphecyTrait;

    public function testProcessProccessValidProcessings(): void
    {
        $sourceDataCollectorContextProphecy = $this->prophesize(SourceDataCollectorContext::class);
        $sourceDataCollectorContextProphecy->getCollectedData()->willReturn([]);

        $dataProcessing1Prophecy = $this->prophesize(DataProcessingInterface::class);
        $dataProcessing1Prophecy->getIdentifier()->shouldBeCalled()->willReturn('id-1');
        $dataProcessing1Prophecy->getProcessor()->shouldBeCalled()->willReturn(function () { return; });

        $dataProcessing2Prophecy = $this->prophesize(DataProcessingInterface::class);
        $dataProcessing2Prophecy->getIdentifier()->shouldBeCalled()->willReturn('id-2');
        $dataProcessing2Prophecy->getProcessor()->shouldBeCalled()->willReturn(function () { return; });

        $dataProcessing3Prophecy = $this->prophesize(InvalidDataProcessing::class);
        $dataProcessing3Prophecy->getIdentifier()->shouldNotBeCalled()->willReturn('id-3');
        $dataProcessing3Prophecy->getProcessor()->shouldNotBeCalled()->willReturn(function () { return; });

        $sourceDataCollector = new SourceDataCollector();
        $sourceDataCollector->process(
            $sourceDataCollectorContextProphecy->reveal(),
            $dataProcessing1Prophecy->reveal(),
            $dataProcessing2Prophecy->reveal(),
            $dataProcessing3Prophecy->reveal()
        );
    }

    public function testProcessThrowsExceptionIfProcessingIdentifiersAreEqual(): void
    {
        $sourceDataCollectorContextProphecy = $this->prophesize(SourceDataCollectorContext::class);
        $sourceDataCollectorContextProphecy->getCollectedData()->willReturn([]);

        $dataProcessing1Prophecy = $this->prophesize(DataProcessingInterface::class);
        $dataProcessing1Prophecy->getIdentifier()->willReturn('id-1');
        $dataProcessing1Prophecy->getProcessor()->willReturn(function () { return; });

        $dataProcessing2Prophecy = $this->prophesize(DataProcessingInterface::class);
        $dataProcessing2Prophecy->getIdentifier()->willReturn('id-1');
        $dataProcessing2Prophecy->getProcessor()->willReturn(function () { return; });

        $this->expectException(AmbiguousDataProcessingException::class);
        $this->expectExceptionCode(1619712131);

        $sourceDataCollector = new SourceDataCollector();
        $sourceDataCollector->process(
            $sourceDataCollectorContextProphecy->reveal(),
            $dataProcessing1Prophecy->reveal(),
            $dataProcessing2Prophecy->reveal()
        );
    }

    public function testProcessReturnsArrayWithScalarProcessorResults(): void
    {
        $sourceDataCollectorContext = new SourceDataCollectorContext(null, null, []);

        $dataProcessing1Prophecy = $this->prophesize(DataProcessingInterface::class);
        $dataProcessing1Prophecy->getIdentifier()->willReturn('id-1');
        $dataProcessing1Prophecy->getProcessor()->willReturn(function () use ($sourceDataCollectorContext) {
            $sourceDataCollectorContext->addCollectedData(1);
            $sourceDataCollectorContext->addCollectedData(1.2);
            $sourceDataCollectorContext->addCollectedData('foo');
            $sourceDataCollectorContext->addCollectedData(['bar']);
            $sourceDataCollectorContext->addCollectedData(null);
            $sourceDataCollectorContext->addCollectedData(true);
            $sourceDataCollectorContext->addCollectedData(new \stdClass());
        });

        $dataProcessing2Prophecy = $this->prophesize(DataProcessingInterface::class);
        $dataProcessing2Prophecy->getIdentifier()->willReturn('id-2');
        $dataProcessing2Prophecy->getProcessor()->willReturn(function () use ($sourceDataCollectorContext) {
            $sourceDataCollectorContext->addCollectedData(2);
        });

        $dataProcessing3Prophecy = $this->prophesize(DataProcessingInterface::class);
        $dataProcessing3Prophecy->getIdentifier()->willReturn('id-3');
        $dataProcessing3Prophecy->getProcessor()->willReturn(function () use ($sourceDataCollectorContext) {
            $sourceDataCollectorContext->addCollectedData(2.3);
        });

        $dataProcessing4Prophecy = $this->prophesize(DataProcessingInterface::class);
        $dataProcessing4Prophecy->getIdentifier()->willReturn('id-4');
        $dataProcessing4Prophecy->getProcessor()->willReturn(function () use ($sourceDataCollectorContext) {
            $sourceDataCollectorContext->addCollectedData('baz');
        });

        $dataProcessing5Prophecy = $this->prophesize(DataProcessingInterface::class);
        $dataProcessing5Prophecy->getIdentifier()->willReturn('id-5');
        $dataProcessing5Prophecy->getProcessor()->willReturn(function () use ($sourceDataCollectorContext) {
            $sourceDataCollectorContext->addCollectedData(null);
        });

        $dataProcessing6Prophecy = $this->prophesize(DataProcessingInterface::class);
        $dataProcessing6Prophecy->getIdentifier()->willReturn('id-6');
        $dataProcessing6Prophecy->getProcessor()->willReturn(function () use ($sourceDataCollectorContext) {
            $sourceDataCollectorContext->addCollectedData(true);
        });

        $dataProcessing7Prophecy = $this->prophesize(DataProcessingInterface::class);
        $dataProcessing7Prophecy->getIdentifier()->willReturn('id-7');
        $dataProcessing7Prophecy->getProcessor()->willReturn(function () use ($sourceDataCollectorContext) {
            $sourceDataCollectorContext->addCollectedData(new \stdClass());
        });

        $dataProcessing8Prophecy = $this->prophesize(DataProcessingInterface::class);
        $dataProcessing8Prophecy->getIdentifier()->willReturn('id-8');
        $dataProcessing8Prophecy->getProcessor()->willReturn(function () use ($sourceDataCollectorContext) {
            $sourceDataCollectorContext->addCollectedData('baz');
        });

        $sourceDataCollector = new SourceDataCollector();

        $this->assertEquals(
            [
                1,
                1.2,
                'foo',
                'bar',
                true,
                2,
                2.3,
                'baz',
            ],
            $sourceDataCollector->process(
                $sourceDataCollectorContext,
                $dataProcessing1Prophecy->reveal(),
                $dataProcessing2Prophecy->reveal(),
                $dataProcessing3Prophecy->reveal(),
                $dataProcessing4Prophecy->reveal(),
                $dataProcessing5Prophecy->reveal(),
                $dataProcessing6Prophecy->reveal(),
                $dataProcessing7Prophecy->reveal(),
                $dataProcessing8Prophecy->reveal()
            )
        );
    }
}
