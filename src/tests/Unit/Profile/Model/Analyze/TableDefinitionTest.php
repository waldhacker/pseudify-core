<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Profile\Model\Analyze;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\MissingExcludedTableException;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\MissingSourceTableException;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\MissingTargetTableException;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\SourceTable;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\TableDefinition;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\TargetTable;

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

    public function testHasSourceTableReturnsTrue(): void
    {
        $tableDefinition = new TableDefinition('foo');
        $table = new SourceTable('t_1');
        $tableDefinition->addSourceTable($table);

        $this->assertTrue($tableDefinition->hasSourceTable('t_1'));
    }

    public function testHasSourceTableReturnsFalse(): void
    {
        $tableDefinition = new TableDefinition('foo');
        $table = new SourceTable('t_1');
        $tableDefinition->addSourceTable($table);

        $this->assertFalse($tableDefinition->hasSourceTable('t_2'));
    }

    public function testGetSourceTableReturnsColumn(): void
    {
        $tableDefinition = new TableDefinition('foo');
        $table = new SourceTable('t_1');
        $tableDefinition->addSourceTable($table);

        $this->assertEquals($table, $tableDefinition->getSourceTable('t_1'));
    }

    public function testGetSourceTableThrowsException(): void
    {
        $this->expectException(MissingSourceTableException::class);
        $this->expectExceptionCode(1621654993);

        $tableDefinition = new TableDefinition('foo');
        $table = new SourceTable('t_1');
        $tableDefinition->addSourceTable($table);

        $tableDefinition->getSourceTable('t_2');
    }

    public function testGetTargetTableThrowsException(): void
    {
        $this->expectException(MissingTargetTableException::class);
        $this->expectExceptionCode(1621654994);

        $tableDefinition = new TableDefinition('foo');
        $table = new TargetTable('t_1');
        $tableDefinition->addTargetTable($table);

        $tableDefinition->getTargetTable('t_2');
    }

    public function testIsTargetTableExcludedReturnsTrue(): void
    {
        $tableDefinition = new TableDefinition('foo');
        $table = new TargetTable('t_1');
        $tableDefinition->excludeTargetTable($table);

        $this->assertTrue($tableDefinition->isTargetTableExcluded('t_1'));
    }

    public function testIsTargetTableExcludedReturnsFalse(): void
    {
        $tableDefinition = new TableDefinition('foo');
        $table = new TargetTable('t_1');
        $tableDefinition->excludeTargetTable($table);

        $this->assertFalse($tableDefinition->isTargetTableExcluded('t_2'));
    }

    public function testGetExcludedTargetTableReturnsExcludeTargetTable(): void
    {
        $tableDefinition = new TableDefinition('foo');
        $table = new TargetTable('t_1');
        $tableDefinition->excludeTargetTable($table);

        $this->assertEquals($table, $tableDefinition->getExcludedTargetTable('t_1'));
    }

    public function testExcludeTargetTablesCreateAndAddTables(): void
    {
        $tableDefinition = new TableDefinition('foo');
        $table1 = new TargetTable('t_1');
        $table2 = new TargetTable('t_2');
        $tableDefinition->excludeTargetTables(['t_1', 't_2']);

        $this->assertEquals(['t_1' => $table1, 't_2' => $table2], $tableDefinition->getExcludedTargetTables());
    }

    public function testGetExcludedTargetTableThrowsException(): void
    {
        $this->expectException(MissingExcludedTableException::class);
        $this->expectExceptionCode(1621654995);

        $tableDefinition = new TableDefinition('foo');
        $table = new TargetTable('t_1');
        $tableDefinition->excludeTargetTable($table);

        $tableDefinition->getExcludedTargetTable('t_2');
    }

    public function testRemoveExcludedTargetTableRemovesExcludedTargetTable(): void
    {
        $this->expectException(MissingExcludedTableException::class);
        $this->expectExceptionCode(1621654995);

        $tableDefinition = new TableDefinition('foo');
        $table1 = new TargetTable('t_1');
        $table2 = new TargetTable('t_2');

        $tableDefinition->excludeTargetTable($table1);
        $tableDefinition->excludeTargetTable($table2);
        $tableDefinition->removeExcludedTargetTable('t_1');
        $tableDefinition->getExcludedTargetTable('t_1');
    }

    public function testGetExcludedTargetTablesReturnsExcludeTargetTables(): void
    {
        $tableDefinition = new TableDefinition('foo');
        $table1 = new TargetTable('t_1');
        $table2 = new TargetTable('t_2');

        $tableDefinition->excludeTargetTable($table1);
        $tableDefinition->excludeTargetTable($table2);

        $this->assertEquals(['t_1' => $table1, 't_2' => $table2], $tableDefinition->getExcludedTargetTables());
    }

    public function testIsTargetColumnTypeExcludedReturnsTrue(): void
    {
        $tableDefinition = new TableDefinition('foo');
        $tableDefinition->excludeTargetColumnType('string');

        $this->assertTrue($tableDefinition->isTargetColumnTypeExcluded('string'));
    }

    public function testIsTargetColumnTypeExcludedReturnsFalse(): void
    {
        $tableDefinition = new TableDefinition('foo');
        $tableDefinition->excludeTargetColumnType('string');

        $this->assertFalse($tableDefinition->isTargetColumnTypeExcluded('strong'));
    }

    public function testRemoveExcludedTargetColumnTypeRemovesExcludedTargetColumnType(): void
    {
        $tableDefinition = new TableDefinition('foo');
        $tableDefinition->excludeTargetColumnType('string');
        $tableDefinition->excludeTargetColumnType('int');
        $tableDefinition->removeExcludedTargetColumnType('string');

        $this->assertFalse($tableDefinition->isTargetColumnTypeExcluded('string'));
    }

    public function testHasSourceStringReturnsTrue(): void
    {
        $tableDefinition = new TableDefinition('foo');
        $tableDefinition->addSourceString('string');

        $this->assertTrue($tableDefinition->hasSourceString('string'));
    }

    public function testHasSourceStringReturnsFalse(): void
    {
        $tableDefinition = new TableDefinition('foo');
        $tableDefinition->addSourceString('string');

        $this->assertFalse($tableDefinition->hasSourceString('strong'));
    }

    public function testRemoveSourceStringRemovesSourceString(): void
    {
        $tableDefinition = new TableDefinition('foo');
        $tableDefinition->addSourceString('string');
        $tableDefinition->addSourceString('int');
        $tableDefinition->removeSourceString('string');

        $this->assertFalse($tableDefinition->hasSourceString('string'));
    }

    public function testGetTargetDataFrameCuttingLengthReturnsDefaultValue(): void
    {
        $tableDefinition = new TableDefinition('foo');

        $this->assertEquals(10, $tableDefinition->getTargetDataFrameCuttingLength());
    }

    public function testGetTargetDataFrameCuttingLengthReturnsCustomLengthValue(): void
    {
        $tableDefinition = new TableDefinition('foo');
        $tableDefinition->setTargetDataFrameCuttingLength(20);

        $this->assertEquals(20, $tableDefinition->getTargetDataFrameCuttingLength());
    }

    public function testsetTargetDataFrameCuttingLengthSetsCustomLength(): void
    {
        $tableDefinition = new TableDefinition('foo');
        $tableDefinition->setTargetDataFrameCuttingLength(20);

        $this->assertEquals(20, $tableDefinition->getTargetDataFrameCuttingLength());
    }
}
