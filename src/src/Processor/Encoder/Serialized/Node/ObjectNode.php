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

namespace Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Node;

use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Node;

/**
 * Based on qafoo/ser-pretty
 * https://github.com/Qafoo/ser-pretty.
 */
class ObjectNode extends Node
{
    /**
     * @param array<string, AttributeNode> $properties
     *
     * @api
     */
    public function __construct(private array $properties, private string $className, protected ?Node $parentNode = null)
    {
    }

    /**
     * @return array<string, AttributeNode>
     *
     * @api
     */
    public function getContent(): array
    {
        return $this->properties;
    }

    /*
     * semantic alias
     * @return array<string, AttributeNode>
     * @api
     */
    public function getProperties(): array
    {
        return $this->getContent();
    }

    /**
     * @api
     */
    public function hasProperty(string $identifier): bool
    {
        return isset($this->properties[$identifier]);
    }

    /**
     * @api
     */
    public function getProperty(string $identifier): AttributeNode
    {
        if (!$this->hasProperty($identifier)) {
            throw new MissingPropertyException(sprintf('missing object property "%s" for object "%s"', $identifier, $this->className), 1621657000);
        }

        return $this->properties[$identifier];
    }

    /**
     * @api
     */
    public function replaceProperty(string $identifier, Node $property): ObjectNode
    {
        $originalNode = $this->getProperty($identifier);

        $this->properties[$identifier] = new AttributeNode(
            $property,
            $originalNode->getPropertyName(),
            $originalNode->getScope(),
            $originalNode->getClassName()
        );
        $property->setParent($this->properties[$identifier]);
        $this->properties[$identifier]->setParent($this);

        return $this;
    }

    /**
     * semantic shortcut.
     *
     * @api
     */
    public function getPropertyContent(string $identifier): Node
    {
        if (!$this->hasProperty($identifier)) {
            throw new MissingPropertyException(sprintf('missing object property "%s" for object "%s"', $identifier, $this->className), 1621657001);
        }

        return $this->properties[$identifier]->getContent();
    }

    /**
     * semantic shortcut.
     *
     * @return array<string, Node>
     *
     * @api
     */
    public function getPropertiesContents(): array
    {
        $properties = [];
        foreach ($this->properties as $identifier => $property) {
            $properties[$identifier] = $property->getContent();
        }

        return $properties;
    }

    /**
     * @api
     */
    public function getClassName(): string
    {
        return $this->className;
    }
}
