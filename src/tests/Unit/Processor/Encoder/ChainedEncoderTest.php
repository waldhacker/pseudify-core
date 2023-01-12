<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Processor\Encoder;

use PHPUnit\Framework\TestCase;
use Waldhacker\Pseudify\Core\Processor\Encoder\Base64Encoder;
use Waldhacker\Pseudify\Core\Processor\Encoder\ChainedEncoder;
use Waldhacker\Pseudify\Core\Processor\Encoder\HexEncoder;
use Waldhacker\Pseudify\Core\Processor\Encoder\MissingEncoderException;

class ChainedEncoderTest extends TestCase
{
    public function testDecodeReturnsPlainText(): void
    {
        $converter = new ChainedEncoder([new HexEncoder(), new Base64Encoder()]);

        $this->assertEquals(
            'foo',
            $converter->decode(bin2hex(base64_encode('foo')))
        );
    }

    public function testEncodeReturnsPlainText(): void
    {
        $converter = new ChainedEncoder([new HexEncoder(), new Base64Encoder()]);

        $this->assertEquals(
            bin2hex(base64_encode('foo')),
            $converter->encode('foo')
        );
    }

    public function testConstructorFilterValidEncoders(): void
    {
        $encoder1 = new HexEncoder();
        $encoder2 = new Base64Encoder();
        $converter = new ChainedEncoder([$encoder1, new \stdClass(), $encoder2]);

        $this->assertEquals(
            [$encoder1, $encoder2],
            $converter->getEncoders()
        );
    }

    public function testGetEncoderReturnsExistingEncoder(): void
    {
        $encoder1 = new HexEncoder();
        $encoder2 = new Base64Encoder();
        $converter = new ChainedEncoder([$encoder1, $encoder2]);

        $this->assertEquals(
            $encoder2,
            $converter->getEncoder(1)
        );
    }

    public function testGetEncoderThrowsExceptionIfEncoderDoesNotExists(): void
    {
        $this->expectException(MissingEncoderException::class);
        $this->expectExceptionCode(1621656967);

        $encoder1 = new HexEncoder();
        $encoder2 = new Base64Encoder();
        $converter = new ChainedEncoder([$encoder1, $encoder2]);

        $this->assertEquals(
            null,
            $converter->getEncoder(2)
        );
    }

    public function testAddEncoderAddsEncoder(): void
    {
        $encoder1 = new HexEncoder();
        $encoder2 = new Base64Encoder();
        $converter = new ChainedEncoder([$encoder1]);

        $converter->addEncoder($encoder2);

        $this->assertEquals(
            [$encoder1, $encoder2],
            $converter->getEncoders()
        );
    }

    public function testRemoveEncoderRemovesEncoder(): void
    {
        $encoder1 = new HexEncoder();
        $encoder2 = new Base64Encoder();
        $converter = new ChainedEncoder([$encoder1, $encoder2]);

        $converter->removeEncoder(0);

        $this->assertEquals(
            [$encoder2],
            $converter->getEncoders()
        );
    }
}
