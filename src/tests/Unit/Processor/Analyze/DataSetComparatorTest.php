<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Processor\Analyze;

use PHPUnit\Framework\TestCase;
use Waldhacker\Pseudify\Core\Processor\Analyze\DataSetComparator;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\Finding;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\SourceColumn;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\SourceTable;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\TargetColumn;
use Waldhacker\Pseudify\Core\Profile\Model\Analyze\TargetTable;

class DataSetComparatorTest extends TestCase
{
    public function compareDataSetsDataProvider(): array
    {
        return [
            [
                'sourceData' => [],
                'targetData' => [],
                'withTargetDataFrames' => false,
                'targetDataFrameCuttingLength' => 10,
                'expected' => [],
            ],
            [
                'sourceData' => ['needle'],
                'targetData' => ['foo'],
                'withTargetDataFrames' => false,
                'targetDataFrameCuttingLength' => 10,
                'expected' => [],
            ],
            [
                'sourceData' => ['needle'],
                'targetData' => ['needle'],
                'withTargetDataFrames' => false,
                'targetDataFrameCuttingLength' => 10,
                'expected' => [new Finding(new SourceTable('__custom__'), new SourceColumn('__custom__'), new TargetTable('__custom__'), new TargetColumn('__custom__'), 'needle', [])],
            ],
            [
                'sourceData' => ['needle'],
                'targetData' => ['needle'],
                'withTargetDataFrames' => true,
                'targetDataFrameCuttingLength' => 10,
                'expected' => [new Finding(new SourceTable('__custom__'), new SourceColumn('__custom__'), new TargetTable('__custom__'), new TargetColumn('__custom__'), 'needle', ['needle'])],
            ],
            [
                'sourceData' => ['needle'],
                'targetData' => ['some very very long needle text before and after'],
                'withTargetDataFrames' => true,
                'targetDataFrameCuttingLength' => 15,
                'expected' => [new Finding(new SourceTable('__custom__'), new SourceColumn('__custom__'), new TargetTable('__custom__'), new TargetColumn('__custom__'), 'needle', ['...very very long needle text before an...'])],
            ],
            [
                'sourceData' => ['needle'],
                'targetData' => ['some very very long needle text before and after'],
                'withTargetDataFrames' => true,
                'targetDataFrameCuttingLength' => 0,
                'expected' => [new Finding(new SourceTable('__custom__'), new SourceColumn('__custom__'), new TargetTable('__custom__'), new TargetColumn('__custom__'), 'needle', ['some very very long needle text before and after'])],
            ],
            [
                'sourceData' => ['needle'],
                'targetData' => ['some needle text'],
                'withTargetDataFrames' => false,
                'targetDataFrameCuttingLength' => 10,
                'expected' => [new Finding(new SourceTable('__custom__'), new SourceColumn('__custom__'), new TargetTable('__custom__'), new TargetColumn('__custom__'), 'needle', [])],
            ],
            [
                'sourceData' => ['needle'],
                'targetData' => ['some needle text'],
                'withTargetDataFrames' => true,
                'targetDataFrameCuttingLength' => 10,
                'expected' => [new Finding(new SourceTable('__custom__'), new SourceColumn('__custom__'), new TargetTable('__custom__'), new TargetColumn('__custom__'), 'needle', ['some needle text'])],
            ],
            [
                'sourceData' => ['needle'],
                'targetData' => ['needle text'],
                'withTargetDataFrames' => true,
                'targetDataFrameCuttingLength' => 10,
                'expected' => [new Finding(new SourceTable('__custom__'), new SourceColumn('__custom__'), new TargetTable('__custom__'), new TargetColumn('__custom__'), 'needle', ['needle text'])],
            ],
            [
                'sourceData' => ['needle'],
                'targetData' => ['some needle'],
                'withTargetDataFrames' => true,
                'targetDataFrameCuttingLength' => 10,
                'expected' => [new Finding(new SourceTable('__custom__'), new SourceColumn('__custom__'), new TargetTable('__custom__'), new TargetColumn('__custom__'), 'needle', ['some needle'])],
            ],
            [
                'sourceData' => ['foo', 'bar'],
                'targetData' => ['some foo and some bar'],
                'withTargetDataFrames' => false,
                'targetDataFrameCuttingLength' => 10,
                'expected' => [
                    new Finding(new SourceTable('__custom__'), new SourceColumn('__custom__'), new TargetTable('__custom__'), new TargetColumn('__custom__'), 'foo', []),
                    new Finding(new SourceTable('__custom__'), new SourceColumn('__custom__'), new TargetTable('__custom__'), new TargetColumn('__custom__'), 'bar', []),
                ],
            ],
            [
                'sourceData' => ['foo', 'bar'],
                'targetData' => ['some foo and some bar'],
                'withTargetDataFrames' => true,
                'targetDataFrameCuttingLength' => 10,
                'expected' => [
                    new Finding(new SourceTable('__custom__'), new SourceColumn('__custom__'), new TargetTable('__custom__'), new TargetColumn('__custom__'), 'foo', ['some foo and some ...']),
                    new Finding(new SourceTable('__custom__'), new SourceColumn('__custom__'), new TargetTable('__custom__'), new TargetColumn('__custom__'), 'bar', ['... and some bar']),
                ],
            ],
            [
                'sourceData' => ['foo'],
                'targetData' => ['some foo and some other foo'],
                'withTargetDataFrames' => false,
                'targetDataFrameCuttingLength' => 10,
                'expected' => [
                    new Finding(new SourceTable('__custom__'), new SourceColumn('__custom__'), new TargetTable('__custom__'), new TargetColumn('__custom__'), 'foo', []),
                ],
            ],
            [
                'sourceData' => ['foo'],
                'targetData' => ['some foo and some other foo'],
                'withTargetDataFrames' => true,
                'targetDataFrameCuttingLength' => 10,
                'expected' => [
                    new Finding(new SourceTable('__custom__'), new SourceColumn('__custom__'), new TargetTable('__custom__'), new TargetColumn('__custom__'), 'foo', ['some foo and some ...', '...ome other foo']),
                ],
            ],
            [
                'sourceData' => ['foo', 'bar'],
                'targetData' => ['some foo and some other foo', 'some bar and some other bar'],
                'withTargetDataFrames' => false,
                'targetDataFrameCuttingLength' => 10,
                'expected' => [
                    new Finding(new SourceTable('__custom__'), new SourceColumn('__custom__'), new TargetTable('__custom__'), new TargetColumn('__custom__'), 'foo', []),
                    new Finding(new SourceTable('__custom__'), new SourceColumn('__custom__'), new TargetTable('__custom__'), new TargetColumn('__custom__'), 'bar', []),
                ],
            ],
            [
                'sourceData' => ['foo', 'bar'],
                'targetData' => ['some foo and some other foo', 'some bar and some other bar'],
                'withTargetDataFrames' => true,
                'targetDataFrameCuttingLength' => 10,
                'expected' => [
                    new Finding(new SourceTable('__custom__'), new SourceColumn('__custom__'), new TargetTable('__custom__'), new TargetColumn('__custom__'), 'foo', ['some foo and some ...', '...ome other foo']),
                    new Finding(new SourceTable('__custom__'), new SourceColumn('__custom__'), new TargetTable('__custom__'), new TargetColumn('__custom__'), 'bar', ['some bar and some ...', '...ome other bar']),
                ],
            ],
            [
                'sourceData' => [987654321],
                'targetData' => ['some 987654321'],
                'withTargetDataFrames' => false,
                'targetDataFrameCuttingLength' => 10,
                'expected' => [
                    new Finding(new SourceTable('__custom__'), new SourceColumn('__custom__'), new TargetTable('__custom__'), new TargetColumn('__custom__'), '987654321', []),
                ],
            ],
            [
                'sourceData' => ["hello \xE9"],
                'targetData' => ['hello'],
                'withTargetDataFrames' => false,
                'targetDataFrameCuttingLength' => 10,
                'expected' => [],
            ],
            [
                'sourceData' => ["hello \xE9"],
                'targetData' => ['hello foo'],
                'withTargetDataFrames' => false,
                'targetDataFrameCuttingLength' => 10,
                'expected' => [
                    new Finding(new SourceTable('__custom__'), new SourceColumn('__custom__'), new TargetTable('__custom__'), new TargetColumn('__custom__'), 'hello ', []),
                ],
            ],
            [
                'sourceData' => ['regex:[a-zA-Z]+@waldhacker\.dev'],
                'targetData' => ['hello info@waldhacker.dev foo'],
                'withTargetDataFrames' => false,
                'targetDataFrameCuttingLength' => 10,
                'expected' => [
                    new Finding(new SourceTable('__custom__'), new SourceColumn('__custom__'), new TargetTable('__custom__'), new TargetColumn('__custom__'), 'info@waldhacker.dev', []),
                ],
            ],
            [
                'sourceData' => ['regex:([a-zA-Z]+@waldhacker\.dev)'],
                'targetData' => ['hello info@waldhacker.dev foo'],
                'withTargetDataFrames' => false,
                'targetDataFrameCuttingLength' => 10,
                'expected' => [
                    new Finding(new SourceTable('__custom__'), new SourceColumn('__custom__'), new TargetTable('__custom__'), new TargetColumn('__custom__'), 'info@waldhacker.dev', []),
                ],
            ],
            [
                'sourceData' => ['regex:([a-zA-Z]+)(@waldhacker\.dev)'],
                'targetData' => ['hello info@waldhacker.dev foo'],
                'withTargetDataFrames' => false,
                'targetDataFrameCuttingLength' => 10,
                'expected' => [
                    new Finding(new SourceTable('__custom__'), new SourceColumn('__custom__'), new TargetTable('__custom__'), new TargetColumn('__custom__'), 'info@waldhacker.dev', []),
                    new Finding(new SourceTable('__custom__'), new SourceColumn('__custom__'), new TargetTable('__custom__'), new TargetColumn('__custom__'), 'info', []),
                    new Finding(new SourceTable('__custom__'), new SourceColumn('__custom__'), new TargetTable('__custom__'), new TargetColumn('__custom__'), '@waldhacker.dev', []),
                ],
            ],
            [
                'sourceData' => ['regex:([a-zA-Z]+)(@waldhacker\.dev)'],
                'targetData' => ['hello info@waldhacker.dev foo', 'ahoi woot@waldhacker.dev bar'],
                'withTargetDataFrames' => true,
                'targetDataFrameCuttingLength' => 10,
                'expected' => [
                    new Finding(new SourceTable('__custom__'), new SourceColumn('__custom__'), new TargetTable('__custom__'), new TargetColumn('__custom__'), 'info@waldhacker.dev', ['hello info@waldhacker.dev foo']),
                    new Finding(new SourceTable('__custom__'), new SourceColumn('__custom__'), new TargetTable('__custom__'), new TargetColumn('__custom__'), 'info', ['hello info@waldhacke...']),
                    new Finding(new SourceTable('__custom__'), new SourceColumn('__custom__'), new TargetTable('__custom__'), new TargetColumn('__custom__'), '@waldhacker.dev', ['ahoi woot@waldhacker.dev bar']),
                    new Finding(new SourceTable('__custom__'), new SourceColumn('__custom__'), new TargetTable('__custom__'), new TargetColumn('__custom__'), 'woot@waldhacker.dev', ['ahoi woot@waldhacker.dev bar']),
                    new Finding(new SourceTable('__custom__'), new SourceColumn('__custom__'), new TargetTable('__custom__'), new TargetColumn('__custom__'), 'woot', ['ahoi woot@waldhacke...']),
                ],
            ],
            [
                'sourceData' => ['regex:#@waldhacker\.dev'],
                'targetData' => ['hello #@waldhacker.dev foo'],
                'withTargetDataFrames' => false,
                'targetDataFrameCuttingLength' => 10,
                'expected' => [
                    new Finding(new SourceTable('__custom__'), new SourceColumn('__custom__'), new TargetTable('__custom__'), new TargetColumn('__custom__'), '#@waldhacker.dev', []),
                ],
            ],
            [
                'sourceData' => ['regex:info@example\.com'],
                'targetData' => ['hello info@waldhacker.dev foo'],
                'withTargetDataFrames' => false,
                'targetDataFrameCuttingLength' => 10,
                'expected' => [],
            ],
        ];
    }

    /**
     * @dataProvider compareDataSetsDataProvider
     */
    public function testCompareDataSets(array $sourceData, array $targetData, bool $withTargetDataFrames, int $targetDataFrameCuttingLength, array $expected): void
    {
        $this->assertEquals(
            $expected,
            array_values((new DataSetComparator())->compareDataSets(
                $sourceData,
                $targetData,
                new SourceTable('__custom__'),
                new SourceColumn('__custom__'),
                new TargetTable('__custom__'),
                new TargetColumn('__custom__'),
                $withTargetDataFrames,
                $targetDataFrameCuttingLength
            ))
        );
    }
}
