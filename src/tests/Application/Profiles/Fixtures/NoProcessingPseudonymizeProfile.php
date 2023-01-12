<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Application\Profiles\Fixtures;

use Waldhacker\Pseudify\Core\Processor\Processing\DataProcessing;
use Waldhacker\Pseudify\Core\Processor\Processing\Pseudonymize\DataManipulatorContext;
use Waldhacker\Pseudify\Core\Profile\Model\Pseudonymize\Column;
use Waldhacker\Pseudify\Core\Profile\Model\Pseudonymize\Table;
use Waldhacker\Pseudify\Core\Profile\Model\Pseudonymize\TableDefinition;
use Waldhacker\Pseudify\Core\Profile\Pseudonymize\ProfileInterface;

class NoProcessingPseudonymizeProfile implements ProfileInterface
{
    public function getIdentifier(): string
    {
        return 'nop';
    }

    public function getTableDefinition(): TableDefinition
    {
        $tableDefinition = new TableDefinition('nop');
        $tableDefinition
            ->addTable(Table::create('wh_user')
                ->addColumn(Column::create('username')
                    ->addDataProcessing(new DataProcessing(
                        function (DataManipulatorContext $context): void {
                        },
                        uniqid()
                    ))
                )
            );

        return $tableDefinition;
    }
}
