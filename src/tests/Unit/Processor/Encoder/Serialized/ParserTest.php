<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Processor\Encoder\Serialized;

use PHPUnit\Framework\TestCase;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\InvalidDataException;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\InvalidDataTypeException;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\MissingDataTypeException;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Node;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Node\ArrayElementNode;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Node\ArrayNode;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Node\AttributeNode;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Node\BooleanNode;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Node\FloatNode;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Node\IntegerNode;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Node\NullNode;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Node\ObjectNode;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Node\RecursionByReferenceNode;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Node\RecursionNode;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Node\SerializableObjectNode;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Node\StringNode;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\OutOfBoundsException;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Parser;
use Waldhacker\Pseudify\Core\Tests\Unit\Processor\Encoder\Serialized\Fixtures\SerializableInterfaceObjectWithArrayData;
use Waldhacker\Pseudify\Core\Tests\Unit\Processor\Encoder\Serialized\Fixtures\SerializableInterfaceObjectWithScalarData;
use Waldhacker\Pseudify\Core\Tests\Unit\Processor\Encoder\Serialized\Fixtures\SerializableObjectWithArrayData;
use Waldhacker\Pseudify\Core\Tests\Unit\Processor\Encoder\Serialized\Fixtures\SerializableObjectWithScalarData;
use Waldhacker\Pseudify\Core\Tests\Unit\Processor\Encoder\Serialized\Fixtures\SimpleObject;

class ParserTest extends TestCase
{
    protected $parser;

    public function setUp(): void
    {
        $this->parser = new Parser();
    }

    public function testParseUnparsableSerializedStringThrowsException(): void
    {
        $this->expectException(MissingDataTypeException::class);
        $this->expectExceptionCode(1620887372);
        $this->parser->parse('foo');
    }

    public function testParseThrowsExceptionOnInvalidDataLenghts(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionCode(1620887375);
        $this->parser->parse('s:100:"foo";');
    }

    public function testParseThrowsExceptionOnInvalidDataIndexCalculation(): void
    {
        $parser = new class() extends Parser {
            protected function parseInternal(): Node
            {
                $this->currentIndex = 100;

                return parent::parseInternal();
            }
        };

        $this->expectException(InvalidDataException::class);
        $this->expectExceptionCode(1620887373);
        $parser->parse('s:3:"foo";');
    }

    public function testParseSerializedEmptyStringReturnsStringNode(): void
    {
        $this->assertEquals(
            new StringNode(''),
            $this->parser->parse(serialize(''))
        );
    }

    public function testParseSerializedStringReturnsStringNode(): void
    {
        $this->assertEquals(
            new StringNode('foo'),
            $this->parser->parse(serialize('foo'))
        );
    }

    public function testParseSerializedIntegerReturnsIntegerNode(): void
    {
        $this->assertEquals(
            new IntegerNode(1),
            $this->parser->parse(serialize(1))
        );
    }

    public function testParseSerializedFloatReturnsFloatNode(): void
    {
        $this->assertEquals(
            new FloatNode(1.3),
            $this->parser->parse(serialize(1.3))
        );
    }

    public function testParseSerializedNullReturnsNullNode(): void
    {
        $this->assertEquals(
            new NullNode(),
            $this->parser->parse(serialize(null))
        );
    }

    public function testParseSerializedTrueBoolReturnsBoolNode(): void
    {
        $this->assertEquals(
            new BooleanNode(true),
            $this->parser->parse(serialize(true))
        );
    }

    public function testParseSerializedFalseBoolReturnsBoolNode(): void
    {
        $this->assertEquals(
            new BooleanNode(false),
            $this->parser->parse(serialize(false))
        );
    }

    public function testParseSerializedEmptyArrayReturnsArrayNode(): void
    {
        $this->assertEquals(
            new ArrayNode([]),
            $this->parser->parse(serialize([]))
        );
    }

