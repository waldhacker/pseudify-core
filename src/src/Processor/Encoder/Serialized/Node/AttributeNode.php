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
class AttributeNode extends Node
{
    public const SCOPE_PRIVATE = 'private';
    public const SCOPE_PROTECTED = 'protected';
    public const SCOPE_PUBLIC = 'public';

    /*
     * @api
     */
    public function __construct(private Node $content, private string $propertyName, private string $scope, private ?string $className = null, protected ?Node $parentNode = null)
    {
    }

    /*
     * @api
     */
    public function getContent(): Node
    {
        return $this->content;
    }

    /*
     * @api
     */
    public function getClassName(): ?string
    {
        return $this->className;
    }

    /*
     * @api
     */
    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    /*
     * @api
     */
    public function getScope(): string
    {
        return $this->scope;
    }
}
