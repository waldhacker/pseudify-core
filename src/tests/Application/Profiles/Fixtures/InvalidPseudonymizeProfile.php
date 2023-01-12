<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Application\Profiles\Fixtures;

use Waldhacker\Pseudify\Core\Processor\Processing\Pseudonymize\DataManipulatorPreset;
use Waldhacker\Pseudify\Core\Profile\Model\Pseudonymize\Column;
use Waldhacker\Pseudify\Core\Profile\Model\Pseudonymize\Table;
use Waldhacker\Pseudify\Core\Profile\Model\Pseudonymize\TableDefinition;
use Waldhacker\Pseudify\Core\Profile\Pseudonymize\ProfileInterface;

class InvalidPseudonymizeProfile implements ProfileInterface
{
    public function getIdentifier(): string
    {
        return 'invalid';
    }

    public function getTableDefinition(): TableDefinition
    {
        $tableDefinition = new TableDefinition('Invalid');
        $tableDefinition
            ->addTable(Table::create('wh_user')
                ->addColumn(Column::create('username')
                    ->addDataProcessing(DataManipulatorPreset::scalarData(fakerFormatter: 'userName', processingIdentifier: 'p-1'))
                )
            );

        $tableDefinition->getTable('non_existing');

        return $tableDefinition;
    }
}
