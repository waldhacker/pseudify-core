<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Profile\Model\Analyze;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\MissingSourceColumnException;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\SourceColumn;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\SourceTable;

class SourceTableTest extends TestCase
{
    use ProphecyTrait;

    public function testHasColumnReturnsTrue(): void
    {
        $column = new SourceColumn('foo-2');
        $table = new SourceTable('foo-1');
        $table->addColumn($column);

        $this->assertTrue($table->hasColumn('foo-2'));
    }

    public function testHasColumnReturnsFalse(): void
    {
        $column = new SourceColumn('foo-2');
        $table = new SourceTable('foo-1');
        $table->addColumn($column);

        $this->assertFalse($table->hasColumn('foo-42'));
    }

    public function testGetColumnReturnsColumn(): void
    {
        $column = new SourceColumn('foo-2');
        $table = new SourceTable('foo-1');
        $table->addColumn($column);

        $this->assertEquals($column, $table->getColumn('foo-2'));
    }

    public function testAddColumnsCreateAndAddColumns(): void
    {
        $column1 = SourceColumn::create('foo-2');
        $column2 = SourceColumn::create('foo-3');
        $table = new SourceTable('foo-1');
        $table->addColumns(['foo-2', 'foo-3']);

        $this->assertEquals(['foo-2' => $column1, 'foo-3' => $column2], $table->getColumns());
    }

    public function testGetColumnThrowsException(): void
    {
        $this->expectException(MissingSourceColumnException::class);
        $this->expectExceptionCode(1621654996);

        $column = new SourceColumn('foo-2');
        $table = new SourceTable('foo-1');
        $table->addColumn($column);
        $table->getColumn('foo-42');
    }
}