    public function testParseSerializedIndexedArrayReturnsArrayNode(): void
    {
        $keyNode1 = new IntegerNode(0);
        $valueNode1 = new IntegerNode(1);
        $keyNode2 = new IntegerNode(1);
        $valueNode2 = new IntegerNode(2);

        $arrayElementNodes = [
            0 => new ArrayElementNode($valueNode1, $keyNode1),
            1 => new ArrayElementNode($valueNode2, $keyNode2),
        ];
        $keyNode1->setParent($arrayElementNodes[0]);
        $valueNode1->setParent($arrayElementNodes[0]);
        $keyNode2->setParent($arrayElementNodes[1]);
        $valueNode2->setParent($arrayElementNodes[1]);

        $arrayNode = new ArrayNode($arrayElementNodes);
        $arrayElementNodes[0]->setParent($arrayNode);
        $arrayElementNodes[1]->setParent($arrayNode);

        $this->assertEquals(
            $arrayNode,
            $this->parser->parse(serialize([1, 2]))
        );
    }

    public function testParseSerializedAssociativeArrayReturnsArrayNode(): void
    {
        $keyNode1 = new StringNode('foo');
        $valueNode1 = new IntegerNode(1);
        $keyNode2 = new StringNode('bar');
        $valueNode2 = new IntegerNode(2);

        $arrayElementNodes = [
            'foo' => new ArrayElementNode($valueNode1, $keyNode1),
            'bar' => new ArrayElementNode($valueNode2, $keyNode2),
        ];
        $keyNode1->setParent($arrayElementNodes['foo']);
        $valueNode1->setParent($arrayElementNodes['foo']);
        $keyNode2->setParent($arrayElementNodes['bar']);
        $valueNode2->setParent($arrayElementNodes['bar']);

        $arrayNode = new ArrayNode($arrayElementNodes);
        $arrayElementNodes['foo']->setParent($arrayNode);
        $arrayElementNodes['bar']->setParent($arrayNode);

        $this->assertEquals(
            $arrayNode,
            $this->parser->parse(serialize(['foo' => 1, 'bar' => 2]))
        );
    }

    public function testParseSerializedMixedArrayReturnsArrayNode(): void
    {
        $keyNode1 = new StringNode('foo');
        $valueNode1 = new IntegerNode(1);
        $keyNode2 = new IntegerNode(0);
        $valueNode2 = new IntegerNode(2);

        $arrayElementNodes = [
            'foo' => new ArrayElementNode($valueNode1, $keyNode1),
            0 => new ArrayElementNode($valueNode2, $keyNode2),
        ];
        $keyNode1->setParent($arrayElementNodes['foo']);
        $valueNode1->setParent($arrayElementNodes['foo']);
        $keyNode2->setParent($arrayElementNodes[0]);
        $valueNode2->setParent($arrayElementNodes[0]);

        $arrayNode = new ArrayNode($arrayElementNodes);
        $arrayElementNodes['foo']->setParent($arrayNode);
        $arrayElementNodes[0]->setParent($arrayNode);

        $this->assertEquals(
            $arrayNode,
            $this->parser->parse(serialize(['foo' => 1, 2]))
        );
    }

