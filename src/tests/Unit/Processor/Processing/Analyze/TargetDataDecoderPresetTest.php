<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Processor\Processing\Analyze;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Waldhacker\Pseudify\Core\Processor\Processing\Analyze\TargetDataDecoderContext;
use Waldhacker\Pseudify\Core\Processor\Processing\Analyze\TargetDataDecoderPreset;

class TargetDataDecoderPresetTest extends TestCase
{
    use ProphecyTrait;

    public function testNormalizedJsonStringAddsDecodedData(): void
    {
        $dataProcessing = TargetDataDecoderPreset::normalizedJsonString();

        $targetDataDecoderContext = new TargetDataDecoderContext('{"name": "Ren\u00e9"}', null, []);
        $processor = $dataProcessing->getProcessor();
        $processor($targetDataDecoderContext);

        $this->assertEquals(
            '{"name":"René"}',
            $targetDataDecoderContext->getDecodedData()
        );
    }

    public function testNormalizedJsonStringDoNotAddsDecodedDataIfSourceDataIsNoString(): void
    {
        $dataProcessing = TargetDataDecoderPreset::normalizedJsonString('p-1');

        $targetDataDecoderContext = new TargetDataDecoderContext(1, null, []);
        $processor = $dataProcessing->getProcessor();
        $processor($targetDataDecoderContext);

        $this->assertEquals(
            null,
            $targetDataDecoderContext->getDecodedData()
        );
    }

    public function testNormalizedJsonStringDoNotAddsDecodedDataIfSourceDataIsInvalidJson(): void
    {
        $dataProcessing = TargetDataDecoderPreset::normalizedJsonString('p-1');

        $targetDataDecoderContext = new TargetDataDecoderContext('{x', null, []);
        $processor = $dataProcessing->getProcessor();
        $processor($targetDataDecoderContext);

        $this->assertEquals(
            null,
            $targetDataDecoderContext->getDecodedData()
        );
    }

    public function testNormalizedJsonStringDoNotAddsDecodedDataIfSourceDataContainsInvalidUtf8Characters(): void
    {
        $dataProcessing = TargetDataDecoderPreset::normalizedJsonString('p-1');

        $targetDataDecoderContext = new TargetDataDecoderContext('{"name": "Ren\uE0FG"}', null, []);
        $processor = $dataProcessing->getProcessor();
        $processor($targetDataDecoderContext);

        $this->assertEquals(
            null,
            $targetDataDecoderContext->getDecodedData()
        );
    }
}
