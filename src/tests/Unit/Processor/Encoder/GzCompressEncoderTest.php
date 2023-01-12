<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Processor\Encoder;

use PHPUnit\Framework\TestCase;
use Waldhacker\Pseudify\Core\Processor\Encoder\GzCompressEncoder;

class GzCompressEncoderTest extends TestCase
{
    public function testDecodeReturnsPlainTextWithValidInput(): void
    {
        $converter = new GzCompressEncoder();

        $this->assertEquals(
            'foo',
            $converter->decode(gzcompress('foo'))
        );
    }

    public function testDecodeReturnsFalseWithInvalidInput(): void
    {
        $converter = new GzCompressEncoder();

        $this->assertEquals(
            false,
            $converter->decode('ddd')
        );
    }

    public function testDecodeReturnsFalseWithInvalidConstructorOptions(): void
    {
        $converter = new GzCompressEncoder([GzCompressEncoder::DECODE_MAX_LENGTH => 1]);

        $this->assertEquals(
            false,
            $converter->decode(gzcompress('foo'))
        );
    }

    public function testEncodeReturnsCompressedDataWithValidInput(): void
    {
        $converter = new GzCompressEncoder();
        $this->assertEquals(
            gzcompress('foo'),
            $converter->encode('foo')
        );
    }

    public function testEncodeReturnsFalseWithInvalidConstructorOptions(): void
    {
        $converter = new GzCompressEncoder([GzCompressEncoder::ENCODE_LEVEL => 999]);

        $this->assertEquals(
            false,
            $converter->encode('foo')
        );
    }

    public function testEncodeReturnsFalseWithNonStringInput(): void
    {
        $converter = new GzCompressEncoder();

        $this->assertEquals(
            false,
            $converter->encode(1)
        );
    }
}
