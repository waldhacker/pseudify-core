<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Profile\Model\Analyze;

use PHPUnit\Framework\TestCase;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\Stats;

class StatsTest extends TestCase
{
    public function statsDataProvider(): array
    {
        return [
            'test set 1' => [
                'sourceTableRowCount' => ['table_1' => 1, 'table_2' => 1],
                'sourceTableColumnCount' => ['table_1' => 1, 'table_2' => 1],
                'targetTableRowCount' => ['table_3' => 1, 'table_4' => 1],
                'targetTableColumnCount' => ['table_3' => 1, 'table_4' => 1],
                'withCustomStringSearch' => false,
                'expectedTotalProcessings' => 4,
                'expectedTotalTargetProcessings' => 2,
            ],
            'test set 2' => [
                'sourceTableRowCount' => ['table_1' => 1, 'table_2' => 1],
                'sourceTableColumnCount' => ['table_1' => 1, 'table_2' => 1],
                'targetTableRowCount' => ['table_3' => 2, 'table_4' => 2],
                'targetTableColumnCount' => ['table_3' => 1, 'table_4' => 1],
                'withCustomStringSearch' => false,
                'expectedTotalProcessings' => 8,
                'expectedTotalTargetProcessings' => 4,
            ],
            'test set 3' => [
                'sourceTableRowCount' => ['table_1' => 2, 'table_2' => 2],
                'sourceTableColumnCount' => ['table_1' => 1, 'table_2' => 1],
                'targetTableRowCount' => ['table_3' => 2, 'table_4' => 2],
                'targetTableColumnCount' => ['table_3' => 1, 'table_4' => 1],
                'withCustomStringSearch' => false,
                'expectedTotalProcessings' => 16,
                'expectedTotalTargetProcessings' => 4,
            ],
            'test set 4' => [
                'sourceTableRowCount' => ['table_1' => 50, 'table_2' => 1000],
                'sourceTableColumnCount' => ['table_1' => 2, 'table_2' => 4],
                'targetTableRowCount' => ['table_3' => 100, 'table_4' => 500],
                'targetTableColumnCount' => ['table_3' => 5, 'table_4' => 3],
                'withCustomStringSearch' => false,
                'expectedTotalProcessings' => 8200000,
                'expectedTotalTargetProcessings' => 2000,
            ],
            'test set 5' => [
                'sourceTableRowCount' => ['table_1' => 50, 'table_2' => 1000],
                'sourceTableColumnCount' => ['table_1' => 2, 'table_2' => 4],
                'targetTableRowCount' => ['table_3' => 100, 'table_4' => 500],
                'targetTableColumnCount' => ['table_3' => 5, 'table_4' => 3],
                'withCustomStringSearch' => true,
                'expectedTotalProcessings' => 8202000,
                'expectedTotalTargetProcessings' => 2000,
            ],
        ];
    }

    /**
     * @dataProvider statsDataProvider
     */
    public function testGetTotalProcessingsReturnsNumberOfTotalProcessings(
        array $sourceTableRowCount,
        array $sourceTableColumnCount,
        array $targetTableRowCount,
        array $targetTableColumnCount,
        bool $withCustomStringSearch,
        int $expectedTotalProcessings,
        int $expectedTotalTargetProcessings
    ): void {
        $stats = new Stats(
            $sourceTableRowCount,
            $sourceTableColumnCount,
            $targetTableRowCount,
            $targetTableColumnCount,
            $withCustomStringSearch
        );

        $this->assertEquals(
            $expectedTotalProcessings,
            $stats->getTotalProcessings()
        );
    }

    /**
     * @dataProvider statsDataProvider
     */
    public function testGetTotalTargetProcessingsReturnsNumberOfTotalTargetProcessings(
        array $sourceTableRowCount,
        array $sourceTableColumnCount,
        array $targetTableRowCount,
        array $targetTableColumnCount,
        bool $withCustomStringSearch,
        int $expectedTotalProcessings,
        int $expectedTotalTargetProcessings
    ): void {
        $stats = new Stats(
            $sourceTableRowCount,
            $sourceTableColumnCount,
            $targetTableRowCount,
            $targetTableColumnCount,
            $withCustomStringSearch
        );

        $this->assertEquals(
            $expectedTotalTargetProcessings,
            $stats->getTotalTargetProcessings()
        );
    }

    public function testGetSourceTableRowCount(): void
    {
        $stats = new Stats(['t_1' => 50], ['t_1' => 2], ['t_3' => 100], ['t_3' => 5], false);
        $this->assertEquals(50, $stats->getSourceTableRowCount('t_1'));
    }

    public function testGetSourceTableColumnCount(): void
    {
        $stats = new Stats(['t_1' => 50], ['t_1' => 2], ['t_3' => 100], ['t_3' => 5], false);
        $this->assertEquals(2, $stats->getSourceTableColumnCount('t_1'));
    }

    public function testGetTargetTableRowCount(): void
    {
        $stats = new Stats(['t_1' => 50], ['t_1' => 2], ['t_3' => 100], ['t_3' => 5], false);
        $this->assertEquals(100, $stats->getTargetTableRowCount('t_3'));
    }

    public function testGetTargetTableColumnCount(): void
    {
        $stats = new Stats(['t_1' => 50], ['t_1' => 2], ['t_3' => 100], ['t_3' => 5], false);
        $this->assertEquals(5, $stats->getTargetTableColumnCount('t_3'));
    }
}