    public function testParseSerializedMixedNestedArrayReturnsArrayNode(): void
    {
        $keyNode1 = new StringNode('foo');
        $valueNode1 = new IntegerNode(1);

        $keyNode2 = new IntegerNode(0);
        $valueNode2 = new IntegerNode(2);

        $keyNode3 = new IntegerNode(1);
        $valueNode3 = new FloatNode(3.1);

        $keyNode4 = new IntegerNode(2);
        $valueNode4 = new StringNode('bar');

        $keyNode5 = new IntegerNode(3);
        $valueNode5 = new BooleanNode(true);

        $keyNode6 = new IntegerNode(4);
        $valueNode6 = new NullNode();

        $node7KeyNode1 = new StringNode('a');
        $node7ValueNode1 = new StringNode('b');

        $node7arrayElementNodes = [
            'a' => new ArrayElementNode($node7ValueNode1, $node7KeyNode1),
        ];

        $keyNode7 = new IntegerNode(5);
        $valueNode7 = new ArrayNode($node7arrayElementNodes);

        $arrayElementNodes = [
            'foo' => new ArrayElementNode($valueNode1, $keyNode1),
            0 => new ArrayElementNode($valueNode2, $keyNode2),
            1 => new ArrayElementNode($valueNode3, $keyNode3),
            2 => new ArrayElementNode($valueNode4, $keyNode4),
            3 => new ArrayElementNode($valueNode5, $keyNode5),
            4 => new ArrayElementNode($valueNode6, $keyNode6),
            5 => new ArrayElementNode($valueNode7, $keyNode7),
        ];
        $keyNode1->setParent($arrayElementNodes['foo']);
        $valueNode1->setParent($arrayElementNodes['foo']);

        $keyNode2->setParent($arrayElementNodes[0]);
        $valueNode2->setParent($arrayElementNodes[0]);

        $keyNode3->setParent($arrayElementNodes[1]);
        $valueNode3->setParent($arrayElementNodes[1]);

        $keyNode4->setParent($arrayElementNodes[2]);
        $valueNode4->setParent($arrayElementNodes[2]);

        $keyNode5->setParent($arrayElementNodes[3]);
        $valueNode5->setParent($arrayElementNodes[3]);

        $keyNode6->setParent($arrayElementNodes[4]);
        $valueNode6->setParent($arrayElementNodes[4]);

        $keyNode7->setParent($arrayElementNodes[5]);
        $valueNode7->setParent($arrayElementNodes[5]);

        $node7KeyNode1->setParent($node7arrayElementNodes['a']);
        $node7ValueNode1->setParent($node7arrayElementNodes['a']);
        $node7arrayElementNodes['a']->setParent($valueNode7);

        $arrayNode = new ArrayNode($arrayElementNodes);
        $arrayElementNodes['foo']->setParent($arrayNode);
        $arrayElementNodes[0]->setParent($arrayNode);
        $arrayElementNodes[1]->setParent($arrayNode);
        $arrayElementNodes[2]->setParent($arrayNode);
        $arrayElementNodes[3]->setParent($arrayNode);
        $arrayElementNodes[4]->setParent($arrayNode);
        $arrayElementNodes[5]->setParent($arrayNode);

        $this->assertEquals(
            $arrayNode,
            $this->parser->parse(serialize(['foo' => 1, 2, 3.1, 'bar', true, null, ['a' => 'b']]))
        );
    }

    public function testParseSerializedObjectWithNonStringMemberNameThrowsException(): void
    {
        $serializedObject = 'O:86:"Waldhacker\Pseudify\Core\Tests\Unit\Processor\Encoder\Serialized\Fixtures\SimpleObject":3:{i:0;s:3:"foo";i:1;s:3:"bar";i:2;s:3:"baz";}';

        $this->expectException(InvalidDataTypeException::class);
        $this->expectExceptionCode(1620887374);
        $this->parser->parse($serializedObject);
    }

    public function testParseSerializedObjectWithStringValuedMembersReturnsObjectNode(): void
    {
        $member1ValueNode = new StringNode('foo');
        $member2ValueNode = new StringNode('bar');
        $member3ValueNode = new StringNode('baz');

        $member1Node = new AttributeNode($member1ValueNode, 'privateMember', AttributeNode::SCOPE_PRIVATE, SimpleObject::class);
        $member2Node = new AttributeNode($member2ValueNode, 'protectedMember', AttributeNode::SCOPE_PROTECTED, '*');
        $member3Node = new AttributeNode($member3ValueNode, 'publicMember', AttributeNode::SCOPE_PUBLIC);

        $member1ValueNode->setParent($member1Node);
        $member2ValueNode->setParent($member2Node);
        $member3ValueNode->setParent($member3Node);

        $attributeNodes = [
            'privateMember' => $member1Node,
            'protectedMember' => $member2Node,
            'publicMember' => $member3Node,
        ];

        $objectNode = new ObjectNode($attributeNodes, SimpleObject::class);

        $attributeNodes['privateMember']->setParent($objectNode);
        $attributeNodes['protectedMember']->setParent($objectNode);
        $attributeNodes['publicMember']->setParent($objectNode);

        $this->assertEquals(
            $objectNode,
            $this->parser->parse(serialize(new SimpleObject('foo', 'bar', 'baz')))
        );
    }

