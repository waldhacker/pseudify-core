<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Profile\Model\Pseudonymize;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Waldhacker\Pseudify\Core\Profile\Model\Pseudonymize\Column;
use Waldhacker\Pseudify\Core\Profile\Model\Pseudonymize\MissingColumnException;
use Waldhacker\Pseudify\Core\Profile\Model\Pseudonymize\Table;

class TableTest extends TestCase
{
    use ProphecyTrait;

    public function testHasColumnReturnsTrue(): void
    {
        $column = new Column('foo-2');
        $table = new Table('foo-1');
        $table->addColumn($column);

        $this->assertTrue($table->hasColumn('foo-2'));
    }

    public function testHasColumnReturnsFalse(): void
    {
        $column = new Column('foo-2');
        $table = new Table('foo-1');
        $table->addColumn($column);

        $this->assertFalse($table->hasColumn('foo-42'));
    }

    public function testGetColumnReturnsColumn(): void
    {
        $column = new Column('foo-2');
        $table = new Table('foo-1');
        $table->addColumn($column);

        $this->assertEquals($column, $table->getColumn('foo-2'));
    }

    public function testGetColumnThrowsException(): void
    {
        $this->expectException(MissingColumnException::class);
        $this->expectExceptionCode(1621654990);

        $column = new Column('foo-2');
        $table = new Table('foo-1');
        $table->addColumn($column);
        $table->getColumn('foo-42');
    }

    public function testRemoveColumnRemovesColumn(): void
    {
        $this->expectException(MissingColumnException::class);
        $this->expectExceptionCode(1621654990);

        $column1 = new Column('foo-2');
        $column2 = new Column('foo-3');
        $table = new Table('foo-1');
        $table->addColumn($column1);
        $table->addColumn($column2);
        $table->removeColumn('foo-2');
        $table->getColumn('foo-2');
    }

    public function testGetColumnIdentifiersReturnsColumnIdentifiers(): void
    {
        $column1 = new Column('foo-2');
        $column2 = new Column('foo-3');
        $table = new Table('foo-1');
        $table->addColumn($column1);
        $table->addColumn($column2);

        $this->assertEquals(['foo-2', 'foo-3'], $table->getColumnIdentifiers());
    }

    public function testAddColumnsCreateAndAddColumns(): void
    {
        $column1 = Column::create('foo-2');
        $column2 = Column::create('foo-3');
        $table = new Table('foo-1');
        $table->addColumns(['foo-2', 'foo-3']);

        $this->assertEquals(['foo-2' => $column1, 'foo-3' => $column2], $table->getColumns());
    }
}
