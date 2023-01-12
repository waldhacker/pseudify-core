<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Processor\Encoder;

use PHPUnit\Framework\TestCase;
use Waldhacker\Pseudify\Core\Processor\Encoder\Base64Encoder;

class Base64EncoderTest extends TestCase
{
    public function testDecodeReturnsPlainTextWithValidInput(): void
    {
        $converter = new Base64Encoder();
        $this->assertEquals(
            'foo',
            $converter->decode(base64_encode('foo'))
        );
    }

    public function testDecodeReturnsFalseWithInvalidInput(): void
    {
        $converter = new Base64Encoder();
        $this->assertEquals(
            false,
            $converter->decode('$$$')
        );
    }

    public function testDecodeReturnsPlainTextWithInputWithInvalidChars(): void
    {
        $converter = new Base64Encoder();
        $this->assertEquals(
            'foo',
            $converter->decode(base64_encode('foo').'$$$')
        );
    }

    public function testDecodeReturnsFalseWithInputWithInvalidCharsWithConstructorStrictMode(): void
    {
        $converter = new Base64Encoder([Base64Encoder::DECODE_STRICT => true]);
        $this->assertEquals(
            false,
            $converter->decode(base64_encode('foo').'$$$')
        );
    }

    public function testDecodeReturnsFalseWithInputWithInvalidCharsWithDecodeStrictMode(): void
    {
        $converter = new Base64Encoder();
        $this->assertEquals(
            false,
            $converter->decode(base64_encode('foo').'$$$', [Base64Encoder::DECODE_STRICT => true])
        );
    }

    public function testEncodeReturnsBase64String(): void
    {
        $converter = new Base64Encoder();
        $this->assertEquals(
            base64_encode('foo'),
            $converter->encode('foo')
        );
    }
}
