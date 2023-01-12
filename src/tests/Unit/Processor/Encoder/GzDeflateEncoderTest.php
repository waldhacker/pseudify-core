<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Processor\Encoder;

use PHPUnit\Framework\TestCase;
use Waldhacker\Pseudify\Core\Processor\Encoder\GzDeflateEncoder;

class GzDeflateEncoderTest extends TestCase
{
    public function testDecodeReturnsPlainTextWithValidInput(): void
    {
        $converter = new GzDeflateEncoder();

        $this->assertEquals(
            'foo',
            $converter->decode(gzdeflate('foo'))
        );
    }

    public function testDecodeReturnsFalseWithInvalidInput(): void
    {
        $converter = new GzDeflateEncoder();

        $this->assertEquals(
            false,
            $converter->decode('ddd')
        );
    }

    public function testDecodeReturnsFalseWithInvalidConstructorOptions(): void
    {
        $converter = new GzDeflateEncoder([GzDeflateEncoder::DECODE_MAX_LENGTH => 1]);

        $this->assertEquals(
            false,
            $converter->decode(gzdeflate('foo'))
        );
    }

    public function testEncodeReturnsCompressedDataWithValidInput(): void
    {
        $converter = new GzDeflateEncoder();
        $this->assertEquals(
            gzdeflate('foo'),
            $converter->encode('foo')
        );
    }

    public function testEncodeReturnsFalseWithInvalidConstructorOptions(): void
    {
        $converter = new GzDeflateEncoder([GzDeflateEncoder::ENCODE_LEVEL => 999]);

        $this->assertEquals(
            false,
            $converter->encode('foo')
        );
    }

    public function testEncodeReturnsFalseWithNonStringInput(): void
    {
        $converter = new GzDeflateEncoder();

        $this->assertEquals(
            false,
            $converter->encode(1)
        );
    }
}
