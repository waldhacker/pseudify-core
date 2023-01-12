<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Profile\Analyze;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\StringType;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Waldhacker\Pseudify\Core\Database\Schema;
use Waldhacker\Pseudify\Core\Profile\Analyze\TableDefinitionAutoConfiguration;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\SourceColumn;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\SourceTable;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\TableDefinition;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\TargetColumn;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\TargetTable;

class TableDefinitionAutoConfigurationTest extends TestCase
{
    use ProphecyTrait;

    public function configureDataProvider(): array
    {
        return [
            'autofillNotConfiguredTargetTables' => [
                'tableDefinition' => TableDefinition::create('TEST'),
                'expectedTableDefinition' => TableDefinition::create('TEST')
                    ->addTargetTable(TargetTable::create('table_1')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    )
                    ->addTargetTable(TargetTable::create('table_2')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    )
                    ->addTargetTable(TargetTable::create('table_3')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    )
                    ->addTargetTable(TargetTable::create('table_4')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    )
                    ->addTargetTable(TargetTable::create('table_5')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    ),
            ],
            'removeNotExistingSourceTables' => [
                'tableDefinition' => TableDefinition::create('TEST')
                    ->addSourceTable(SourceTable::create('non_existing_table_1')),
                'expectedTableDefinition' => TableDefinition::create('TEST')
                    ->addTargetTable(TargetTable::create('table_1')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    )
                    ->addTargetTable(TargetTable::create('table_2')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    )
                    ->addTargetTable(TargetTable::create('table_3')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    )
                    ->addTargetTable(TargetTable::create('table_4')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    )
                    ->addTargetTable(TargetTable::create('table_5')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    ),
            ],
            'removeNotExistingTargetTables' => [
                'tableDefinition' => TableDefinition::create('TEST')
                    ->addTargetTable(TargetTable::create('non_existing_table_1')),
                'expectedTableDefinition' => TableDefinition::create('TEST')
                    ->addTargetTable(TargetTable::create('table_1')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    )
                    ->addTargetTable(TargetTable::create('table_2')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    )
                    ->addTargetTable(TargetTable::create('table_3')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    )
                    ->addTargetTable(TargetTable::create('table_4')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    )
                    ->addTargetTable(TargetTable::create('table_5')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    ),
            ],
            'removeNotExistingSourceColumns' => [
                'tableDefinition' => TableDefinition::create('TEST')
                    ->addSourceTable(SourceTable::create('table_1')
                        ->addColumn(SourceColumn::create('column_1'))
                        ->addColumn(SourceColumn::create('non_existing_column_1'))
                    ),
                'expectedTableDefinition' => TableDefinition::create('TEST')
                    ->addSourceTable(SourceTable::create('table_1')
                        ->addColumn(SourceColumn::create('column_1'))
                    )
                    ->addTargetTable(TargetTable::create('table_1')
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    )
                    ->addTargetTable(TargetTable::create('table_2')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    )
                    ->addTargetTable(TargetTable::create('table_3')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    )
                    ->addTargetTable(TargetTable::create('table_4')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    )
                    ->addTargetTable(TargetTable::create('table_5')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    ),
            ],
            'removeNotExistingTargetColumns' => [
                'tableDefinition' => TableDefinition::create('TEST')
                    ->addTargetTable(TargetTable::create('table_1')
                        ->addColumn(TargetColumn::create('non_existing_column_1'))
                    ),
                'expectedTableDefinition' => TableDefinition::create('TEST')
                    ->addTargetTable(TargetTable::create('table_1')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    )
                    ->addTargetTable(TargetTable::create('table_2')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    )
                    ->addTargetTable(TargetTable::create('table_3')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    )
                    ->addTargetTable(TargetTable::create('table_4')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    )
                    ->addTargetTable(TargetTable::create('table_5')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    ),
            ],
            'removeExcludedTargetTables' => [
                'tableDefinition' => TableDefinition::create('TEST')
                    ->excludeTargetTable(TargetTable::create('table_[1-3]'))
                    ->excludeTargetTable(TargetTable::create('table_4')),
                'expectedTableDefinition' => TableDefinition::create('TEST')
                    ->excludeTargetTable(TargetTable::create('table_[1-3]'))
                    ->excludeTargetTable(TargetTable::create('table_4'))
                    ->addTargetTable(TargetTable::create('table_5')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    ),
            ],
            'removeTargetColumnsBySourceColumns' => [
                'tableDefinition' => TableDefinition::create('TEST')
                    ->excludeTargetTable(TargetTable::create('table_1'))
                    ->addSourceTable(SourceTable::create('table_1')
                        ->addColumn(SourceColumn::create('column_1'))
                        ->addColumn(SourceColumn::create('column_3'))
                    )
                    ->addSourceTable(SourceTable::create('table_2')
                        ->addColumn(SourceColumn::create('column_1'))
                        ->addColumn(SourceColumn::create('column_3'))
                    ),
                'expectedTableDefinition' => TableDefinition::create('TEST')
                    ->excludeTargetTable(TargetTable::create('table_1'))
                    ->addSourceTable(SourceTable::create('table_1')
                        ->addColumn(SourceColumn::create('column_1'))
                        ->addColumn(SourceColumn::create('column_3'))
                    )
                    ->addSourceTable(SourceTable::create('table_2')
                        ->addColumn(SourceColumn::create('column_1'))
                        ->addColumn(SourceColumn::create('column_3'))
                    )
                    ->addTargetTable(TargetTable::create('table_2')
                        ->addColumn(TargetColumn::create('column_2'))
                    )
                    ->addTargetTable(TargetTable::create('table_3')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    )
                    ->addTargetTable(TargetTable::create('table_4')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    )
                    ->addTargetTable(TargetTable::create('table_5')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    ),
            ],
            'removeSourceTablesWithEmptyColumns' => [
                'tableDefinition' => TableDefinition::create('TEST')
                    ->addSourceTable(SourceTable::create('table_1')),
                'expectedTableDefinition' => TableDefinition::create('TEST')
                    ->addTargetTable(TargetTable::create('table_1')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    )
                    ->addTargetTable(TargetTable::create('table_2')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    )
                    ->addTargetTable(TargetTable::create('table_3')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    )
                    ->addTargetTable(TargetTable::create('table_4')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    )
                    ->addTargetTable(TargetTable::create('table_5')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    ),
            ],
            'removeTargetTablesWithEmptyColumns' => [
                'tableDefinition' => TableDefinition::create('TEST')
                    ->addTargetTable(TargetTable::create('table_5')
                        ->excludeColumnTypes(['string'])
                    ),
                'expectedTableDefinition' => TableDefinition::create('TEST')
                    ->addTargetTable(TargetTable::create('table_1')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    )
                    ->addTargetTable(TargetTable::create('table_2')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    )
                    ->addTargetTable(TargetTable::create('table_3')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    )
                    ->addTargetTable(TargetTable::create('table_4')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    ),
            ],
            'removeExcludedTargetColumnsByType' => [
                'tableDefinition' => TableDefinition::create('TEST')
                    ->addTargetTable(TargetTable::create('table_1')
                        ->excludeColumnTypes(['integer', 'datetime'])
                    ),
                'expectedTableDefinition' => TableDefinition::create('TEST')
                    ->addTargetTable(TargetTable::create('table_1')
                        ->excludeColumnTypes(['integer', 'datetime'])
                        ->addColumn(TargetColumn::create('column_1'))
                    )
                    ->addTargetTable(TargetTable::create('table_2')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    )
                    ->addTargetTable(TargetTable::create('table_3')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    )
                    ->addTargetTable(TargetTable::create('table_4')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    )
                    ->addTargetTable(TargetTable::create('table_5')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    ),
            ],
            'removeExcludedTargetColumnsByIdentifier' => [
                'tableDefinition' => TableDefinition::create('TEST')
                    ->addTargetTable(TargetTable::create('table_3')
                        ->excludeColumn(TargetColumn::create('column_1'))
                        ->excludeColumn(TargetColumn::create('column_3'))
                    ),
                'expectedTableDefinition' => TableDefinition::create('TEST')
                    ->addTargetTable(TargetTable::create('table_1')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    )
                    ->addTargetTable(TargetTable::create('table_2')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    )
                    ->addTargetTable(TargetTable::create('table_3')
                        ->excludeColumn(TargetColumn::create('column_1'))
                        ->excludeColumn(TargetColumn::create('column_3'))
                        ->addColumn(TargetColumn::create('column_2'))
                    )
                    ->addTargetTable(TargetTable::create('table_4')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    )
                    ->addTargetTable(TargetTable::create('table_5')
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    ),
            ],
            'removeGlobalExcludedTargetColumnTypes' => [
                'tableDefinition' => TableDefinition::create('TEST')
                    ->excludeTargetColumnTypes(['integer', 'datetime']),
                'expectedTableDefinition' => TableDefinition::create('TEST')
                    ->excludeTargetColumnTypes(['integer', 'datetime'])
                    ->addTargetTable(TargetTable::create('table_1')
                        ->excludeColumnTypes(['integer', 'datetime'])
                        ->addColumn(TargetColumn::create('column_1'))
                    )
                    ->addTargetTable(TargetTable::create('table_2')
                        ->excludeColumnTypes(['integer', 'datetime'])
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    )
                    ->addTargetTable(TargetTable::create('table_3')
                        ->excludeColumnTypes(['integer', 'datetime'])
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_3'))
                    )
                    ->addTargetTable(TargetTable::create('table_4')
                        ->excludeColumnTypes(['integer', 'datetime'])
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_3'))
                    )
                    ->addTargetTable(TargetTable::create('table_5')
                        ->excludeColumnTypes(['integer', 'datetime'])
                        ->addColumn(TargetColumn::create('column_1'))
                        ->addColumn(TargetColumn::create('column_2'))
                        ->addColumn(TargetColumn::create('column_3'))
                    ),
            ],
        ];
    }

