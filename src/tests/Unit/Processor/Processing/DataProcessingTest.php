<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Processor\Processing;

use PHPUnit\Framework\TestCase;
use Waldhacker\Pseudify\Core\Processor\Processing\DataProcessing;

class DataProcessingTest extends TestCase
{
    public function testGetIdentifierReturnsIdentifier(): void
    {
        $dataProcessing = new DataProcessing(function () { return; }, 'id-1');
        $this->assertEquals(
            'id-1',
            $dataProcessing->getIdentifier()
        );
    }

    public function testGetProcessorReturnsCallable(): void
    {
        $dataProcessing = new DataProcessing(function () { return; }, 'id-1');
        $this->assertIsCallable(
            $dataProcessing->getProcessor()
        );
    }
}
