<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Processor\Encoder\Serialized\Node;

use PHPUnit\Framework\TestCase;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Node\ArrayElementNode;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Node\ArrayNode;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Node\IntegerNode;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Node\MissingPropertyException;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Node\StringNode;

class ArrayNodeTest extends TestCase
{
    public function testGetContentReturnsArrayElementNodes(): void
    {
        $arrayElementNodes = [
            'foo' => new ArrayElementNode(new IntegerNode(1), new StringNode('foo')),
            'bar' => new ArrayElementNode(new IntegerNode(2), new StringNode('bar')),
        ];

        $this->assertEquals(
            $arrayElementNodes,
            (new ArrayNode($arrayElementNodes))->getContent()
        );
    }

    public function testGetPropertiesReturnsArrayElementNodes(): void
    {
        $arrayElementNodes = [
            'foo' => new ArrayElementNode(new IntegerNode(1), new StringNode('foo')),
            'bar' => new ArrayElementNode(new IntegerNode(2), new StringNode('bar')),
        ];

        $this->assertEquals(
            $arrayElementNodes,
            (new ArrayNode($arrayElementNodes))->getProperties()
        );
    }

    public function testHasPropertyReturnsTrueIfIndexedPropertyExists(): void
    {
        $arrayElementNodes = [
            'foo' => new ArrayElementNode(new IntegerNode(1), new StringNode('foo')),
            0 => new ArrayElementNode(new IntegerNode(2), new IntegerNode(0)),
        ];

        $this->assertEquals(
            true,
            (new ArrayNode($arrayElementNodes))->hasProperty(0)
        );
    }

    public function testHasPropertyReturnsFalseIfIndexedPropertyNotExists(): void
    {
        $arrayElementNodes = [
            'foo' => new ArrayElementNode(new IntegerNode(1), new StringNode('foo')),
            0 => new ArrayElementNode(new IntegerNode(2), new IntegerNode(0)),
        ];

        $this->assertEquals(
            false,
            (new ArrayNode($arrayElementNodes))->hasProperty(1)
        );
    }

    public function testHasPropertyReturnsTrueIfAssociativePropertyExists(): void
    {
        $arrayElementNodes = [
            'foo' => new ArrayElementNode(new IntegerNode(1), new StringNode('foo')),
            0 => new ArrayElementNode(new IntegerNode(2), new IntegerNode(0)),
        ];

        $this->assertEquals(
            true,
            (new ArrayNode($arrayElementNodes))->hasProperty('foo')
        );
    }

    public function testHasPropertyReturnsFalseIfAssociativePropertyNotExists(): void
    {
        $arrayElementNodes = [
            'foo' => new ArrayElementNode(new IntegerNode(1), new StringNode('foo')),
            0 => new ArrayElementNode(new IntegerNode(2), new IntegerNode(0)),
        ];

        $this->assertEquals(
            false,
            (new ArrayNode($arrayElementNodes))->hasProperty('bar')
        );
    }

    public function testGetPropertyReturnsArrayElementNodeIfIndexedPropertyExists(): void
    {
        $arrayElementNodes = [
            'foo' => new ArrayElementNode(new IntegerNode(1), new StringNode('foo')),
            0 => new ArrayElementNode(new IntegerNode(2), new IntegerNode(0)),
        ];

        $this->assertEquals(
            $arrayElementNodes[0],
            (new ArrayNode($arrayElementNodes))->getProperty(0)
        );
    }

    public function testGetPropertyThrowsExceptionIfIndexedPropertyNotExists(): void
    {
        $this->expectException(MissingPropertyException::class);
        $this->expectExceptionCode(1621657002);

        $arrayElementNodes = [
            'foo' => new ArrayElementNode(new IntegerNode(1), new StringNode('foo')),
            0 => new ArrayElementNode(new IntegerNode(2), new IntegerNode(0)),
        ];

        $this->assertEquals(
            null,
            (new ArrayNode($arrayElementNodes))->getProperty(1)
        );
    }

    public function testGetPropertyReturnsArrayElementNodeIfAssociativePropertyExists(): void
    {
        $arrayElementNodes = [
            'foo' => new ArrayElementNode(new IntegerNode(1), new StringNode('foo')),
            0 => new ArrayElementNode(new IntegerNode(2), new IntegerNode(0)),
        ];

        $this->assertEquals(
            $arrayElementNodes['foo'],
            (new ArrayNode($arrayElementNodes))->getProperty('foo')
        );
    }

    public function testGetPropertyThrowsExceptionIfAssociativePropertyNotExists(): void
    {
        $this->expectException(MissingPropertyException::class);
        $this->expectExceptionCode(1621657002);

        $arrayElementNodes = [
            'foo' => new ArrayElementNode(new IntegerNode(1), new StringNode('foo')),
            0 => new ArrayElementNode(new IntegerNode(2), new IntegerNode(0)),
        ];

        $this->assertEquals(
            null,
            (new ArrayNode($arrayElementNodes))->getProperty('bar')
        );
    }

