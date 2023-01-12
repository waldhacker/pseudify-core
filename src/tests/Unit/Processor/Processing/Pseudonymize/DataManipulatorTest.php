<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Processor\Processing\Pseudonymize;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Waldhacker\Pseudify\Core\Faker\Faker;
use Waldhacker\Pseudify\Core\Processor\Processing\AmbiguousDataProcessingException;
use Waldhacker\Pseudify\Core\Processor\Processing\DataProcessing;
use Waldhacker\Pseudify\Core\Processor\Processing\DataProcessingInterface;
use Waldhacker\Pseudify\Core\Processor\Processing\Pseudonymize\DataManipulator;
use Waldhacker\Pseudify\Core\Processor\Processing\Pseudonymize\DataManipulatorContext;
use Waldhacker\Pseudify\Core\Tests\Unit\Processor\Processing\Fixtures\InvalidDataProcessing;

class DataManipulatorTest extends TestCase
{
    use ProphecyTrait;

    public function testProcessProccessValidProcessings(): void
    {
        $dataManipulatorContextProphecy = $this->prophesize(DataManipulatorContext::class);
        $dataManipulatorContextProphecy->withProcessedData(Argument::cetera())->willReturn($dataManipulatorContextProphecy->reveal());
        $dataManipulatorContextProphecy->getDecodedData()->willReturn(null);
        $dataManipulatorContextProphecy->setProcessedData(Argument::cetera())->willReturn($dataManipulatorContextProphecy->reveal());
        $dataManipulatorContextProphecy->getProcessedData()->willReturn(null);

        $dataProcessing1Prophecy = $this->prophesize(DataProcessingInterface::class);
        $dataProcessing1Prophecy->getIdentifier()->shouldBeCalled()->willReturn('id-1');
        $dataProcessing1Prophecy->getProcessor()->shouldBeCalled()->willReturn(function () { return; });

        $dataProcessing2Prophecy = $this->prophesize(DataProcessingInterface::class);
        $dataProcessing2Prophecy->getIdentifier()->shouldBeCalled()->willReturn('id-2');
        $dataProcessing2Prophecy->getProcessor()->shouldBeCalled()->willReturn(function () { return; });

        $dataProcessing3Prophecy = $this->prophesize(InvalidDataProcessing::class);
        $dataProcessing3Prophecy->getIdentifier()->shouldNotBeCalled()->willReturn('id-3');
        $dataProcessing3Prophecy->getProcessor()->shouldNotBeCalled()->willReturn(function () { return; });

        $dataManipulator = new DataManipulator();
        $dataManipulator->process(
            $dataManipulatorContextProphecy->reveal(),
            $dataProcessing1Prophecy->reveal(),
            $dataProcessing2Prophecy->reveal(),
            $dataProcessing3Prophecy->reveal()
        );
    }

    public function testProcessThrowsExceptionIfProcessingIdentifiersAreEqual(): void
    {
        $dataManipulatorContextProphecy = $this->prophesize(DataManipulatorContext::class);
        $dataManipulatorContextProphecy->withProcessedData(Argument::cetera())->willReturn($dataManipulatorContextProphecy->reveal());
        $dataManipulatorContextProphecy->getDecodedData()->willReturn(null);
        $dataManipulatorContextProphecy->setProcessedData(Argument::cetera())->willReturn($dataManipulatorContextProphecy->reveal());

        $dataProcessing1Prophecy = $this->prophesize(DataProcessingInterface::class);
        $dataProcessing1Prophecy->getIdentifier()->willReturn('id-1');
        $dataProcessing1Prophecy->getProcessor()->willReturn(function () { return; });

        $dataProcessing2Prophecy = $this->prophesize(DataProcessingInterface::class);
        $dataProcessing2Prophecy->getIdentifier()->willReturn('id-1');
        $dataProcessing2Prophecy->getProcessor()->willReturn(function () { return; });

        $this->expectException(AmbiguousDataProcessingException::class);
        $this->expectExceptionCode(1620916028);

        $dataManipulator = new DataManipulator();
        $dataManipulator->process(
            $dataManipulatorContextProphecy->reveal(),
            $dataProcessing1Prophecy->reveal(),
            $dataProcessing2Prophecy->reveal()
        );
    }

    public function testProcessReturnsManipilatedData(): void
    {
        $fakerProphecy = $this->prophesize(Faker::class);
        $dataManipulatorContext = new DataManipulatorContext($fakerProphecy->reveal(), '{"foo":"baz"}', ['foo' => 'boar'], []);

        $dataProcessing1 = new DataProcessing(function (DataManipulatorContext $context) {
            $context->setProcessedData(['foo' => 'baz']);
        }, 'id-1');

        $dataProcessing2 = new DataProcessing(function (DataManipulatorContext $context) {
        }, 'id-2');

        $dataProcessing3 = new DataProcessing(function (DataManipulatorContext $context) {
            $context->setProcessedData(['foo' => 'zab']);
        }, 'id-3');

        $dataManipulator = new DataManipulator();
        $this->assertEquals(
            ['foo' => 'zab'],
            $dataManipulator->process(
                $dataManipulatorContext,
                $dataProcessing1,
                $dataProcessing2,
                $dataProcessing3
            )
        );
    }
}
