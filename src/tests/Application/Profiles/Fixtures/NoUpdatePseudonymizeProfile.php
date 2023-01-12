<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Application\Profiles\Fixtures;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\Column as DoctrineColumn;
use Waldhacker\Pseudify\Core\Processor\Processing\Pseudonymize\DataManipulatorPreset;
use Waldhacker\Pseudify\Core\Profile\Model\Pseudonymize\Column;
use Waldhacker\Pseudify\Core\Profile\Model\Pseudonymize\Table;
use Waldhacker\Pseudify\Core\Profile\Model\Pseudonymize\TableDefinition;
use Waldhacker\Pseudify\Core\Profile\Pseudonymize\ProfileInterface;

class NoUpdatePseudonymizeProfile implements ProfileInterface
{
    public function getIdentifier(): string
    {
        return 'noupdate';
    }

    public function getTableDefinition(): TableDefinition
    {
        $tableDefinition = new TableDefinition('No update');
        $tableDefinition
            ->addTable(Table::create('wh_user')
                ->addColumn(Column::create('username')
                    ->addDataProcessing(DataManipulatorPreset::scalarData(fakerFormatter: 'userName'))
                    ->onBeforeUpdateData(function (QueryBuilder $queryBuilder, Table $table, Column $column, DoctrineColumn $columnInfo, $originalData, $processedData, array $databaseRow) {
                        $queryBuilder->where($queryBuilder->expr()->eq(1, 0));
                    })
                )
            );

        return $tableDefinition;
    }
}
