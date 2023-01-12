<?php

declare(strict_types=1);

/*
 * This file is part of the pseudify database pseudonymizer project
 * - (c) 2022 waldhacker UG (haftungsbeschränkt)
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Converter;

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

class StringConverter
{
    /**
     * @api
     */
    public function convert(Node $node): string
    {
        switch (get_class($node)) {
            case ArrayNode::class:
                return $this->convertArray($node);

            case ArrayElementNode::class:
                return $this->convertArrayElement($node);

            case AttributeNode::class:
                return $this->convertAttribute($node);

            case BooleanNode::class:
                return $this->convertBoolean($node);

            case FloatNode::class:
                return $this->convertFloat($node);

            case IntegerNode::class:
                return $this->convertInteger($node);

            case NullNode::class:
                return $this->convertNull();

            case ObjectNode::class:
                return $this->convertObject($node);

            case RecursionNode::class:
                return $this->convertRecursion($node);

            case RecursionByReferenceNode::class:
                return $this->convertRecursionByReference($node);

            case SerializableObjectNode::class:
                return $this->convertSerializableObject($node);

            case StringNode::class:
                return $this->convertString($node);

            default:
                throw new MissingNodeTypeException(sprintf('unknown node type "%s"', get_class($node)), 1620889720);
        }
    }

    private function convertArray(ArrayNode $node): string
    {
        $content = '';
        foreach ($node->getContent() as $element) {
            /* ArrayElementNode $element */
            $content .= $this->convert($element);
        }

        // a:2:{i:0;s:3:"foo";i:1;s:3:"bar";}
        return sprintf('a:%s:{%s}', count($node->getContent()), $content);
    }

    private function convertArrayElement(ArrayElementNode $node): string
    {
        // i:0;s:3:"foo";
        return $this->convert($node->getKey()).$this->convert($node->getContent());
    }

    private function convertObject(ObjectNode $node): string
    {
        $content = '';
        foreach ($node->getContent() as $element) {
            /* AttributeNode $element */
            $content .= $this->convert($element);
        }

        // O:27:"Waldhacker\Pseudify\Core\TestClass":2:{s:32:"\x00Waldhacker\Pseudify\Core\TestClass\x00Foo";i:0;s:3:"bar";s:3:"baz";}
        return sprintf(
            'O:%s:"%s":%s:{%s}',
            strlen($node->getClassName()),
            $node->getClassName(),
            count($node->getContent()),
            $content
        );
    }

    private function convertAttribute(AttributeNode $node): string
    {
        if (AttributeNode::SCOPE_PRIVATE === $node->getScope()) {
            // private member
            // s:61:"\x00Waldhacker\Pseudify\Core\Processor\Encoder\SerializedEncoder\x00member";i:5;
            return sprintf(
                's:%s:"%s%s%s%s";%s',
                strlen($node->getPropertyName()) + strlen($node->getClassName() ?? '__invalid__') + 2,
                "\x00",
                $node->getClassName() ?? '__invalid__',
                "\x00",
                $node->getPropertyName(),
                $this->convert($node->getContent())
            );
        } elseif (AttributeNode::SCOPE_PROTECTED === $node->getScope()) {
            // protected member
            // s:9:"\x00*\x00member";i:5;
            return sprintf(
                's:%s:"%s*%s%s";%s',
                strlen($node->getPropertyName()) + 3,
                "\x00",
                "\x00",
                $node->getPropertyName(),
                $this->convert($node->getContent())
            );
        } else {
            // public member
            // s:6:"member";i:5;
            return sprintf(
                's:%s:"%s";%s',
                strlen($node->getPropertyName()),
                $node->getPropertyName(),
                $this->convert($node->getContent())
            );
        }
    }

    private function convertSerializableObject(SerializableObjectNode $node): string
    {
        $content = $this->convert($node->getContent());

        // C:39:"Waldhacker\Pseudify\Core\SerializableTestClass":5:{i:23;}
        return sprintf(
            'C:%s:"%s":%s:{%s}',
            strlen($node->getClassName()),
            $node->getClassName(),
            strlen($content),
            $content
        );
    }

    private function convertString(StringNode $node): string
    {
        // s:3:"foo";
        return sprintf('s:%s:"%s";', strlen($node->getContent()), $node->getContent());
    }

    private function convertInteger(IntegerNode $node): string
    {
        // i:23;
        return sprintf('i:%s;', $node->getContent());
    }

    private function convertFloat(FloatNode $node): string
    {
        // d:42.5;
        return sprintf('d:%s;', $node->getContent());
    }

    private function convertNull(): string
    {
        // N;
        return 'N;';
    }

    private function convertBoolean(BooleanNode $node): string
    {
        // b:1;
        return sprintf('b:%s;', $node->getContent() ? 1 : 0);
    }

    private function convertRecursion(RecursionNode $node): string
    {
        // r:66;
        return sprintf('r:%s;', $node->getContent());
    }

    private function convertRecursionByReference(RecursionByReferenceNode $node): string
    {
        // R:66;
        return sprintf('R:%s;', $node->getContent());
    }
}
