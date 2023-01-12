<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Processor\Encoder;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Waldhacker\Pseudify\Core\Processor\Encoder\JsonEncoder;

class JsonEncoderTest extends TestCase
{
    public function testDecodeReturnsArrayWithValidInput(): void
    {
        $converter = new JsonEncoder();

        $this->assertEquals(
            ['foo' => 'oof', 'bar' => 'rab'],
            $converter->decode(json_encode(['foo' => 'oof', 'bar' => 'rab']))
        );
    }

    public function testDecodeReturnsScalarWithValidInput(): void
    {
        $converter = new JsonEncoder();

        $this->assertEquals(
            'foo',
            $converter->decode(json_encode('foo'))
        );
    }

    public function testDecodeThrowsExceptionWithInvalidInput(): void
    {
        $converter = new JsonEncoder();

        $this->expectException(NotEncodableValueException::class);
        $converter->decode('{');
    }

    public function testEncodeReturnsJsonString(): void
    {
        $converter = new JsonEncoder();
        $this->assertEquals(
            json_encode('foo'),
            $converter->encode('foo')
        );
    }
}
