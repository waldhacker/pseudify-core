<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Processor\Encoder;

use PHPUnit\Framework\TestCase;
use Waldhacker\Pseudify\Core\Processor\Encoder\ZlibEncodeEncoder;

class ZlibEncodeEncoderTest extends TestCase
{
    public function testDecodeReturnsPlainTextWithValidInput(): void
    {
        $converter = new ZlibEncodeEncoder();

        $this->assertEquals(
            'foo',
            $converter->decode(zlib_encode('foo', ZLIB_ENCODING_RAW, -1))
        );
    }

    public function testDecodeReturnsFalseWithInvalidInput(): void
    {
        $converter = new ZlibEncodeEncoder();

        $this->assertEquals(
            false,
            $converter->decode('ddd')
        );
    }

    public function testDecodeReturnsFalseWithInvalidConstructorOptions(): void
    {
        $converter = new ZlibEncodeEncoder([ZlibEncodeEncoder::DECODE_MAX_LENGTH => 1]);

        $this->assertEquals(
            false,
            $converter->decode(zlib_encode('foo', ZLIB_ENCODING_RAW, -1))
        );
    }

    public function testEncodeReturnsCompressedDataWithValidInput(): void
    {
        $converter = new ZlibEncodeEncoder();
        $this->assertEquals(
            zlib_encode('foo', ZLIB_ENCODING_RAW, -1),
            $converter->encode('foo')
        );
    }

    public function testEncodeReturnsFalseWithInvalidConstructorOptions(): void
    {
        $converter = new ZlibEncodeEncoder([ZlibEncodeEncoder::ENCODE_LEVEL => 999]);

        $this->assertEquals(
            false,
            $converter->encode('foo')
        );
    }

    public function testEncodeReturnsFalseWithNonStringInput(): void
    {
        $converter = new ZlibEncodeEncoder();

        $this->assertEquals(
            false,
            $converter->encode(1)
        );
    }
}
