<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Profile\Model\Pseudonymize;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Waldhacker\Pseudify\Core\Profile\Model\Pseudonymize\MissingTableException;
use Waldhacker\Pseudify\Core\Profile\Model\Pseudonymize\Table;
use Waldhacker\Pseudify\Core\Profile\Model\Pseudonymize\TableDefinition;

class TableDefinitionTest extends TestCase
{
    use ProphecyTrait;

    public function testCreateCreatesTableDefinition(): void
    {
        $tableDefinition = new TableDefinition('foo');
        $this->assertEquals($tableDefinition, TableDefinition::create('foo'));
    }

    public function testGetIdentifierReturnsIdentifier(): void
    {
        $tableDefinition = new TableDefinition('foo');
        $this->assertEquals('foo', $tableDefinition->getIdentifier());
    }

    public function testHasTableReturnsTrue(): void
    {
        $tableDefinition = new TableDefinition('foo');
        $table = new Table('t_1');
        $tableDefinition->addTable($table);

        $this->assertTrue($tableDefinition->hasTable('t_1'));
    }

    public function testHasTableReturnsFalse(): void
    {
        $tableDefinition = new TableDefinition('foo');
        $table = new Table('t_1');
        $tableDefinition->addTable($table);

        $this->assertFalse($tableDefinition->hasTable('t_2'));
    }

    public function testGetTableReturnsColumn(): void
    {
        $tableDefinition = new TableDefinition('foo');
        $table = new Table('t_1');
        $tableDefinition->addTable($table);

        $this->assertEquals($table, $tableDefinition->getTable('t_1'));
    }

    public function testGetTableThrowsException(): void
    {
        $this->expectException(MissingTableException::class);
        $this->expectExceptionCode(1621654991);

        $tableDefinition = new TableDefinition('foo');
        $table = new Table('t_1');
        $tableDefinition->addTable($table);

        $tableDefinition->getTable('t_2');
    }

    public function testRemoveTableRemovesTable(): void
    {
        $this->expectException(MissingTableException::class);
        $this->expectExceptionCode(1621654991);

        $tableDefinition = new TableDefinition('foo');
        $table1 = new Table('t_1');
        $table2 = new Table('t_2');

        $tableDefinition->addTable($table1);
        $tableDefinition->addTable($table2);
        $tableDefinition->removeTable('t_1');
        $tableDefinition->getTable('t_1');
    }

    public function testGetTableIdentifiersReturnsTableIdentifiers(): void
    {
        $tableDefinition = new TableDefinition('foo');
        $table1 = new Table('t_1');
        $table2 = new Table('t_2');

        $tableDefinition->addTable($table1);
        $tableDefinition->addTable($table2);

        $this->assertEquals(['t_1', 't_2'], $tableDefinition->getTableIdentifiers());
    }
}
