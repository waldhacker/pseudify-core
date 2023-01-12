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

namespace Waldhacker\Pseudify\Core\Processor\Encoder;

use Symfony\Component\Serializer\Encoder\YamlEncoder as SymfonyEncoder;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;

class YamlEncoder implements EncoderInterface
{
    public const PRESERVE_EMPTY_OBJECTS = SymfonyEncoder::PRESERVE_EMPTY_OBJECTS;
    public const YAML_FLAGS = SymfonyEncoder::YAML_FLAGS;
    public const YAML_INDENT = SymfonyEncoder::YAML_INDENT;
    public const YAML_INLINE = SymfonyEncoder::YAML_INLINE;

    private array $defaultContext = [
        self::YAML_FLAGS => 0,
        self::YAML_INDENT => 0,
        self::YAML_INLINE => 0,
    ];

    private SymfonyEncoder $concreteEncoder;

    /**
     * @api
     */
    public function __construct(array $defaultContext = [])
    {
        $this->defaultContext = array_merge($this->defaultContext, $defaultContext);
        $this->concreteEncoder = new SymfonyEncoder(new Dumper(), new Parser(), $this->defaultContext);
    }

    /**
     * @param string $data
     *
     * @return mixed
     *
     * @api
     */
    public function decode($data, array $context = [])
    {
        $context = array_merge($this->defaultContext, $context);

        return $this->concreteEncoder->decode($data, SymfonyEncoder::FORMAT, $context);
    }

    /**
     * @param array $data
     *
     * @return string
     *
     * @api
     */
    public function encode($data, array $context = [])
    {
        $context = array_merge($this->defaultContext, $context);

        return $this->concreteEncoder->encode($data, SymfonyEncoder::FORMAT, $context);
    }
}
