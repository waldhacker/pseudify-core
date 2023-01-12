<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Application\Profiles\Fixtures;

use Doctrine\DBAL\Platforms\SQLServer2012Platform;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\Column as DoctrineColumn;
use Faker\Provider\Person;
use Waldhacker\Pseudify\Core\Faker\Faker;
use Waldhacker\Pseudify\Core\Processor\Encoder\Base64Encoder;
use Waldhacker\Pseudify\Core\Processor\Encoder\ChainedEncoder;
use Waldhacker\Pseudify\Core\Processor\Encoder\GzEncodeEncoder;
use Waldhacker\Pseudify\Core\Processor\Encoder\HexEncoder;
use Waldhacker\Pseudify\Core\Processor\Encoder\JsonEncoder;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Node\StringNode;
use Waldhacker\Pseudify\Core\Processor\Encoder\SerializedEncoder;
use Waldhacker\Pseudify\Core\Processor\Processing\DataProcessing;
use Waldhacker\Pseudify\Core\Processor\Processing\Pseudonymize\DataManipulatorContext;
use Waldhacker\Pseudify\Core\Processor\Processing\Pseudonymize\DataManipulatorPreset;
use Waldhacker\Pseudify\Core\Profile\Model\Pseudonymize\Column;
use Waldhacker\Pseudify\Core\Profile\Model\Pseudonymize\Table;
use Waldhacker\Pseudify\Core\Profile\Model\Pseudonymize\TableDefinition;
use Waldhacker\Pseudify\Core\Profile\Pseudonymize\ProfileInterface;

class TestPseudonymizeProfile implements ProfileInterface
{
    public function getIdentifier(): string
    {
        return 'test';
    }