    public function testGetPropertyContentReturnsArrayElementContentNodeIfIndexedPropertyExists(): void
    {
        $contentNode = new IntegerNode(1);
        $arrayElementNodes = [
            'foo' => new ArrayElementNode(new IntegerNode(2), new StringNode('foo')),
            0 => new ArrayElementNode($contentNode, new IntegerNode(0)),
        ];

        $this->assertEquals(
            $contentNode,
            (new ArrayNode($arrayElementNodes))->getPropertyContent(0)
        );
    }

    public function testGetPropertyContentThrowsExceptionIfIndexedPropertyNotExists(): void
    {
        $this->expectException(MissingPropertyException::class);
        $this->expectExceptionCode(1621657003);

        $arrayElementNodes = [
            'foo' => new ArrayElementNode(new IntegerNode(1), new StringNode('foo')),
            0 => new ArrayElementNode(new IntegerNode(2), new IntegerNode(0)),
        ];

        $this->assertEquals(
            null,
            (new ArrayNode($arrayElementNodes))->getPropertyContent(1)
        );
    }

    public function testGetPropertyContentReturnsArrayElementContentNodeIfAssociativePropertyExists(): void
    {
        $contentNode = new IntegerNode(2);
        $arrayElementNodes = [
            'foo' => new ArrayElementNode($contentNode, new StringNode('foo')),
            0 => new ArrayElementNode(new IntegerNode(1), new IntegerNode(0)),
        ];

        $this->assertEquals(
            $contentNode,
            (new ArrayNode($arrayElementNodes))->getPropertyContent('foo')
        );
    }

    public function testGetPropertyContentThrowsExceptionIfAssociativePropertyNotExists(): void
    {
        $this->expectException(MissingPropertyException::class);
        $this->expectExceptionCode(1621657003);

        $arrayElementNodes = [
            'foo' => new ArrayElementNode(new IntegerNode(1), new StringNode('foo')),
            0 => new ArrayElementNode(new IntegerNode(2), new IntegerNode(0)),
        ];

        $this->assertEquals(
            null,
            (new ArrayNode($arrayElementNodes))->getPropertyContent('bar')
        );
    }

    public function testGetPropertiesContentsReturnsArrayElementContentNodes(): void
    {
        $arrayElementValueNode1 = new IntegerNode(1);
        $arrayElementValueNode2 = new IntegerNode(2);

        $arrayElementNodes = [
            'foo' => new ArrayElementNode($arrayElementValueNode1, new StringNode('foo')),
            0 => new ArrayElementNode($arrayElementValueNode2, new IntegerNode(0)),
        ];

        $this->assertEquals(
            ['foo' => $arrayElementValueNode1, 0 => $arrayElementValueNode2],
            (new ArrayNode($arrayElementNodes))->getPropertiesContents()
        );
    }

    public function testReplacePropertyReplacesArrayElementNode(): void
    {
        $arrayElementKeyNode1 = new StringNode('foo');
        $arrayElementKeyNode2 = new IntegerNode(0);
        $arrayElementValueNode1 = new IntegerNode(1);
        $arrayElementValueNode2 = new IntegerNode(2);
        $arrayElementValueNode3 = new IntegerNode(3);

        $arrayElementNodes = [
            'foo' => new ArrayElementNode($arrayElementValueNode1, $arrayElementKeyNode1),
            0 => new ArrayElementNode($arrayElementValueNode2, $arrayElementKeyNode2),
        ];

        $arrayNode = new ArrayNode($arrayElementNodes);

        $this->assertEquals(
            $arrayElementValueNode1,
            $arrayNode->getProperty('foo')->getContent()
        );

        $arrayNode->replaceProperty('foo', $arrayElementValueNode3);

        $this->assertEquals(
            $arrayElementValueNode3,
            $arrayNode->getProperty('foo')->getContent()
        );

        $this->assertEquals(
            $arrayElementKeyNode1,
            $arrayNode->getProperty('foo')->getKey()
        );

        $this->assertEquals(
            $arrayNode,
            $arrayNode->getProperty('foo')->getParent()
        );
    }

    public function testReplacePropertyThrowsExceptionIfPropertyDoesNotExists(): void
    {
        $this->expectException(MissingPropertyException::class);
        $this->expectExceptionCode(1621657002);

        $arrayElementValueNode1 = new IntegerNode(1);
        $arrayElementValueNode2 = new IntegerNode(2);
        $arrayElementValueNode3 = new IntegerNode(3);

        $arrayElementNodes = [
            'foo' => new ArrayElementNode($arrayElementValueNode1, new StringNode('foo')),
            0 => new ArrayElementNode($arrayElementValueNode2, new IntegerNode(0)),
        ];

        $arrayNode = new ArrayNode($arrayElementNodes);

        $this->assertEquals(
            $arrayElementValueNode1,
            $arrayNode->getProperty('foo')->getContent()
        );

        $arrayNode->replaceProperty('bar', $arrayElementValueNode3);

        $this->assertEquals(
            null,
            $arrayNode->getProperty('bar')
        );
    }
}
