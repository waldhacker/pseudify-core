<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Processor\Encoder;

use PHPUnit\Framework\TestCase;
use Waldhacker\Pseudify\Core\Processor\Encoder\YamlEncoder;

class YamlEncoderTest extends TestCase
{
    public function testDecodeReturnsArrayWithValidInput(): void
    {
        $converter = new YamlEncoder();

        $this->assertEquals(
            ['foo' => 1],
            $converter->decode('{ foo: 1 }')
        );
    }

    public function testEncodeReturnsStringWithValidInput(): void
    {
        $converter = new YamlEncoder();

        $this->assertEquals(
            '{ foo: 1 }',
            $converter->encode(['foo' => 1])
        );
    }
}