    public function getTableDefinition(): TableDefinition
    {
        $tableDefinition = new TableDefinition('TEST');
        $tableDefinition
            ->addTable(Table::create('non_existing_table')
                ->addColumn(Column::create('non_existing_column'))
            )
            ->addTable(Table::create('wh_user')
                ->addColumn(Column::create('non_existing_column'))
                ->addColumn(Column::create('username')
                    ->addDataProcessing(DataManipulatorPreset::scalarData(fakerFormatter: 'userName'))
                )
                ->addColumn(Column::create('password')
                    ->addDataProcessing(new DataProcessing(
                        function (DataManipulatorContext $context): void {
                            $context->setProcessedData($context->fake()->password());
                        },
                        uniqid()
                    ))
                )
                ->addColumn(Column::create('first_name')
                    ->addDataProcessing(DataManipulatorPreset::scalarData(processingIdentifier: 'p-1', fakerFormatter: 'firstName', fakerArguments: [Person::GENDER_FEMALE]))
                )
                ->addColumn(Column::create('last_name')
                    ->addDataProcessing(DataManipulatorPreset::scalarData(fakerFormatter: 'lastName', processingIdentifier: 'p-1'))
                )
                ->addColumn(Column::create('email')
                    ->addDataProcessing(DataManipulatorPreset::scalarData(fakerFormatter: 'email', processingIdentifier: 'p-1'))
                )
                ->addColumn(Column::create('city')
                    ->addDataProcessing(new DataProcessing(
                        function (DataManipulatorContext $context): void {
                            $context->setProcessedData($context->fake()->city());
                        },
                        uniqid()
                    ))
                )
            )
            ->addTable(Table::create('wh_user_session')
                ->addColumn(Column::create('session_data', Column::DATA_TYPE_SERIALIZED)
                    ->addDataProcessing(new DataProcessing(
                        function (DataManipulatorContext $context): void {
                            $node = $context->getProcessedData();
                            $node->replaceProperty('last_ip', new StringNode($context->fake(scope: Faker::DEFAULT_SCOPE, source: $node->getPropertyContent('last_ip')->getValue())->ipv4()));

                            $context->setProcessedData($node);
                        },
                        uniqid()
                    ))
                )
            )
            ->addTable(Table::create('wh_meta_data')
                ->addColumn(Column::create('meta_data')
                    ->setEncoder(new ChainedEncoder([
                        new HexEncoder(),
                        new GzEncodeEncoder([
                            GzEncodeEncoder::ENCODE_LEVEL => 5,
                            GzEncodeEncoder::ENCODE_ENCODING => ZLIB_ENCODING_GZIP,
                        ]),
                        new SerializedEncoder(),
                    ]))
                    ->addDataProcessing(new DataProcessing(
                        function (DataManipulatorContext $context): void {
                            $node = $context->getProcessedData();

                            $node->getPropertyContent('key1')
                                ->replaceProperty('username', new StringNode($context->fake(scope: Faker::DEFAULT_SCOPE, source: $node->getPropertyContent('key1')->getPropertyContent('username')->getValue())->userName()))
                                ->replaceProperty('password', new StringNode($context->fake(scope: Faker::DEFAULT_SCOPE, source: $node->getPropertyContent('key1')->getPropertyContent('password')->getValue())->password()))
                                ->replaceProperty('first_name', new StringNode($context->fake(scope: Faker::DEFAULT_SCOPE, source: $node->getPropertyContent('key1')->getPropertyContent('first_name')->getValue())->firstName(Person::GENDER_FEMALE)))
                                ->replaceProperty('last_name', new StringNode($context->fake(scope: Faker::DEFAULT_SCOPE, source: $node->getPropertyContent('key1')->getPropertyContent('last_name')->getValue())->lastName()))
                                ->replaceProperty('email', new StringNode($context->fake(scope: Faker::DEFAULT_SCOPE, source: $node->getPropertyContent('key1')->getPropertyContent('email')->getValue())->safeEmail()))
                                ->replaceProperty('city', new StringNode($context->fake(scope: Faker::DEFAULT_SCOPE, source: $node->getPropertyContent('key1')->getPropertyContent('city')->getValue())->city()));

                            $encoder = new SerializedEncoder();
                            $rawSessionData = $node->getPropertyContent('key2')->getPropertyContent('session_data')->getValue();
                            $sessionData = $encoder->decode($rawSessionData);
                            $sessionData->replaceProperty('last_ip', new StringNode($context->fake(scope: Faker::DEFAULT_SCOPE, source: $sessionData->getPropertyContent('last_ip')->getValue())->ipv4()));
                            $rawSessionData = $encoder->encode($sessionData);

                            $node->getPropertyContent('key2')
                                ->replaceProperty('session_data', new StringNode($rawSessionData));

                            $node->getPropertyContent('key3')
                                ->replaceProperty('key4', new StringNode($context->fake(scope: Faker::DEFAULT_SCOPE, source: $node->getPropertyContent('key3')->getPropertyContent('key4')->getValue())->ipv4()));

                            $context->setProcessedData($node);
                        },
                        uniqid()
                    ))
                )
            )
            ->addTable(Table::create('wh_log')
                ->addColumn(Column::create('log_data')
                    ->addDataProcessing(new DataProcessing(
                        function (DataManipulatorContext $context): void {
                            $row = $context->getDatebaseRow();
                            if ('foo' !== $row['log_type']) {
                                return;
                            }
                            $rawData = $context->getProcessedData();

                            $encoder = new ChainedEncoder([new HexEncoder(), new Base64Encoder(), new JsonEncoder()]);
                            $logData = $encoder->decode($rawData);

                            $logData['userName'] = $context->fake(scope: Faker::DEFAULT_SCOPE, source: $logData['userName'])->userName();
                            $logData['email'] = $context->fake(scope: Faker::DEFAULT_SCOPE, source: $logData['email'])->safeEmail();
                            $logData['lastName'] = $context->fake(scope: Faker::DEFAULT_SCOPE, source: $logData['lastName'])->lastName();
                            $logData['ip'] = $context->fake(scope: Faker::DEFAULT_SCOPE, source: $logData['ip'])->ipv4();

                            $context->setProcessedData($encoder->encode($logData));
                        },
                        uniqid()
                    ))
                    ->addDataProcessing(new DataProcessing(
                        function (DataManipulatorContext $context): void {
                            $row = $context->getDatebaseRow();
                            if ('bar' !== $row['log_type']) {
                                return;
                            }
                            $rawData = $context->getProcessedData();

                            $encoder = new ChainedEncoder([new HexEncoder(), new SerializedEncoder()]);
                            $logData = $encoder->decode($rawData);

                            $logData->replaceProperty(0, new StringNode($context->fake()->ipv4()));

                            $logData->getPropertyContent('user')
                                ->replaceProperty('userName', new StringNode($context->fake(scope: Faker::DEFAULT_SCOPE, source: $logData->getPropertyContent('user')->getPropertyContent('userName')->getValue())->userName()))
                                ->replaceProperty('lastName', new StringNode($context->fake(scope: Faker::DEFAULT_SCOPE, source: $logData->getPropertyContent('user')->getPropertyContent('lastName')->getValue())->lastName()))
                                ->replaceProperty('email', new StringNode($context->fake(scope: Faker::DEFAULT_SCOPE, source: $logData->getPropertyContent('user')->getPropertyContent('email')->getValue())->safeEmail()));

                            $context->setProcessedData($encoder->encode($logData));
                        },
                        uniqid()
                    ))
                )
                ->addColumn(Column::create('log_message', Column::DATA_TYPE_JSON)
                    ->addDataProcessing(new DataProcessing(
                        function (DataManipulatorContext $context): void {
                            $logMessage = $context->getProcessedData();
                            $row = $context->getDatebaseRow();
                            preg_match('/^.*(".*").*(".*")$/', $logMessage['message'], $matches);
                            array_shift($matches);

                            if ('foo' === $row['log_type']) {
                                $userName = trim($matches[0], '"');
                                $mail = trim($matches[1], '"');

                                $logMessage['message'] = strtr($logMessage['message'], [
                                    $matches[0] => sprintf('"%s"', $context->fake(scope: Faker::DEFAULT_SCOPE, source: $userName)->userName()),
                                    $matches[1] => sprintf('"%s"', $context->fake(scope: Faker::DEFAULT_SCOPE, source: $mail)->safeEmail()),
                                ]);
                            } else {
                                $lastName = trim($matches[0], '"');
                                $userName = trim($matches[1], '"');

                                $logMessage['message'] = strtr($logMessage['message'], [
                                    $matches[0] => sprintf('"%s"', $context->fake(scope: Faker::DEFAULT_SCOPE, source: $lastName)->lastName()),
                                    $matches[1] => sprintf('"%s"', $context->fake(scope: Faker::DEFAULT_SCOPE, source: $userName)->userName()),
                                ]);
                            }

                            $context->setProcessedData($logMessage);
                        },
                        uniqid()
                    ))
                    ->onBeforeUpdateData(function (QueryBuilder $queryBuilder, Table $table, Column $column, DoctrineColumn $columnInfo, $originalData, $processedData, array $databaseRow) {
                        if ($queryBuilder->getConnection()->getDatabasePlatform() instanceof SQLServer2012Platform) {
                            $bindingType = $column->getBindingType() ?? $columnInfo->getType()->getBindingType();
                            $queryBuilder->where(
                                $queryBuilder->expr()->eq(
                                    sprintf('CONVERT(VARCHAR(MAX), %s)', $queryBuilder->getConnection()->quoteIdentifier($column->getIdentifier())),
                                    $queryBuilder->createNamedParameter($originalData, $bindingType, ':dcValue2')
                                )
                            );
                        }
                    })
                )
                ->addColumn(Column::create('ip')
                    ->addDataProcessing(DataManipulatorPreset::ip())
                )
            );

        return $tableDefinition;
    }
}
