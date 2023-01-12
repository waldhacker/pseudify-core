<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Processor\Encoder\Serialized\Node;

use PHPUnit\Framework\TestCase;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Node\AttributeNode;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Node\MissingPropertyException;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Node\ObjectNode;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Node\StringNode;
use Waldhacker\Pseudify\Core\Tests\Unit\Processor\Encoder\Serialized\Fixtures\SimpleObject;

class ObjectNodeTest extends TestCase
{
    public function testGetContentReturnsAttributeNodes(): void
    {
        $member1ValueNode = new StringNode('foo');
        $member2ValueNode = new StringNode('bar');
        $member3ValueNode = new StringNode('baz');

        $member1Node = new AttributeNode($member1ValueNode, 'privateMember', AttributeNode::SCOPE_PRIVATE, SimpleObject::class);
        $member2Node = new AttributeNode($member2ValueNode, 'protectedMember', AttributeNode::SCOPE_PROTECTED, '*');
        $member3Node = new AttributeNode($member3ValueNode, 'publicMember', AttributeNode::SCOPE_PUBLIC);

        $attributeNodes = [
            'privateMember' => $member1Node,
            'protectedMember' => $member2Node,
            'publicMember' => $member3Node,
        ];

        $this->assertEquals(
            $attributeNodes,
            (new ObjectNode($attributeNodes, SimpleObject::class))->getContent()
        );
    }

    public function testGetPropertiesReturnsAttributeNodes(): void
    {
        $member1ValueNode = new StringNode('foo');
        $member2ValueNode = new StringNode('bar');
        $member3ValueNode = new StringNode('baz');

        $member1Node = new AttributeNode($member1ValueNode, 'privateMember', AttributeNode::SCOPE_PRIVATE, SimpleObject::class);
        $member2Node = new AttributeNode($member2ValueNode, 'protectedMember', AttributeNode::SCOPE_PROTECTED, '*');
        $member3Node = new AttributeNode($member3ValueNode, 'publicMember', AttributeNode::SCOPE_PUBLIC);

        $attributeNodes = [
            'privateMember' => $member1Node,
            'protectedMember' => $member2Node,
            'publicMember' => $member3Node,
        ];

        $this->assertEquals(
            $attributeNodes,
            (new ObjectNode($attributeNodes, SimpleObject::class))->getProperties()
        );
    }

    public function testHasPropertyReturnsTrueIfPropertyExists(): void
    {
        $member1ValueNode = new StringNode('foo');
        $member2ValueNode = new StringNode('bar');
        $member3ValueNode = new StringNode('baz');

        $member1Node = new AttributeNode($member1ValueNode, 'privateMember', AttributeNode::SCOPE_PRIVATE, SimpleObject::class);
        $member2Node = new AttributeNode($member2ValueNode, 'protectedMember', AttributeNode::SCOPE_PROTECTED, '*');
        $member3Node = new AttributeNode($member3ValueNode, 'publicMember', AttributeNode::SCOPE_PUBLIC);

        $attributeNodes = [
            'privateMember' => $member1Node,
            'protectedMember' => $member2Node,
            'publicMember' => $member3Node,
        ];

        $this->assertEquals(
            true,
            (new ObjectNode($attributeNodes, SimpleObject::class))->hasProperty('privateMember')
        );
    }

    public function testHasPropertyReturnsFalseIfPropertyNotExists(): void
    {
        $member1ValueNode = new StringNode('foo');
        $member2ValueNode = new StringNode('bar');
        $member3ValueNode = new StringNode('baz');

        $member1Node = new AttributeNode($member1ValueNode, 'privateMember', AttributeNode::SCOPE_PRIVATE, SimpleObject::class);
        $member2Node = new AttributeNode($member2ValueNode, 'protectedMember', AttributeNode::SCOPE_PROTECTED, '*');
        $member3Node = new AttributeNode($member3ValueNode, 'publicMember', AttributeNode::SCOPE_PUBLIC);

        $attributeNodes = [
            'privateMember' => $member1Node,
            'protectedMember' => $member2Node,
            'publicMember' => $member3Node,
        ];

        $this->assertEquals(
            false,
            (new ObjectNode($attributeNodes, SimpleObject::class))->hasProperty('invalid')
        );
    }

    public function testGetPropertyReturnsAttributeNodeIfPropertyExists(): void
    {
        $member1ValueNode = new StringNode('foo');
        $member2ValueNode = new StringNode('bar');
        $member3ValueNode = new StringNode('baz');

        $member1Node = new AttributeNode($member1ValueNode, 'privateMember', AttributeNode::SCOPE_PRIVATE, SimpleObject::class);
        $member2Node = new AttributeNode($member2ValueNode, 'protectedMember', AttributeNode::SCOPE_PROTECTED, '*');
        $member3Node = new AttributeNode($member3ValueNode, 'publicMember', AttributeNode::SCOPE_PUBLIC);

        $attributeNodes = [
            'privateMember' => $member1Node,
            'protectedMember' => $member2Node,
            'publicMember' => $member3Node,
        ];

        $this->assertEquals(
            $member1Node,
            (new ObjectNode($attributeNodes, SimpleObject::class))->getProperty('privateMember')
        );
    }

