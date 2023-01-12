<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Processor\Encoder\Serialized\Node;

use PHPUnit\Framework\TestCase;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Node\ArrayElementNode;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Node\IntegerNode;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Node\StringNode;

class ArrayElementNodeTest extends TestCase
{
    public function testGetContentReturnsContentNode(): void
    {
        $contentNode = new StringNode('foo');
        $this->assertEquals(
            $contentNode,
            (new ArrayElementNode($contentNode, new IntegerNode(0)))->getContent()
        );
    }

    public function testGetKeyReturnsKeyNode(): void
    {
        $keyNode = new StringNode('bar');
        $this->assertEquals(
            $keyNode,
            (new ArrayElementNode(new StringNode('foo'), $keyNode))->getKey()
        );
    }

    public function testGetPropertyNameReturnsKeyValue(): void
    {
        $keyNode = new StringNode('bar');
        $this->assertEquals(
            'bar',
            (new ArrayElementNode(new StringNode('foo'), $keyNode))->getPropertyName()
        );
    }
}
