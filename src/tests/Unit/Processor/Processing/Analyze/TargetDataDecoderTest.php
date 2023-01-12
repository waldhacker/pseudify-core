<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Processor\Processing\Analyze;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Waldhacker\Pseudify\Core\Processor\Processing\AmbiguousDataProcessingException;
use Waldhacker\Pseudify\Core\Processor\Processing\Analyze\TargetDataDecoder;
use Waldhacker\Pseudify\Core\Processor\Processing\Analyze\TargetDataDecoderContext;
use Waldhacker\Pseudify\Core\Processor\Processing\DataProcessing;
use Waldhacker\Pseudify\Core\Processor\Processing\DataProcessingInterface;
use Waldhacker\Pseudify\Core\Tests\Unit\Processor\Processing\Fixtures\InvalidDataProcessing;

class TargetDataDecoderTest extends TestCase
{
    use ProphecyTrait;

    public function testProcessProccessValidProcessings(): void
    {
        $targetDataDecoderContextProphecy = $this->prophesize(TargetDataDecoderContext::class);
        $targetDataDecoderContextProphecy->withDecodedData(Argument::cetera())->willReturn($targetDataDecoderContextProphecy->reveal());
        $targetDataDecoderContextProphecy->getDecodedData()->willReturn([]);

        $dataProcessing1Prophecy = $this->prophesize(DataProcessingInterface::class);
        $dataProcessing1Prophecy->getIdentifier()->shouldBeCalled()->willReturn('id-1');
        $dataProcessing1Prophecy->getProcessor()->shouldBeCalled()->willReturn(function () { return; });

        $dataProcessing2Prophecy = $this->prophesize(DataProcessingInterface::class);
        $dataProcessing2Prophecy->getIdentifier()->shouldBeCalled()->willReturn('id-2');
        $dataProcessing2Prophecy->getProcessor()->shouldBeCalled()->willReturn(function () { return; });

        $dataProcessing3Prophecy = $this->prophesize(InvalidDataProcessing::class);
        $dataProcessing3Prophecy->getIdentifier()->shouldNotBeCalled()->willReturn('id-3');
        $dataProcessing3Prophecy->getProcessor()->shouldNotBeCalled()->willReturn(function () { return; });

        $targetDataDecoder = new TargetDataDecoder();
        $targetDataDecoder->process(
            $targetDataDecoderContextProphecy->reveal(),
            $dataProcessing1Prophecy->reveal(),
            $dataProcessing2Prophecy->reveal(),
            $dataProcessing3Prophecy->reveal()
        );
    }

    public function testProcessThrowsExceptionIfProcessingIdentifiersAreEqual(): void
    {
        $targetDataDecoderContextProphecy = $this->prophesize(TargetDataDecoderContext::class);
        $targetDataDecoderContextProphecy->withDecodedData(Argument::cetera())->willReturn($targetDataDecoderContextProphecy->reveal());
        $targetDataDecoderContextProphecy->getDecodedData()->willReturn([]);

        $dataProcessing1Prophecy = $this->prophesize(DataProcessingInterface::class);
        $dataProcessing1Prophecy->getIdentifier()->willReturn('id-1');
        $dataProcessing1Prophecy->getProcessor()->willReturn(function () { return; });

        $dataProcessing2Prophecy = $this->prophesize(DataProcessingInterface::class);
        $dataProcessing2Prophecy->getIdentifier()->willReturn('id-1');
        $dataProcessing2Prophecy->getProcessor()->willReturn(function () { return; });

        $this->expectException(AmbiguousDataProcessingException::class);
        $this->expectExceptionCode(1621683731);

        $targetDataDecoder = new TargetDataDecoder();
        $targetDataDecoder->process(
            $targetDataDecoderContextProphecy->reveal(),
            $dataProcessing1Prophecy->reveal(),
            $dataProcessing2Prophecy->reveal()
        );
    }

    public function testProcessReturnsDecodedData(): void
    {
        $targetDataDecoderContext = new TargetDataDecoderContext(null, null, []);

        $dataProcessing1 = new DataProcessing(function (TargetDataDecoderContext $context) {
            $context->setDecodedData(['bar']);
        }, 'id-1');

        $dataProcessing2 = new DataProcessing(function (TargetDataDecoderContext $context) {
            $context->setDecodedData('foo');
        }, 'id-2');

        $targetDataDecoder = new TargetDataDecoder();
        $this->assertEquals(
            ['foo'],
            $targetDataDecoder->process(
                $targetDataDecoderContext,
                $dataProcessing1,
                $dataProcessing2
            )
        );
    }
}
