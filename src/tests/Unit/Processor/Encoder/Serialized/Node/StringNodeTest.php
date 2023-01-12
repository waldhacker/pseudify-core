<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Processor\Encoder\Serialized\Node;

use PHPUnit\Framework\TestCase;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Node\StringNode;

class StringNodeTest extends TestCase
{
    public function testGetContentReturnsEmptyString(): void
    {
        $this->assertEquals(
            '',
            (new StringNode(''))->getContent()
        );
    }

    public function testGetValueReturnsEmptyString(): void
    {
        $this->assertEquals(
            '',
            (new StringNode(''))->getValue()
        );
    }

    public function testGetContentReturnsString(): void
    {
        $this->assertEquals(
            'foo',
            (new StringNode('foo'))->getContent()
        );
    }

    public function testGetValueReturnsString(): void
    {
        $this->assertEquals(
            'foo',
            (new StringNode('foo'))->getValue()
        );
    }
}
