<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Processor\Processing\Analyze;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Waldhacker\Pseudify\Core\Processor\Processing\Analyze\SourceDataCollectorContext;

class SourceDataCollectorContextTest extends TestCase
{
    use ProphecyTrait;

    public function testGetRawDataReturnsRawData(): void
    {
        $rawData = ['bar'];

        $sourceDataCollectorContext = new SourceDataCollectorContext($rawData, null, []);

        $this->assertEquals(
            $rawData,
            $sourceDataCollectorContext->getRawData()
        );
    }

    public function testGetDecodedDataReturnsDecodedData(): void
    {
        $decodedData = ['bar'];

        $sourceDataCollectorContext = new SourceDataCollectorContext(null, $decodedData, []);

        $this->assertEquals(
            $decodedData,
            $sourceDataCollectorContext->getDecodedData()
        );
    }

    public function testGetDatebaseRowReturnsDatebaseRow(): void
    {
        $databaseRow = ['uid' => 1, 'title' => 'foo'];

        $sourceDataCollectorContext = new SourceDataCollectorContext(null, null, $databaseRow);

        $this->assertEquals(
            $databaseRow,
            $sourceDataCollectorContext->getDatebaseRow()
        );
    }

    public function testGetCollectedDataReturnsCollectedData(): void
    {
        $collectedData = 'foo';

        $sourceDataCollectorContext = new SourceDataCollectorContext(null, null, []);
        $sourceDataCollectorContext->addCollectedData($collectedData);

        $this->assertEquals(
            [md5(serialize($collectedData)) => $collectedData],
            $sourceDataCollectorContext->getCollectedData()
        );
    }

    public function testAddCollectedDataAddsCollectedData(): void
    {
        $collectedData1 = 'foo';
        $collectedData2 = 'bar';

        $sourceDataCollectorContext = new SourceDataCollectorContext(null, null, []);
        $sourceDataCollectorContext->addCollectedData($collectedData1);
        $sourceDataCollectorContext->addCollectedData($collectedData2);

        $this->assertEquals(
            [
                md5(serialize($collectedData1)) => $collectedData1,
                md5(serialize($collectedData2)) => $collectedData2,
            ],
            $sourceDataCollectorContext->getCollectedData()
        );
    }

    public function testRemoveCollectedDataRemovesCollectedData(): void
    {
        $collectedData1 = 'foo';
        $collectedData2 = 'bar';
        $collectedData3 = 'baz';

        $sourceDataCollectorContext = new SourceDataCollectorContext(null, null, []);
        $sourceDataCollectorContext->addCollectedData($collectedData1);
        $sourceDataCollectorContext->addCollectedData($collectedData2);
        $sourceDataCollectorContext->addCollectedData($collectedData3);
        $sourceDataCollectorContext->removeCollectedData(md5(serialize($collectedData2)));

        $this->assertEquals(
            [
                md5(serialize($collectedData1)) => $collectedData1,
                md5(serialize($collectedData3)) => $collectedData3,
            ],
            $sourceDataCollectorContext->getCollectedData()
        );
    }
}
