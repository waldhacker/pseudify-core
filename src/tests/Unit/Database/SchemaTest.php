<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\MySQLSchemaManager;
use Doctrine\DBAL\Types\StringType;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Waldhacker\Pseudify\Core\Database\ConnectionManager;
use Waldhacker\Pseudify\Core\Database\MissingColumnException;
use Waldhacker\Pseudify\Core\Database\Query;
use Waldhacker\Pseudify\Core\Database\Schema;

class SchemaTest extends TestCase
{
    use ProphecyTrait;

    public function testListTableNames(): void
    {
        $mySQLSchemaManagerProphecy = $this->prophesize(MySQLSchemaManager::class);
        $mySQLSchemaManagerProphecy->listTableNames()->willReturn(['table_1', 'table_2', 'table_3']);

        $connectionManagerProphecy = $this->prophesize(ConnectionManager::class);
        $connectionProphecy = $this->prophesize(Connection::class);
        $connectionProphecy->createSchemaManager()->willReturn($mySQLSchemaManagerProphecy->reveal());

        $queryProphecy = $this->prophesize(Query::class);
        $queryProphecy->unquoteSingleIdentifier(Argument::any())->willReturnArgument(0);
        $connectionManagerProphecy->getConnection()->willReturn($connectionProphecy->reveal());

        $schema = new Schema($connectionManagerProphecy->reveal(), $queryProphecy->reveal());

        $this->assertEquals(
            ['table_1', 'table_2', 'table_3'],
            $schema->listTableNames()
        );
    }

    public function testListTableColumns(): void
    {
        $mySQLSchemaManagerProphecy = $this->prophesize(MySQLSchemaManager::class);
        $mySQLSchemaManagerProphecy->listTableColumns(Argument::any())->willReturn([
            new Column('column_1', new StringType()),
            new Column('column_2', new StringType()),
            new Column('column_3', new StringType()),
        ]);

        $connectionManagerProphecy = $this->prophesize(ConnectionManager::class);
        $connectionProphecy = $this->prophesize(Connection::class);
        $connectionProphecy->createSchemaManager()->willReturn($mySQLSchemaManagerProphecy->reveal());

        $queryProphecy = $this->prophesize(Query::class);
        $queryProphecy->unquoteSingleIdentifier(Argument::any())->willReturnArgument(0);
        $connectionManagerProphecy->getConnection()->willReturn($connectionProphecy->reveal());

        $schema = new Schema($connectionManagerProphecy->reveal(), $queryProphecy->reveal());

        $this->assertEquals(
            [
                ['name' => 'column_1', 'column' => new Column('column_1', new StringType())],
                ['name' => 'column_2', 'column' => new Column('column_2', new StringType())],
                ['name' => 'column_3', 'column' => new Column('column_3', new StringType())],
            ],
            $schema->listTableColumns('')
        );
    }

    public function testGetColumnReturnsColumnArray(): void
    {
        $mySQLSchemaManagerProphecy = $this->prophesize(MySQLSchemaManager::class);
        $mySQLSchemaManagerProphecy->listTableColumns(Argument::any())->willReturn([
            new Column('column_1', new StringType()),
            new Column('column_2', new StringType()),
            new Column('column_3', new StringType()),
        ]);

        $connectionManagerProphecy = $this->prophesize(ConnectionManager::class);
        $connectionProphecy = $this->prophesize(Connection::class);
        $connectionProphecy->createSchemaManager()->willReturn($mySQLSchemaManagerProphecy->reveal());

        $queryProphecy = $this->prophesize(Query::class);
        $queryProphecy->unquoteSingleIdentifier(Argument::any())->willReturnArgument(0);
        $connectionManagerProphecy->getConnection()->willReturn($connectionProphecy->reveal());

        $schema = new Schema($connectionManagerProphecy->reveal(), $queryProphecy->reveal());

        $this->assertEquals(
            ['name' => 'column_2', 'column' => new Column('column_2', new StringType())],
            $schema->getColumn('', 'column_2')
        );
    }

    public function testGetColumnThrowsExceptionIfColumnNotExists(): void
    {
        $this->expectException(MissingColumnException::class);
        $this->expectExceptionCode(1621654988);

        $mySQLSchemaManagerProphecy = $this->prophesize(MySQLSchemaManager::class);
        $mySQLSchemaManagerProphecy->listTableColumns(Argument::any())->willReturn([
            new Column('column_1', new StringType()),
            new Column('column_2', new StringType()),
            new Column('column_3', new StringType()),
        ]);

        $connectionManagerProphecy = $this->prophesize(ConnectionManager::class);
        $connectionProphecy = $this->prophesize(Connection::class);
        $connectionProphecy->createSchemaManager()->willReturn($mySQLSchemaManagerProphecy->reveal());

        $queryProphecy = $this->prophesize(Query::class);
        $queryProphecy->unquoteSingleIdentifier(Argument::any())->willReturnArgument(0);
        $connectionManagerProphecy->getConnection()->willReturn($connectionProphecy->reveal());

        $schema = new Schema($connectionManagerProphecy->reveal(), $queryProphecy->reveal());
        $schema->getColumn('', 'column_42');
    }
}
