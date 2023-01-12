<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Processor\Encoder;

use PHPUnit\Framework\TestCase;
use Waldhacker\Pseudify\Core\Processor\Encoder\GzEncodeEncoder;

class GzEncodeEncoderTest extends TestCase
{
    public function testDecodeReturnsPlainTextWithValidInput(): void
    {
        $converter = new GzEncodeEncoder();

        $this->assertEquals(
            'foo',
            $converter->decode(gzencode('foo'))
        );
    }

    public function testDecodeReturnsFalseWithInvalidInput(): void
    {
        $converter = new GzEncodeEncoder();

        $this->assertEquals(
            false,
            $converter->decode('ddd')
        );
    }

    public function testDecodeReturnsFalseWithInvalidConstructorOptions(): void
    {
        $converter = new GzEncodeEncoder([GzEncodeEncoder::DECODE_MAX_LENGTH => 1]);

        $this->assertEquals(
            false,
            $converter->decode(gzencode('foo'))
        );
    }

    public function testEncodeReturnsCompressedDataWithValidInput(): void
    {
        $converter = new GzEncodeEncoder();
        $this->assertEquals(
            gzencode('foo'),
            $converter->encode('foo')
        );
    }

    public function testEncodeReturnsFalseWithInvalidConstructorOptions(): void
    {
        $converter = new GzEncodeEncoder([GzEncodeEncoder::ENCODE_LEVEL => 999]);

        $this->assertEquals(
            false,
            $converter->encode('foo')
        );
    }

    public function testEncodeReturnsFalseWithNonStringInput(): void
    {
        $converter = new GzEncodeEncoder();

        $this->assertEquals(
            false,
            $converter->encode(1)
        );
    }
}
