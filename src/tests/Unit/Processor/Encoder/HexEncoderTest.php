<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Processor\Encoder;

use PHPUnit\Framework\TestCase;
use Waldhacker\Pseudify\Core\Processor\Encoder\HexEncoder;

class HexEncoderTest extends TestCase
{
    public function testDecodeReturnsPlainTextWithValidInput(): void
    {
        $converter = new HexEncoder();
        $this->assertEquals(
            'foo',
            $converter->decode(bin2hex('foo'))
        );
    }

    public function testDecodeReturnsFalseWithInvalidInput(): void
    {
        $converter = new HexEncoder();
        $this->assertEquals(
            false,
            $converter->decode('$$$')
        );
    }

    public function testEncodeReturnsHexString(): void
    {
        $converter = new HexEncoder();
        $this->assertEquals(
            bin2hex('foo'),
            $converter->encode('foo')
        );
    }
}
