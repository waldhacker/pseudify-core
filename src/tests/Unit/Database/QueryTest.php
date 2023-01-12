<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQL94Platform;
use Doctrine\DBAL\Platforms\SQLServer2012Platform;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Waldhacker\Pseudify\Core\Database\ConnectionManager;
use Waldhacker\Pseudify\Core\Database\InvalidStatementException;
use Waldhacker\Pseudify\Core\Database\Query;

class QueryTest extends TestCase
{
    use ProphecyTrait;

    public function unquoteSingleIdentifierUnquotesCorrectlyOnDifferentPlatformsDataProvider(): array
    {
        return [
            'mysql' => [
                'platform' => MySQLPlatform::class,
                'quoteChar' => '`',
                'input' => '`anIdentifier`',
                'expected' => 'anIdentifier',
            ],
            'mysql with spaces' => [
                'platform' => MySQLPlatform::class,
                'quoteChar' => '`',
                'input' => ' `anIdentifier` ',
                'expected' => 'anIdentifier',
            ],
            'postgres' => [
                'platform' => PostgreSQL94Platform::class,
                'quoteChar' => '"',
                'input' => '"anIdentifier"',
                'expected' => 'anIdentifier',
            ],
            'mssql' => [
                'platform' => SQLServer2012Platform::class,
                'quoteChar' => '', // no single quote character, but [ and ]
                'input' => '[anIdentifier]',
                'expected' => 'anIdentifier',
            ],
        ];
    }

    /**
     * @dataProvider unquoteSingleIdentifierUnquotesCorrectlyOnDifferentPlatformsDataProvider
     */
    public function testUnquoteSingleIdentifierUnquotesCorrectlyOnDifferentPlatforms(string $platform, string $quoteChar, string $input, string $expected): void
    {
        $connectionManagerProphecy = $this->prophesize(ConnectionManager::class);
        $connectionProphecy = $this->prophesize(Connection::class);
        $databasePlatformProphecy = $this->prophesize($platform);
        $databasePlatformProphecy->getIdentifierQuoteCharacter()->willReturn($quoteChar);
        $connectionProphecy->getDatabasePlatform()->willReturn($databasePlatformProphecy->reveal());
        $connectionManagerProphecy->getConnection()->willReturn($connectionProphecy->reveal());
        $query = new Query($connectionManagerProphecy->reveal());

        $this->assertEquals(
            $expected,
            $query->unquoteSingleIdentifier($input)
        );
    }

    public function quoteIdentifiersForSelectDataProvider(): array
    {
        return [
            'fieldName' => [
                'fieldName',
                '`fieldName`',
            ],
            'tableName.fieldName' => [
                'tableName.fieldName',
                '`tableName`.`fieldName`',
            ],
            'tableName.*' => [
                'tableName.*',
                '`tableName`.*',
            ],
            '*' => [
                '*',
                '*',
            ],
            'fieldName AS anotherFieldName' => [
                'fieldName AS anotherFieldName',
                '`fieldName` AS `anotherFieldName`',
            ],
            'tableName.fieldName AS anotherFieldName' => [
                'tableName.fieldName AS anotherFieldName',
                '`tableName`.`fieldName` AS `anotherFieldName`',
            ],
            'tableName.fieldName AS anotherTable.anotherFieldName' => [
                'tableName.fieldName AS anotherTable.anotherFieldName',
                '`tableName`.`fieldName` AS `anotherTable`.`anotherFieldName`',
            ],
            'fieldName as anotherFieldName' => [
                'fieldName as anotherFieldName',
                '`fieldName` AS `anotherFieldName`',
            ],
            'tableName.fieldName as anotherFieldName' => [
                'tableName.fieldName as anotherFieldName',
                '`tableName`.`fieldName` AS `anotherFieldName`',
            ],
            'tableName.fieldName as anotherTable.anotherFieldName' => [
                'tableName.fieldName as anotherTable.anotherFieldName',
                '`tableName`.`fieldName` AS `anotherTable`.`anotherFieldName`',
            ],
            'fieldName aS anotherFieldName' => [
                'fieldName aS anotherFieldName',
                '`fieldName` AS `anotherFieldName`',
            ],
            'tableName.fieldName aS anotherFieldName' => [
                'tableName.fieldName aS anotherFieldName',
                '`tableName`.`fieldName` AS `anotherFieldName`',
            ],
            'tableName.fieldName aS anotherTable.anotherFieldName' => [
                'tableName.fieldName aS anotherTable.anotherFieldName',
                '`tableName`.`fieldName` AS `anotherTable`.`anotherFieldName`',
            ],
        ];
    }

    /**
     * @dataProvider quoteIdentifiersForSelectDataProvider
     */
    public function testQuoteIdentifiersForSelect($identifier, $expectedResult): void
    {
        $connectionManagerProphecy = $this->prophesize(ConnectionManager::class);
        $connectionProphecy = $this->prophesize(Connection::class);
        $connectionProphecy->getDatabasePlatform()->willReturn(new MySQLPlatform());
        $connectionProphecy->quoteIdentifier(Argument::cetera())->will(function ($arguments) {
            $platform = new MySQLPlatform();

            return $platform->quoteIdentifier($arguments[0]);
        });
        $connectionManagerProphecy->getConnection()->willReturn($connectionProphecy->reveal());

        $query = new Query($connectionManagerProphecy->reveal());

        $this->assertSame(
            [$expectedResult],
            $query->quoteIdentifiersForSelect([$identifier])
        );
    }

    public function testQuoteIdentifiersForSelectWithInvalidAliasThrowsException(): void
    {
        $connectionManagerProphecy = $this->prophesize(ConnectionManager::class);
        $connectionProphecy = $this->prophesize(Connection::class);
        $connectionProphecy->getDatabasePlatform()->willReturn(new MySQLPlatform());
        $connectionProphecy->quoteIdentifier(Argument::cetera())->will(function ($arguments) {
            $platform = new MySQLPlatform();

            return $platform->quoteIdentifier($arguments[0]);
        });
        $connectionManagerProphecy->getConnection()->willReturn($connectionProphecy->reveal());

        $query = new Query($connectionManagerProphecy->reveal());

        $this->expectException(InvalidStatementException::class);
        $this->expectExceptionCode(1619594365);

        $query->quoteIdentifiersForSelect(['aField AS anotherField,someField AS someThing']);
    }
}
