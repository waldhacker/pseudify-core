<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Application\Profiles\Fixtures;

use Waldhacker\Pseudify\Core\Processor\Processing\Analyze\SourceDataCollectorContext;
use Waldhacker\Pseudify\Core\Processor\Processing\DataProcessing;
use Waldhacker\Pseudify\Core\Profile\Analyze\ProfileInterface;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\SourceColumn;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\SourceTable;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\TableDefinition;

class NoProcessingAnalyzeProfile implements ProfileInterface
{
    public function getIdentifier(): string
    {
        return 'nop';
    }

    public function getTableDefinition(): TableDefinition
    {
        $tableDefinition = new TableDefinition('nop');
        $tableDefinition
            ->addSourceTable(SourceTable::create('wh_user')
                ->addColumn(SourceColumn::create('username')
                    ->addDataProcessing(new DataProcessing(
                        function (SourceDataCollectorContext $context): void {
                        },
                        uniqid()
                    ))
                )
            )
            ->addSourceTable(SourceTable::create('wh_log')
                ->addColumn(SourceColumn::create('log_data')
                    ->addDataProcessing(new DataProcessing(
                        function (SourceDataCollectorContext $context): void {
                        },
                        uniqid()
                    ))
                )
            );

        return $tableDefinition;
    }
}
