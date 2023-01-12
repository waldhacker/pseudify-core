<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Application\Profiles\Fixtures;

use Waldhacker\Pseudify\Core\Processor\Encoder\Base64Encoder;
use Waldhacker\Pseudify\Core\Processor\Encoder\ChainedEncoder;
use Waldhacker\Pseudify\Core\Processor\Encoder\GzEncodeEncoder;
use Waldhacker\Pseudify\Core\Processor\Encoder\HexEncoder;
use Waldhacker\Pseudify\Core\Processor\Processing\Analyze\SourceDataCollectorContext;
use Waldhacker\Pseudify\Core\Processor\Processing\Analyze\SourceDataCollectorPreset;
use Waldhacker\Pseudify\Core\Processor\Processing\Analyze\TargetDataDecoderContext;
use Waldhacker\Pseudify\Core\Processor\Processing\DataProcessing;
use Waldhacker\Pseudify\Core\Profile\Analyze\ProfileInterface;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\SourceColumn;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\SourceTable;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\TableDefinition;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\TargetColumn;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\TargetTable;

class TestAnalyzeProfile implements ProfileInterface
{
    public function getIdentifier(): string
    {
        return 'test';
    }

    public function getTableDefinition(): TableDefinition
    {
        $tableDefinition = new TableDefinition('TEST');
        $tableDefinition
            ->excludeTargetColumnTypes(TableDefinition::COMMON_EXCLUED_TARGET_COLUMN_TYPES)
            ->addSourceString('lafayette64@example.net')
            ->addSourceString('Homenick')

            ->addSourceTable(SourceTable::create('wh_user')
                ->addColumn(SourceColumn::create('username')->addDataProcessing(SourceDataCollectorPreset::scalarData()))
                ->addColumn(SourceColumn::create('password')->addDataProcessing(SourceDataCollectorPreset::scalarData('p-1')))
                ->addColumn(SourceColumn::create('first_name')->addDataProcessing(SourceDataCollectorPreset::scalarData('p-1')))
                ->addColumn(SourceColumn::create('last_name')->addDataProcessing(SourceDataCollectorPreset::scalarData('p-1')))
                ->addColumn(SourceColumn::create('email')->addDataProcessing(SourceDataCollectorPreset::scalarData('p-1')))
                ->addColumn(SourceColumn::create('city')->addDataProcessing(SourceDataCollectorPreset::scalarData('p-1')))
            )
            ->addSourceTable(SourceTable::create('wh_user_session')
                ->addColumn(SourceColumn::create('session_data', SourceColumn::DATA_TYPE_SERIALIZED)
                    ->addDataProcessing(new DataProcessing(
                        function (SourceDataCollectorContext $context): void {
                            $node = $context->getDecodedData();
                            $context->addCollectedData($node->getPropertyContent('last_ip')->getValue());
                        },
                        uniqid()
                    ))
                )
            )

            ->addTargetTable(TargetTable::create('wh_meta_data')
                ->excludeColumn(TargetColumn::create('meta_data_plaintext'))
                ->addColumn(TargetColumn::create('meta_data')
                    ->setEncoder(new ChainedEncoder([
                        new HexEncoder(),
                        new GzEncodeEncoder([
                            GzEncodeEncoder::ENCODE_LEVEL => 5,
                            GzEncodeEncoder::ENCODE_ENCODING => ZLIB_ENCODING_GZIP,
                        ]),
                    ]))
                )
            )
            ->addTargetTable(TargetTable::create('wh_log')
                ->excludeColumn(TargetColumn::create('log_data_plaintext'))
                ->addColumn(TargetColumn::create('log_data')
                    ->setEncoder(new HexEncoder())
                    ->addDataProcessing(new DataProcessing(
                        function (TargetDataDecoderContext $context): void {
                            $row = $context->getDatebaseRow();
                            if ('foo' !== $row['log_type']) {
                                return;
                            }
                            $rawData = $context->getDecodedData();

                            $encoder = new Base64Encoder();
                            $logData = $encoder->decode($rawData);

                            $context->setDecodedData($logData);
                        },
                        uniqid()
                    ))
                )
            );

        return $tableDefinition;
    }
}