    public function testGetPropertyThrowsExceptionIfPropertyNotExists(): void
    {
        $this->expectException(MissingPropertyException::class);
        $this->expectExceptionCode(1621657000);

        $member1ValueNode = new StringNode('foo');
        $member2ValueNode = new StringNode('bar');
        $member3ValueNode = new StringNode('baz');

        $member1Node = new AttributeNode($member1ValueNode, 'privateMember', AttributeNode::SCOPE_PRIVATE, SimpleObject::class);
        $member2Node = new AttributeNode($member2ValueNode, 'protectedMember', AttributeNode::SCOPE_PROTECTED, '*');
        $member3Node = new AttributeNode($member3ValueNode, 'publicMember', AttributeNode::SCOPE_PUBLIC);

        $attributeNodes = [
            'privateMember' => $member1Node,
            'protectedMember' => $member2Node,
            'publicMember' => $member3Node,
        ];

        $this->assertEquals(
            null,
            (new ObjectNode($attributeNodes, SimpleObject::class))->getProperty('invalid')
        );
    }

    public function testGetPropertyContentReturnsAttributeContentNodeIfPropertyExists(): void
    {
        $member1ValueNode = new StringNode('foo');
        $member2ValueNode = new StringNode('bar');
        $member3ValueNode = new StringNode('baz');

        $member1Node = new AttributeNode($member1ValueNode, 'privateMember', AttributeNode::SCOPE_PRIVATE, SimpleObject::class);
        $member2Node = new AttributeNode($member2ValueNode, 'protectedMember', AttributeNode::SCOPE_PROTECTED, '*');
        $member3Node = new AttributeNode($member3ValueNode, 'publicMember', AttributeNode::SCOPE_PUBLIC);

        $attributeNodes = [
            'privateMember' => $member1Node,
            'protectedMember' => $member2Node,
            'publicMember' => $member3Node,
        ];

        $this->assertEquals(
            $member1ValueNode,
            (new ObjectNode($attributeNodes, SimpleObject::class))->getPropertyContent('privateMember')
        );
    }

    public function testGetPropertyContentThrowsExceptionIfPropertyNotExists(): void
    {
        $this->expectException(MissingPropertyException::class);
        $this->expectExceptionCode(1621657001);

        $member1ValueNode = new StringNode('foo');
        $member2ValueNode = new StringNode('bar');
        $member3ValueNode = new StringNode('baz');

        $member1Node = new AttributeNode($member1ValueNode, 'privateMember', AttributeNode::SCOPE_PRIVATE, SimpleObject::class);
        $member2Node = new AttributeNode($member2ValueNode, 'protectedMember', AttributeNode::SCOPE_PROTECTED, '*');
        $member3Node = new AttributeNode($member3ValueNode, 'publicMember', AttributeNode::SCOPE_PUBLIC);

        $attributeNodes = [
            'privateMember' => $member1Node,
            'protectedMember' => $member2Node,
            'publicMember' => $member3Node,
        ];

        $this->assertEquals(
            null,
            (new ObjectNode($attributeNodes, SimpleObject::class))->getPropertyContent('invalid')
        );
    }

    public function testGetPropertiesContentsReturnsAttributeContentNodes(): void
    {
        $member1ValueNode = new StringNode('foo');
        $member2ValueNode = new StringNode('bar');
        $member3ValueNode = new StringNode('baz');

        $member1Node = new AttributeNode($member1ValueNode, 'privateMember', AttributeNode::SCOPE_PRIVATE, SimpleObject::class);
        $member2Node = new AttributeNode($member2ValueNode, 'protectedMember', AttributeNode::SCOPE_PROTECTED, '*');
        $member3Node = new AttributeNode($member3ValueNode, 'publicMember', AttributeNode::SCOPE_PUBLIC);

        $attributeNodes = [
            'privateMember' => $member1Node,
            'protectedMember' => $member2Node,
            'publicMember' => $member3Node,
        ];

        $this->assertEquals(
            [
                'privateMember' => $member1ValueNode,
                'protectedMember' => $member2ValueNode,
                'publicMember' => $member3ValueNode,
            ],
            (new ObjectNode($attributeNodes, SimpleObject::class))->getPropertiesContents()
        );
    }

