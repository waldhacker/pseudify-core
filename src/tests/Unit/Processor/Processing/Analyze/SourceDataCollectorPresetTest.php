<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Processor\Processing\Analyze;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Waldhacker\Pseudify\Core\Processor\Processing\Analyze\SourceDataCollectorContext;
use Waldhacker\Pseudify\Core\Processor\Processing\Analyze\SourceDataCollectorPreset;

class SourceDataCollectorPresetTest extends TestCase
{
    use ProphecyTrait;

    public function testScalarSourceDataCollectorAddsDecodedData(): void
    {
        $dataProcessing = SourceDataCollectorPreset::scalarData('p-1');

        $sourceDataCollectorContext = new SourceDataCollectorContext(null, 'foo', []);
        $processor = $dataProcessing->getProcessor();
        $processor($sourceDataCollectorContext);

        $this->assertEquals(
            [md5(serialize('foo')) => 'foo'],
            $sourceDataCollectorContext->getCollectedData()
        );
    }

    public function testScalarSourceDataCollectorDoNotAddsDecodedDataIfSourceStringIsToShort(): void
    {
        $dataProcessing = SourceDataCollectorPreset::scalarData('p-1', 4);

        $sourceDataCollectorContext = new SourceDataCollectorContext(null, 'foo', []);
        $processor = $dataProcessing->getProcessor();
        $processor($sourceDataCollectorContext);

        $this->assertEquals(
            [],
            $sourceDataCollectorContext->getCollectedData()
        );
    }

    public function testScalarSourceDataCollectorAddsDecodedDataIfSourceStringContainsInvalidCharacters(): void
    {
        $dataProcessing = SourceDataCollectorPreset::scalarData('p-1');

        $sourceDataCollectorContext = new SourceDataCollectorContext(null, "hello \xE9", []);
        $processor = $dataProcessing->getProcessor();
        $processor($sourceDataCollectorContext);

        $this->assertEquals(
            [md5(serialize("hello \xE9")) => "hello \xE9"],
            $sourceDataCollectorContext->getCollectedData()
        );
    }
}
