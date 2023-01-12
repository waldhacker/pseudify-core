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
class ArrayNode extends Node
{
    /**
     * @param array<array-key, ArrayElementNode> $properties
     *
     * @api
     */
    public function __construct(private array $properties, protected ?Node $parentNode = null)
    {
        $this->properties = $properties;
    }

    /**
     * @return array<array-key, ArrayElementNode>
     *
     * @api
     */
    public function getContent(): array
    {
        return $this->properties;
    }

    /*
     * semantic alias
     * @return array<array-key, ArrayElementNode>
     * @api
     */
    public function getProperties(): array
    {
        return $this->getContent();
    }

    /**
     * @api
     */
    public function hasProperty(string|int $identifier): bool
    {
        return isset($this->properties[$identifier]);
    }

    /**
     * @api
     */
    public function getProperty(string|int $identifier): ArrayElementNode
    {
        if (!$this->hasProperty($identifier)) {
            throw new MissingPropertyException(sprintf('missing array property "%s"', $identifier), 1621657002);
        }

        return $this->properties[$identifier];
    }

    /**
     * @api
     */
    public function replaceProperty(string|int $identifier, Node $property): ArrayNode
    {
        $originalNode = $this->getProperty($identifier);
        $key = $originalNode->getKey();

        $this->properties[$identifier] = new ArrayElementNode($property, $key);
        $property->setParent($this->properties[$identifier]);
        $key->setParent($this->properties[$identifier]);
        $this->properties[$identifier]->setParent($this);

        return $this;
    }

    /**
     * semantic shortcut.
     *
     * @api
     */
    public function getPropertyContent(string|int $identifier): Node
    {
        if (!$this->hasProperty($identifier)) {
            throw new MissingPropertyException(sprintf('missing array property "%s"', $identifier), 1621657003);
        }

        return $this->properties[$identifier]->getContent();
    }

    /**
     * semantic shortcut.
     *
     * @return array<array-key, Node>
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
}