    public function testParseSerializedObjectWithMixedValuedMembersReturnsObjectNode(): void
    {
        $member1ValueNode = new IntegerNode(123);
        $member2ValueNode = new FloatNode(12.3);
        $member3ValueNode = new BooleanNode(false);

        $member1Node = new AttributeNode($member1ValueNode, 'privateMember', AttributeNode::SCOPE_PRIVATE, SimpleObject::class);
        $member2Node = new AttributeNode($member2ValueNode, 'protectedMember', AttributeNode::SCOPE_PROTECTED, '*');
        $member3Node = new AttributeNode($member3ValueNode, 'publicMember', AttributeNode::SCOPE_PUBLIC);

        $member1ValueNode->setParent($member1Node);
        $member2ValueNode->setParent($member2Node);
        $member3ValueNode->setParent($member3Node);

        $attributeNodes = [
            'privateMember' => $member1Node,
            'protectedMember' => $member2Node,
            'publicMember' => $member3Node,
        ];

        $objectNode = new ObjectNode($attributeNodes, SimpleObject::class);

        $attributeNodes['privateMember']->setParent($objectNode);
        $attributeNodes['protectedMember']->setParent($objectNode);
        $attributeNodes['publicMember']->setParent($objectNode);

        $this->assertEquals(
            $objectNode,
            $this->parser->parse(serialize(new SimpleObject(123, 12.3, false)))
        );
    }

    public function testParseSerializedSerializableInterfaceObjectWithMultipleMembersReturnsSerializableObjectNode(): void
    {
        $keyNode1 = new IntegerNode(0);
        $valueNode1 = new StringNode('foo');
        $keyNode2 = new IntegerNode(1);
        $valueNode2 = new StringNode('bar');
        $keyNode3 = new IntegerNode(2);
        $valueNode3 = new StringNode('baz');

        $arrayElementNodes = [
            0 => new ArrayElementNode($valueNode1, $keyNode1),
            1 => new ArrayElementNode($valueNode2, $keyNode2),
            2 => new ArrayElementNode($valueNode3, $keyNode3),
        ];
        $keyNode1->setParent($arrayElementNodes[0]);
        $valueNode1->setParent($arrayElementNodes[0]);
        $keyNode2->setParent($arrayElementNodes[1]);
        $valueNode2->setParent($arrayElementNodes[1]);
        $keyNode3->setParent($arrayElementNodes[2]);
        $valueNode3->setParent($arrayElementNodes[2]);

        $contentNode = new ArrayNode($arrayElementNodes);
        $arrayElementNodes[0]->setParent($contentNode);
        $arrayElementNodes[1]->setParent($contentNode);
        $arrayElementNodes[2]->setParent($contentNode);

        $objectNode = new SerializableObjectNode($contentNode, SerializableInterfaceObjectWithArrayData::class);

        $contentNode->setParent($objectNode);

        $this->assertEquals(
            $objectNode,
            $this->parser->parse(serialize(new SerializableInterfaceObjectWithArrayData('foo', 'bar', 'baz')))
        );
    }

