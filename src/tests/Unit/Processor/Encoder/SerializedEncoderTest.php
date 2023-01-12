<?php

declare(strict_types=1);

namespace Waldhacker\Pseudify\Core\Tests\Unit\Processor\Encoder;

use PHPUnit\Framework\TestCase;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Node\ArrayElementNode;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Node\ArrayNode;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Node\IntegerNode;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Node\StringNode;
use Waldhacker\Pseudify\Core\Processor\Encoder\SerializedEncoder;

class SerializedEncoderTest extends TestCase
{
    public function testDecodeReturnsNodeObjectWithValidInput(): void
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

        $converter = new SerializedEncoder();

        $this->assertEquals(
            $arrayNode,
            $converter->decode(serialize(['foo' => 1, 'bar' => 2]))
        );
    }

    public function testEncodeReturnsSerializedStringWithNodeInput(): void
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

        $converter = new SerializedEncoder();

        $this->assertEquals(
            serialize(['foo' => 1, 'bar' => 2]),
            $converter->encode($arrayNode)
        );
    }
}
