<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Processor\Encoder;

use PHPUnit\Framework\TestCase;
use Waldhacker\Pseudify\Core\Processor\Encoder\ScalarEncoder;

class ScalarEncoderTest extends TestCase
{
    public function testDecodeReturnsInput(): void
    {
        $converter = new ScalarEncoder();

        $this->assertEquals(
            'foo',
            $converter->decode('foo')
        );
    }

    public function testEncodeReturnsInput(): void
    {
        $converter = new ScalarEncoder();
        $this->assertEquals(
            'foo',
            $converter->encode('foo')
        );
    }
}