    public function testGetClassNameReturnsClassName(): void
    {
        $this->assertEquals(
            SimpleObject::class,
            (new ObjectNode([], SimpleObject::class))->getClassName()
        );
    }

    public function testReplacePropertyReplacesAttributeNode(): void
    {
        $member1ValueNode = new StringNode('foo');
        $member2ValueNode = new StringNode('bar');
        $member3ValueNode = new StringNode('baz');
        $member4ValueNode = new StringNode('new1');
        $member5ValueNode = new StringNode('new2');
        $member6ValueNode = new StringNode('new3');

        $member1Node = new AttributeNode($member1ValueNode, 'privateMember', AttributeNode::SCOPE_PRIVATE, SimpleObject::class);
        $member2Node = new AttributeNode($member2ValueNode, 'protectedMember', AttributeNode::SCOPE_PROTECTED, '*');
        $member3Node = new AttributeNode($member3ValueNode, 'publicMember', AttributeNode::SCOPE_PUBLIC);

        $attributeNodes = [
            'privateMember' => $member1Node,
            'protectedMember' => $member2Node,
            'publicMember' => $member3Node,
        ];

        $objectNode = new ObjectNode($attributeNodes, SimpleObject::class);

        $this->assertEquals(
            $member1ValueNode,
            $objectNode->getProperty('privateMember')->getContent()
        );

        $objectNode->replaceProperty('privateMember', $member4ValueNode);
        $objectNode->replaceProperty('protectedMember', $member5ValueNode);
        $objectNode->replaceProperty('publicMember', $member6ValueNode);

        $this->assertEquals(
            $member4ValueNode,
            $objectNode->getProperty('privateMember')->getContent()
        );
        $this->assertEquals(
            $member5ValueNode,
            $objectNode->getProperty('protectedMember')->getContent()
        );
        $this->assertEquals(
            $member6ValueNode,
            $objectNode->getProperty('publicMember')->getContent()
        );

        $this->assertEquals(
            SimpleObject::class,
            $objectNode->getProperty('privateMember')->getClassName()
        );
        $this->assertEquals(
            '*',
            $objectNode->getProperty('protectedMember')->getClassName()
        );
        $this->assertEquals(
            null,
            $objectNode->getProperty('publicMember')->getClassName()
        );

        $this->assertEquals(
            'privateMember',
            $objectNode->getProperty('privateMember')->getPropertyName()
        );
        $this->assertEquals(
            'protectedMember',
            $objectNode->getProperty('protectedMember')->getPropertyName()
        );
        $this->assertEquals(
            'publicMember',
            $objectNode->getProperty('publicMember')->getPropertyName()
        );

        $this->assertEquals(
            AttributeNode::SCOPE_PRIVATE,
            $objectNode->getProperty('privateMember')->getScope()
        );
        $this->assertEquals(
            AttributeNode::SCOPE_PROTECTED,
            $objectNode->getProperty('protectedMember')->getScope()
        );
        $this->assertEquals(
            AttributeNode::SCOPE_PUBLIC,
            $objectNode->getProperty('publicMember')->getScope()
        );

        $this->assertEquals(
            $objectNode,
            $objectNode->getProperty('privateMember')->getParent()
        );
        $this->assertEquals(
            $objectNode,
            $objectNode->getProperty('protectedMember')->getParent()
        );
        $this->assertEquals(
            $objectNode,
            $objectNode->getProperty('publicMember')->getParent()
        );
    }

    public function testReplacePropertyThrowsExceptionIfPropertyDoesNotExists(): void
    {
        $this->expectException(MissingPropertyException::class);
        $this->expectExceptionCode(1621657000);

        $member1ValueNode = new StringNode('foo');
        $member2ValueNode = new StringNode('bar');
        $member3ValueNode = new StringNode('baz');
        $member4ValueNode = new StringNode('new');

        $member1Node = new AttributeNode($member1ValueNode, 'privateMember', AttributeNode::SCOPE_PRIVATE, SimpleObject::class);
        $member2Node = new AttributeNode($member2ValueNode, 'protectedMember', AttributeNode::SCOPE_PROTECTED, '*');
        $member3Node = new AttributeNode($member3ValueNode, 'publicMember', AttributeNode::SCOPE_PUBLIC);

        $attributeNodes = [
            'privateMember' => $member1Node,
            'protectedMember' => $member2Node,
            'publicMember' => $member3Node,
        ];

        $objectNode = new ObjectNode($attributeNodes, SimpleObject::class);

        $this->assertEquals(
            $member1ValueNode,
            $objectNode->getProperty('privateMember')->getContent()
        );

        $objectNode->replaceProperty('invalid', $member4ValueNode);

        $this->assertEquals(
            null,
            $objectNode->getProperty('invalid')
        );
    }
}
