<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Processor\Encoder;

use PHPUnit\Framework\TestCase;
use Waldhacker\Pseudify\Core\Processor\Encoder\XmlEncoder;

class XmlEncoderTest extends TestCase
{
    public function testDecodeReturnsArrayWithValidInput(): void
    {
        $converter = new XmlEncoder();

        $this->assertEquals(
            [0 => 'foo'],
            $converter->decode('<?xml version="1.0"?>'.PHP_EOL.'<response>foo</response>'.PHP_EOL)
        );
    }

    public function testEncodeReturnsStringWithValidInput(): void
    {
        $converter = new XmlEncoder();

        $this->assertEquals(
            '<?xml version="1.0"?>'.PHP_EOL.'<response><item key="0">foo</item></response>'.PHP_EOL,
            $converter->encode([0 => 'foo'])
        );
    }
}
