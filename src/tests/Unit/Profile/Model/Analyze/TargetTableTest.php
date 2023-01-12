<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Profile\Model\Analyze;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\MissingExcludedColumnException;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\MissingTargetColumnException;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\TargetColumn;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\TargetTable;

class TargetTableTest extends TestCase
{
    use ProphecyTrait;

    public function testHasColumnReturnsTrue(): void
    {
        $column = new TargetColumn('foo-2');
        $table = new TargetTable('foo-1');
        $table->addColumn($column);

        $this->assertTrue($table->hasColumn('foo-2'));
    }

    public function testHasColumnReturnsFalse(): void
    {
        $column = new TargetColumn('foo-2');
        $table = new TargetTable('foo-1');
        $table->addColumn($column);

        $this->assertFalse($table->hasColumn('foo-42'));
    }

    public function testGetColumnReturnsColumn(): void
    {
        $column = new TargetColumn('foo-2');
        $table = new TargetTable('foo-1');
        $table->addColumn($column);

        $this->assertEquals($column, $table->getColumn('foo-2'));
    }

    public function testAddColumnsCreateAndAddColumns(): void
    {
        $column1 = TargetColumn::create('foo-2');
        $column2 = TargetColumn::create('foo-3');
        $table = new TargetTable('foo-1');
        $table->addColumns(['foo-2', 'foo-3']);

        $this->assertEquals(['foo-2' => $column1, 'foo-3' => $column2], $table->getColumns());
    }

    public function testGetColumnThrowsException(): void
    {
        $this->expectException(MissingTargetColumnException::class);
        $this->expectExceptionCode(1621654997);

        $column = new TargetColumn('foo-2');
        $table = new TargetTable('foo-1');
        $table->addColumn($column);
        $table->getColumn('foo-42');
    }

    public function testIsColumnExcludedReturnsTrue(): void
    {
        $column = new TargetColumn('foo-2');
        $table = new TargetTable('foo-1');
        $table->excludeColumn($column);

        $this->assertTrue($table->isColumnExcluded('foo-2'));
    }

    public function testIsColumnExcludedReturnsFalse(): void
    {
        $column = new TargetColumn('foo-2');
        $table = new TargetTable('foo-1');
        $table->excludeColumn($column);

        $this->assertFalse($table->isColumnExcluded('foo-42'));
    }

    public function testGetExcludedColumnReturnsColumn(): void
    {
        $column = new TargetColumn('foo-2');
        $table = new TargetTable('foo-1');
        $table->excludeColumn($column);

        $this->assertEquals($column, $table->getExcludedColumn('foo-2'));
    }

    public function testGetExcludedColumnThrowsException(): void
    {
        $this->expectException(MissingExcludedColumnException::class);
        $this->expectExceptionCode(1621654998);

        $column = new TargetColumn('foo-2');
        $table = new TargetTable('foo-1');
        $table->excludeColumn($column);
        $table->getExcludedColumn('foo-42');
    }

    public function testRemoveExcludedTargetColumnRemovesExcludedTargetColumn(): void
    {
        $this->expectException(MissingExcludedColumnException::class);
        $this->expectExceptionCode(1621654998);

        $column1 = new TargetColumn('foo-2');
        $column2 = new TargetColumn('foo-3');
        $table = new TargetTable('foo-1');
        $table->excludeColumn($column1);
        $table->excludeColumn($column2);
        $table->removeExcludedColumn('foo-2');
        $table->getExcludedColumn('foo-2');
    }

    public function testGetExcludedColumnsReturnsExcludedTargetColumns(): void
    {
        $column1 = new TargetColumn('foo-2');
        $column2 = new TargetColumn('foo-3');
        $table = new TargetTable('foo-1');
        $table->excludeColumn($column1);
        $table->excludeColumn($column2);

        $this->assertEquals(['foo-2' => $column1, 'foo-3' => $column2], $table->getExcludedColumns());
    }

    public function testExcludeColumnsCreateAndAddColumns(): void
    {
        $column1 = TargetColumn::create('foo-2');
        $column2 = TargetColumn::create('foo-3');
        $table = new TargetTable('foo-1');
        $table->excludeColumns(['foo-2', 'foo-3']);

        $this->assertEquals(['foo-2' => $column1, 'foo-3' => $column2], $table->getExcludedColumns());
    }

    public function testIsTargetColumnTypeExcludedReturnsTrue(): void
    {
        $table = new TargetTable('foo-1');
        $table->excludeColumnType('string');

        $this->assertTrue($table->isTargetColumnTypeExcluded('string'));
    }

    public function testIsTargetColumnTypeExcludedReturnsFalse(): void
    {
        $table = new TargetTable('foo-1');
        $table->excludeColumnType('string');

        $this->assertFalse($table->isTargetColumnTypeExcluded('strong'));
    }

    public function testRemoveExcludedTargetColumnTypeRemovesExcludeTargetColumnType(): void
    {
        $table = new TargetTable('foo-1');
        $table->excludeColumnType('string');
        $table->excludeColumnType('int');
        $table->removeExcludedColumnType('string');

        $this->assertFalse($table->isTargetColumnTypeExcluded('string'));
    }
}