    public function testParseSerializedSerializableObjectWithMultipleMembersReturnsObjectNode(): void
    {
        $member1ValueNode = new StringNode('foo');
        $member2ValueNode = new StringNode('bar');
        $member3ValueNode = new StringNode('baz');

        $member1Node = new AttributeNode($member1ValueNode, 'privateMember', AttributeNode::SCOPE_PUBLIC);
        $member2Node = new AttributeNode($member2ValueNode, 'protectedMember', AttributeNode::SCOPE_PUBLIC);
        $member3Node = new AttributeNode($member3ValueNode, 'publicMember', AttributeNode::SCOPE_PUBLIC);

        $member1ValueNode->setParent($member1Node);
        $member2ValueNode->setParent($member2Node);
        $member3ValueNode->setParent($member3Node);

        $attributeNodes = [
            'privateMember' => $member1Node,
            'protectedMember' => $member2Node,
            'publicMember' => $member3Node,
        ];

        $objectNode = new ObjectNode($attributeNodes, SerializableObjectWithArrayData::class);

        $attributeNodes['privateMember']->setParent($objectNode);
        $attributeNodes['protectedMember']->setParent($objectNode);
        $attributeNodes['publicMember']->setParent($objectNode);

        $this->assertEquals(
            $objectNode,
            $this->parser->parse(serialize(new SerializableObjectWithArrayData('foo', 'bar', 'baz')))
        );
    }

    public function testParseSerializedSerializableInterfaceObjectWithSingleMemberReturnsSerializableObjectNode(): void
    {
        $contentNode = new StringNode('foo');
        $objectNode = new SerializableObjectNode($contentNode, SerializableInterfaceObjectWithScalarData::class);
        $contentNode->setParent($objectNode);

        $this->assertEquals(
            $objectNode,
            $this->parser->parse(serialize(new SerializableInterfaceObjectWithScalarData('foo')))
        );
    }

    public function testParseSerializedSerializableObjectWithSingleMemberReturnsObjectNode(): void
    {
        $member1ValueNode = new StringNode('foo');
        $member1Node = new AttributeNode($member1ValueNode, 'privateMember', AttributeNode::SCOPE_PUBLIC);
        $member1ValueNode->setParent($member1Node);

        $attributeNodes = [
            'privateMember' => $member1Node,
        ];

        $objectNode = new ObjectNode($attributeNodes, SerializableObjectWithScalarData::class);

        $attributeNodes['privateMember']->setParent($objectNode);

        $this->assertEquals(
            $objectNode,
            $this->parser->parse(serialize(new SerializableObjectWithScalarData('foo')))
        );
    }

    public function testParseSerializedSerializableInterfaceObjectWithNestedMembersReturnsSerializableObjectNode(): void
    {
        $keyNode1 = new IntegerNode(0);
        $valueNode1 = new StringNode('foo');
        $keyNode2 = new IntegerNode(1);
        $valueNode2 = new StringNode('bar');
        $keyNode3 = new IntegerNode(2);
        $valueNode3 = new StringNode('baz');

        $arrayElementNodes = [
            0 => new ArrayElementNode($valueNode1, $keyNode1),
            1 => new ArrayElementNode($valueNode2, $keyNode2),
            2 => new ArrayElementNode($valueNode3, $keyNode3),
        ];
        $keyNode1->setParent($arrayElementNodes[0]);
        $valueNode1->setParent($arrayElementNodes[0]);
        $keyNode2->setParent($arrayElementNodes[1]);
        $valueNode2->setParent($arrayElementNodes[1]);
        $keyNode3->setParent($arrayElementNodes[2]);
        $valueNode3->setParent($arrayElementNodes[2]);

        $contentNode1 = new ArrayNode($arrayElementNodes);
        $arrayElementNodes[0]->setParent($contentNode1);
        $arrayElementNodes[1]->setParent($contentNode1);
        $arrayElementNodes[2]->setParent($contentNode1);

        $objectNode1 = new SerializableObjectNode($contentNode1, SerializableInterfaceObjectWithArrayData::class);

        $contentNode1->setParent($objectNode1);

        $contentNode2 = $objectNode1;
        $objectNode2 = new SerializableObjectNode($contentNode2, SerializableInterfaceObjectWithScalarData::class);
        $contentNode2->setParent($objectNode2);

        $this->assertEquals(
            $objectNode2,
            $this->parser->parse(serialize(new SerializableInterfaceObjectWithScalarData(new SerializableInterfaceObjectWithArrayData('foo', 'bar', 'baz'))))
        );
    }

