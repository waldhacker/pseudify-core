<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Processor\Encoder;

use PHPUnit\Framework\TestCase;
use Waldhacker\Pseudify\Core\Processor\Encoder\CsvEncoder;

class CsvEncoderTest extends TestCase
{
    public function testDecodeReturnsArrayWithValidInputWithConstructorOptions(): void
    {
        $converter = new CsvEncoder([CsvEncoder::NO_HEADERS_KEY => true]);

        $this->assertEquals(
            [[1, 2, 3], [4, 5, 6]],
            $converter->decode('1,2,3'.PHP_EOL.'4,5,6')
        );
    }

    public function testDecodeReturnsArrayWithValidInputWithDecodeOptions(): void
    {
        $converter = new CsvEncoder();

        $this->assertEquals(
            [[1, 2, 3], [4, 5, 6]],
            $converter->decode('1,2,3'.PHP_EOL.'4,5,6', [CsvEncoder::NO_HEADERS_KEY => true])
        );
    }

    public function testEncodeReturnsStringWithValidInputWithConstructorOptions(): void
    {
        $converter = new CsvEncoder([CsvEncoder::NO_HEADERS_KEY => true]);

        $this->assertEquals(
            '1,2,3'.PHP_EOL.'4,5,6'.PHP_EOL,
            $converter->encode([[1, 2, 3], [4, 5, 6]])
        );
    }

    public function testEncodeReturnsStringWithValidInputWithEncodeOptions(): void
    {
        $converter = new CsvEncoder();

        $this->assertEquals(
            '1,2,3'.PHP_EOL.'4,5,6'.PHP_EOL,
            $converter->encode([[1, 2, 3], [4, 5, 6]], [CsvEncoder::NO_HEADERS_KEY => true])
        );
    }
}
