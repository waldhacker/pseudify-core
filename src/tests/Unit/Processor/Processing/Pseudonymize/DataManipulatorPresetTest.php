<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Processing\Pseudonymize;

use PHPUnit\Framework\TestCase;
use Waldhacker\Pseudify\Core\Faker\Faker;
use Waldhacker\Pseudify\Core\Processor\Processing\Pseudonymize\DataManipulatorContext;
use Waldhacker\Pseudify\Core\Processor\Processing\Pseudonymize\DataManipulatorPreset;

class DataManipulatorPresetTest extends TestCase
{
    public function testScalardataManipulatorReturnsDefaultScopedFakeData(): void
    {
        $fakerMock = $this->createPartialMock(Faker::class, ['withScope', 'withSource', '__call']);

        $fakerMock->expects($this->once())->method('withScope')->with(Faker::DEFAULT_SCOPE)->will($this->returnSelf());
        $fakerMock->expects($this->once())->method('withSource')->with(null)->will($this->returnSelf());
        $fakerMock->expects($this->once())->method('__call')->with('username', [])->will($this->returnValue('foo'));

        $dataProcessing = DataManipulatorPreset::scalarData(fakerFormatter: 'username');
        $processor = $dataProcessing->getProcessor();
        $dataManipulatorContext = new DataManipulatorContext($fakerMock, null, null, []);

        $processor($dataManipulatorContext);
        $this->assertEquals(
            'foo',
            $dataManipulatorContext->getProcessedData()
        );
    }

    public function testScalardataManipulatorReturnsCustomScopedFakeData(): void
    {
        $scope = 'fe-user';
        $fakerMock = $this->createPartialMock(Faker::class, ['withScope', 'withSource', '__call']);

        $fakerMock->expects($this->once())->method('withScope')->with($scope)->will($this->returnSelf());
        $fakerMock->expects($this->once())->method('withSource')->with(null)->will($this->returnSelf());
        $fakerMock->expects($this->once())->method('__call')->with('username', [])->will($this->returnValue('foo'));

        $dataProcessing = DataManipulatorPreset::scalarData(scope: $scope, fakerFormatter: 'username');
        $processor = $dataProcessing->getProcessor();
        $dataManipulatorContext = new DataManipulatorContext($fakerMock, null, null, []);

        $processor($dataManipulatorContext);
        $this->assertEquals(
            'foo',
            $dataManipulatorContext->getProcessedData()
        );
    }

    public function testScalardataManipulatorReturnsCustomScopedCustomConfiguredFakeData(): void
    {
        $scope = 'fe-user';
        $fakerArguments = [1, 3, true];
        $fakerMock = $this->createPartialMock(Faker::class, ['withScope', 'withSource', '__call']);

        $fakerMock->expects($this->once())->method('withScope')->with($scope)->will($this->returnSelf());
        $fakerMock->expects($this->once())->method('withSource')->with(null)->will($this->returnSelf());
        $fakerMock->expects($this->once())->method('__call')->with('username', $fakerArguments)->will($this->returnValue('foo'));

        $dataProcessing = DataManipulatorPreset::scalarData(scope: $scope, fakerFormatter: 'username', fakerArguments: $fakerArguments);
        $processor = $dataProcessing->getProcessor();
        $dataManipulatorContext = new DataManipulatorContext($fakerMock, null, null, []);

        $processor($dataManipulatorContext);
        $this->assertEquals(
            'foo',
            $dataManipulatorContext->getProcessedData()
        );
    }
}