    public function testParseSerializedSerializableObjectWithNestedMembersReturnsObjectNode(): void
    {
        $nestedMember1ValueNode = new StringNode('foo');
        $nestedMember2ValueNode = new StringNode('bar');
        $nestedMember3ValueNode = new StringNode('baz');

        $nestedMember1Node = new AttributeNode($nestedMember1ValueNode, 'privateMember', AttributeNode::SCOPE_PUBLIC);
        $nestedMember2Node = new AttributeNode($nestedMember2ValueNode, 'protectedMember', AttributeNode::SCOPE_PUBLIC);
        $nestedMember3Node = new AttributeNode($nestedMember3ValueNode, 'publicMember', AttributeNode::SCOPE_PUBLIC);

        $nestedMember1ValueNode->setParent($nestedMember1Node);
        $nestedMember2ValueNode->setParent($nestedMember2Node);
        $nestedMember3ValueNode->setParent($nestedMember3Node);

        $nestedAttributeNodes = [
            'privateMember' => $nestedMember1Node,
            'protectedMember' => $nestedMember2Node,
            'publicMember' => $nestedMember3Node,
        ];

        $nestedObjectNode = new ObjectNode($nestedAttributeNodes, SerializableObjectWithArrayData::class);

        $nestedAttributeNodes['privateMember']->setParent($nestedObjectNode);
        $nestedAttributeNodes['protectedMember']->setParent($nestedObjectNode);
        $nestedAttributeNodes['publicMember']->setParent($nestedObjectNode);

        $member1ValueNode = $nestedObjectNode;
        $member1Node = new AttributeNode($member1ValueNode, 'privateMember', AttributeNode::SCOPE_PUBLIC);
        $member1ValueNode->setParent($member1Node);

        $attributeNodes = [
            'privateMember' => $member1Node,
        ];

        $objectNode = new ObjectNode($attributeNodes, SerializableObjectWithScalarData::class);

        $attributeNodes['privateMember']->setParent($objectNode);

        $this->assertEquals(
            $objectNode,
            $this->parser->parse(serialize(new SerializableObjectWithScalarData(new SerializableObjectWithArrayData('foo', 'bar', 'baz'))))
        );
    }

    public function testParseSerializedObjectWithRecursionByReferenceMembersReturnsObjectNode(): void
    {
        $object = new \stdClass();
        $object->recursion = $object;
        $object->recursionByReference = &$object;

        $member1ValueNode = new RecursionNode(1);
        $member2ValueNode = new RecursionByReferenceNode(1);

        $member1Node = new AttributeNode($member1ValueNode, 'recursion', AttributeNode::SCOPE_PUBLIC);
        $member2Node = new AttributeNode($member2ValueNode, 'recursionByReference', AttributeNode::SCOPE_PUBLIC);

        $member1ValueNode->setParent($member1Node);
        $member2ValueNode->setParent($member2Node);

        $attributeNodes = [
            'recursion' => $member1Node,
            'recursionByReference' => $member2Node,
        ];

        $objectNode = new ObjectNode($attributeNodes, \stdClass::class);

        $attributeNodes['recursion']->setParent($objectNode);
        $attributeNodes['recursionByReference']->setParent($objectNode);

        $this->assertEquals(
            $objectNode,
            $this->parser->parse(serialize($object))
        );
    }

