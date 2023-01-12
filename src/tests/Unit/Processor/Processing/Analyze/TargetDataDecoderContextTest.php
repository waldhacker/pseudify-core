<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Processor\Processing\Analyze;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Waldhacker\Pseudify\Core\Processor\Processing\Analyze\TargetDataDecoderContext;

class TargetDataDecoderContextTest extends TestCase
{
    use ProphecyTrait;

    public function testGetRawDataReturnsRawData(): void
    {
        $rawData = ['bar'];

        $targetDataDecoderContext = new TargetDataDecoderContext($rawData, null, []);

        $this->assertEquals(
            $rawData,
            $targetDataDecoderContext->getRawData()
        );
    }

    public function testGetDatebaseRowReturnsDatebaseRow(): void
    {
        $databaseRow = ['uid' => 1, 'title' => 'foo'];

        $targetDataDecoderContext = new TargetDataDecoderContext(null, null, $databaseRow);

        $this->assertEquals(
            $databaseRow,
            $targetDataDecoderContext->getDatebaseRow()
        );
    }

    public function testGetDecodedDataReturnsDecodedData(): void
    {
        $decodedData = ['bar'];

        $targetDataDecoderContext = new TargetDataDecoderContext(null, $decodedData, []);

        $this->assertEquals(
            $decodedData,
            $targetDataDecoderContext->getDecodedData()
        );
    }

    public function testSetDecodedDataSetDecodedData(): void
    {
        $decodedData = ['bar'];

        $targetDataDecoderContext = new TargetDataDecoderContext(null, $decodedData, []);
        $targetDataDecoderContext->setDecodedData('bar');

        $this->assertEquals(
            'bar',
            $targetDataDecoderContext->getDecodedData()
        );
    }

    public function testWithDecodedDataReturnsNewInstanceWithProcessedData(): void
    {
        $processedData = 'foo';

        $targetDataDecoderContext = new TargetDataDecoderContext('foo', 'bar', ['uid' => 1]);

        $this->assertEquals(
            'bar',
            $targetDataDecoderContext->getDecodedData()
        );

        $targetDataDecoderContext = $targetDataDecoderContext->withDecodedData('custom');

        $this->assertEquals(
            'custom',
            $targetDataDecoderContext->getDecodedData()
        );

        $this->assertEquals(
            'foo',
            $targetDataDecoderContext->getRawData()
        );

        $this->assertEquals(
            ['uid' => 1],
            $targetDataDecoderContext->getDatebaseRow()
        );
    }
}
