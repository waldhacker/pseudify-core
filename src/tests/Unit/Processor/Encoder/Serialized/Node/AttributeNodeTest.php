<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Processor\Encoder\Serialized\Node;

use PHPUnit\Framework\TestCase;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Node\AttributeNode;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Node\StringNode;

class AttributeNodeTest extends TestCase
{
    public function testGetContentReturnsContentNode(): void
    {
        $contentNode = new StringNode('foo');
        $this->assertEquals(
            $contentNode,
            (new AttributeNode($contentNode, '', ''))->getContent()
        );
    }

    public function testGetClassNameReturnsNull(): void
    {
        $this->assertEquals(
            null,
            (new AttributeNode(new StringNode('foo'), '', ''))->getClassName()
        );
    }

    public function testGetClassNameReturnsString(): void
    {
        $this->assertEquals(
            'foo',
            (new AttributeNode(new StringNode('foo'), '', '', 'foo'))->getClassName()
        );
    }

    public function testGetPropertyNameReturnsString(): void
    {
        $this->assertEquals(
            'foo',
            (new AttributeNode(new StringNode('foo'), 'foo', ''))->getPropertyName()
        );
    }

    public function testGetScopeReturnsString(): void
    {
        $this->assertEquals(
            'foo',
            (new AttributeNode(new StringNode('foo'), '', 'foo'))->getScope()
        );
    }
}
