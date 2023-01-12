<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Profile\Model\Analyze;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Waldhacker\Pseudify\Core\Processor\Encoder\Base64Encoder;
use Waldhacker\Pseudify\Core\Processor\Encoder\GzCompressEncoder;
use Waldhacker\Pseudify\Core\Processor\Encoder\GzDeflateEncoder;
use Waldhacker\Pseudify\Core\Processor\Encoder\GzEncodeEncoder;
use Waldhacker\Pseudify\Core\Processor\Encoder\HexEncoder;
use Waldhacker\Pseudify\Core\Processor\Encoder\ScalarEncoder;
use Waldhacker\Pseudify\Core\Processor\Encoder\ZlibEncodeEncoder;
use Waldhacker\Pseudify\Core\Processor\Processing\DataProcessingInterface;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\MissingDataProcessingException;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\TargetColumn;

class TargetColumnTest extends TestCase
{
    use ProphecyTrait;

    public function testCreateCreatesColumn(): void
    {
        $column = new TargetColumn('foo');
        $column->setEncoder(new ScalarEncoder([]));
        $this->assertEquals($column, TargetColumn::create('foo'));
    }

    public function testCreateCreatesColumnWithBase64Encoder(): void
    {
        $column = new TargetColumn('foo');
        $column->setEncoder(new Base64Encoder([]));
        $this->assertEquals($column, TargetColumn::create('foo', TargetColumn::DATA_TYPE_BASE64));
    }

    public function testCreateCreatesColumnWithGzCompressEncoder(): void
    {
        $column = new TargetColumn('foo');
        $column->setEncoder(new GzCompressEncoder([]));
        $this->assertEquals($column, TargetColumn::create('foo', TargetColumn::DATA_TYPE_GZCOMPRESS));
    }

    public function testCreateCreatesColumnWithGzDeflateEncoder(): void
    {
        $column = new TargetColumn('foo');
        $column->setEncoder(new GzDeflateEncoder([]));
        $this->assertEquals($column, TargetColumn::create('foo', TargetColumn::DATA_TYPE_GZDEFLATE));
    }

    public function testCreateCreatesColumnWithGzEncodeEncoder(): void
    {
        $column = new TargetColumn('foo');
        $column->setEncoder(new GzEncodeEncoder([]));
        $this->assertEquals($column, TargetColumn::create('foo', TargetColumn::DATA_TYPE_GZENCODE));
    }

    public function testCreateCreatesColumnWithHexEncoder(): void
    {
        $column = new TargetColumn('foo');
        $column->setEncoder(new HexEncoder([]));
        $this->assertEquals($column, TargetColumn::create('foo', TargetColumn::DATA_TYPE_HEX));
    }

    public function testCreateCreatesColumnWithScalarEncoder(): void
    {
        $column = new TargetColumn('foo');
        $column->setEncoder(new ScalarEncoder([]));
        $this->assertEquals($column, TargetColumn::create('foo', TargetColumn::DATA_TYPE_SCALAR));
    }

    public function testCreateCreatesColumnWithZlibEncodeEncoder(): void
    {
        $column = new TargetColumn('foo');
        $column->setEncoder(new ZlibEncodeEncoder([]));
        $this->assertEquals($column, TargetColumn::create('foo', TargetColumn::DATA_TYPE_ZLIBENCODE));
    }

    public function testHasDataProcessingReturnsTrue(): void
    {
        $dataProcessing1 = $this->prophesize(DataProcessingInterface::class);
        $dataProcessing1->getIdentifier()->willReturn('id-1');
        $column = new TargetColumn('foo');
        $column->addDataProcessing($dataProcessing1->reveal());
        $this->assertTrue($column->hasDataProcessing('id-1'));
    }

    public function testHasDataProcessingReturnsFalse(): void
    {
        $dataProcessing1 = $this->prophesize(DataProcessingInterface::class);
        $dataProcessing1->getIdentifier()->willReturn('id-1');
        $column = new TargetColumn('foo');
        $column->addDataProcessing($dataProcessing1->reveal());
        $this->assertFalse($column->hasDataProcessing('id-2'));
    }

    public function testGetDataProcessingReturnsDataProcessing(): void
    {
        $dataProcessing1 = $this->prophesize(DataProcessingInterface::class);
        $dataProcessing1->getIdentifier()->willReturn('id-1');
        $column = new TargetColumn('foo');
        $column->addDataProcessing($dataProcessing1->reveal());
        $this->assertEquals($dataProcessing1->reveal(), $column->getDataProcessing('id-1'));
    }

    public function testGetDataProcessingThrowsException(): void
    {
        $this->expectException(MissingDataProcessingException::class);
        $this->expectExceptionCode(1621686502);

        $dataProcessing1 = $this->prophesize(DataProcessingInterface::class);
        $dataProcessing1->getIdentifier()->willReturn('id-1');
        $column = new TargetColumn('foo');
        $column->addDataProcessing($dataProcessing1->reveal());
        $column->getDataProcessing('id-42');
    }

    public function testRemoveDataProcessingRemovesDataProcessing(): void
    {
        $this->expectException(MissingDataProcessingException::class);
        $this->expectExceptionCode(1621686502);

        $dataProcessing1 = $this->prophesize(DataProcessingInterface::class);
        $dataProcessing1->getIdentifier()->willReturn('id-1');
        $dataProcessing2 = $this->prophesize(DataProcessingInterface::class);
        $dataProcessing2->getIdentifier()->willReturn('id-2');

        $column = new TargetColumn('foo');
        $column->addDataProcessing($dataProcessing1->reveal());
        $column->addDataProcessing($dataProcessing2->reveal());
        $column->removeDataProcessing('id-1');
        $column->getDataProcessing('id-1');
    }

    public function testGetDataProcessingIdentifiersReturnsDataProcessingIdentifiers(): void
    {
        $dataProcessing1 = $this->prophesize(DataProcessingInterface::class);
        $dataProcessing1->getIdentifier()->willReturn('id-1');
        $dataProcessing2 = $this->prophesize(DataProcessingInterface::class);
        $dataProcessing2->getIdentifier()->willReturn('id-2');

        $column = new TargetColumn('foo');
        $column->addDataProcessing($dataProcessing1->reveal());
        $column->addDataProcessing($dataProcessing2->reveal());

        $this->assertEquals(['id-1', 'id-2'], $column->getDataProcessingIdentifiers());
    }
}