    /**
     * @dataProvider configureDataProvider
     */
    public function testConfigureReturnsAutoconfiguredTableDefinition(TableDefinition $tableDefinition, TableDefinition $expectedTableDefinition): void
    {
        $schemaProphecy = $this->prophesize(Schema::class);
        $schemaProphecy->listTableNames()->willReturn(['table_1', 'table_2', 'table_3', 'table_4', 'table_5']);
        $schemaProphecy->listTableColumns('table_1')->willReturn([
            ['name' => 'column_1', 'column' => new Column('column_1', new StringType())],
            ['name' => 'column_2', 'column' => new Column('column_2', new IntegerType())],
            ['name' => 'column_3', 'column' => new Column('column_3', new DateTimeType())],
        ]);
        $schemaProphecy->listTableColumns('table_2')->willReturn([
            ['name' => 'column_1', 'column' => new Column('column_1', new StringType())],
            ['name' => 'column_2', 'column' => new Column('column_2', new StringType())],
            ['name' => 'column_3', 'column' => new Column('column_3', new StringType())],
        ]);
        $schemaProphecy->listTableColumns('table_3')->willReturn([
            ['name' => 'column_1', 'column' => new Column('column_1', new StringType())],
            ['name' => 'column_2', 'column' => new Column('column_2', new IntegerType())],
            ['name' => 'column_3', 'column' => new Column('column_3', new StringType())],
        ]);
        $schemaProphecy->listTableColumns('table_4')->willReturn([
            ['name' => 'column_1', 'column' => new Column('column_1', new StringType())],
            ['name' => 'column_2', 'column' => new Column('column_2', new IntegerType())],
            ['name' => 'column_3', 'column' => new Column('column_3', new StringType())],
        ]);
        $schemaProphecy->listTableColumns('table_5')->willReturn([
            ['name' => 'column_1', 'column' => new Column('column_1', new StringType())],
            ['name' => 'column_2', 'column' => new Column('column_2', new StringType())],
            ['name' => 'column_3', 'column' => new Column('column_3', new StringType())],
        ]);

        $tableDefinitionAutoConfiguration = new TableDefinitionAutoConfiguration($schemaProphecy->reveal());

        $this->assertEquals(
            $expectedTableDefinition,
            $tableDefinitionAutoConfiguration->configure($tableDefinition)
        );
    }
}