    public function testParseSerializedObjectWithRecursionMemberReturnsObjectNode(): void
    {
        $object1Member1ValueNode = new StringNode('foo1');
        $object1Member2ValueNode = new StringNode('bar1');
        $object1Member3ValueNode = new StringNode('baz1');

        $object1Member1Node = new AttributeNode($object1Member1ValueNode, 'privateMember', AttributeNode::SCOPE_PRIVATE, SimpleObject::class);
        $object1Member2Node = new AttributeNode($object1Member2ValueNode, 'protectedMember', AttributeNode::SCOPE_PROTECTED, '*');
        $object1Member3Node = new AttributeNode($object1Member3ValueNode, 'publicMember', AttributeNode::SCOPE_PUBLIC);

        $object1Member1ValueNode->setParent($object1Member1Node);
        $object1Member2ValueNode->setParent($object1Member2Node);
        $object1Member3ValueNode->setParent($object1Member3Node);

        $object1AttributeNodes = [
            'privateMember' => $object1Member1Node,
            'protectedMember' => $object1Member2Node,
            'publicMember' => $object1Member3Node,
        ];

        $objectNode1 = new ObjectNode($object1AttributeNodes, SimpleObject::class);

        $object1AttributeNodes['privateMember']->setParent($objectNode1);
        $object1AttributeNodes['protectedMember']->setParent($objectNode1);
        $object1AttributeNodes['publicMember']->setParent($objectNode1);

        $object2Member1ValueNode = new StringNode('foo2');
        $object2Member2ValueNode = new StringNode('bar2');
        $object2Member3ValueNode = new RecursionByReferenceNode(2);

        $object2Member1Node = new AttributeNode($object2Member1ValueNode, 'privateMember', AttributeNode::SCOPE_PRIVATE, SimpleObject::class);
        $object2Member2Node = new AttributeNode($object2Member2ValueNode, 'protectedMember', AttributeNode::SCOPE_PROTECTED, '*');
        $object2Member3Node = new AttributeNode($object2Member3ValueNode, 'publicMember', AttributeNode::SCOPE_PUBLIC);

        $object2Member1ValueNode->setParent($object2Member1Node);
        $object2Member2ValueNode->setParent($object2Member2Node);
        $object2Member3ValueNode->setParent($object2Member3Node);

        $object2AttributeNodes = [
            'privateMember' => $object2Member1Node,
            'protectedMember' => $object2Member2Node,
            'publicMember' => $object2Member3Node,
        ];

        $objectNode2 = new ObjectNode($object2AttributeNodes, SimpleObject::class);

        $object2AttributeNodes['privateMember']->setParent($objectNode2);
        $object2AttributeNodes['protectedMember']->setParent($objectNode2);
        $object2AttributeNodes['publicMember']->setParent($objectNode2);

        $object3Member3ValueNode = new StringNode('baz3');

        $object3Member1Node = new AttributeNode($objectNode1, 'privateMember', AttributeNode::SCOPE_PRIVATE, SimpleObject::class);
        $object3Member2Node = new AttributeNode($objectNode2, 'protectedMember', AttributeNode::SCOPE_PROTECTED, '*');
        $object3Member3Node = new AttributeNode($object3Member3ValueNode, 'publicMember', AttributeNode::SCOPE_PUBLIC);

        $objectNode1->setParent($object3Member1Node);
        $objectNode2->setParent($object3Member2Node);
        $object3Member3ValueNode->setParent($object3Member3Node);

        $object3AttributeNodes = [
            'privateMember' => $object3Member1Node,
            'protectedMember' => $object3Member2Node,
            'publicMember' => $object3Member3Node,
        ];

        $objectNode3 = new ObjectNode($object3AttributeNodes, SimpleObject::class);

        $object3AttributeNodes['privateMember']->setParent($objectNode3);
        $object3AttributeNodes['protectedMember']->setParent($objectNode3);
        $object3AttributeNodes['publicMember']->setParent($objectNode3);

        $object1 = new SimpleObject('foo1', 'bar1', 'baz1');
        $object2 = new SimpleObject('foo2', 'bar2', 'baz2');
        $object2->publicMember = &$object1;

        $this->assertEquals(
            $objectNode3,
            $this->parser->parse(serialize(new SimpleObject($object1, $object2, 'baz3')))
        );
    }
}
