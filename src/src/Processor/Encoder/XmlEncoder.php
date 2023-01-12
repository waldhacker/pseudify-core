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

use Symfony\Component\Serializer\Encoder\XmlEncoder as SymfonyEncoder;

class XmlEncoder implements EncoderInterface
{
    public const AS_COLLECTION = SymfonyEncoder::AS_COLLECTION;
    public const DECODER_IGNORED_NODE_TYPES = SymfonyEncoder::DECODER_IGNORED_NODE_TYPES;
    public const ENCODER_IGNORED_NODE_TYPES = SymfonyEncoder::ENCODER_IGNORED_NODE_TYPES;
    public const ENCODING = SymfonyEncoder::ENCODING;
    public const FORMAT_OUTPUT = SymfonyEncoder::FORMAT_OUTPUT;
    public const LOAD_OPTIONS = SymfonyEncoder::LOAD_OPTIONS;
    public const REMOVE_EMPTY_TAGS = SymfonyEncoder::REMOVE_EMPTY_TAGS;
    public const ROOT_NODE_NAME = SymfonyEncoder::ROOT_NODE_NAME;
    public const STANDALONE = SymfonyEncoder::STANDALONE;
    public const TYPE_CAST_ATTRIBUTES = SymfonyEncoder::TYPE_CAST_ATTRIBUTES;
    public const VERSION = SymfonyEncoder::VERSION;

    protected array $defaultContext = [
        self::AS_COLLECTION => false,
        self::DECODER_IGNORED_NODE_TYPES => [\XML_PI_NODE, \XML_COMMENT_NODE],
        self::ENCODER_IGNORED_NODE_TYPES => [],
        self::LOAD_OPTIONS => \LIBXML_NONET | \LIBXML_NOBLANKS,
        self::REMOVE_EMPTY_TAGS => false,
        self::ROOT_NODE_NAME => 'response',
        self::TYPE_CAST_ATTRIBUTES => true,
    ];

    private SymfonyEncoder $concreteEncoder;

    /**
     * @api
     */
    public function __construct(array $defaultContext = [])
    {
        $this->defaultContext = array_merge($this->defaultContext, $defaultContext);
        $this->concreteEncoder = new SymfonyEncoder($this->defaultContext);
    }

    /**
     * @param string $data
     *
     * @return array
     *
     * @api
     */
    public function decode($data, array $context = [])
    {
        $context = array_merge($this->defaultContext, $context);

        return (array) $this->concreteEncoder->decode($data, SymfonyEncoder::FORMAT, $context);
    }

    /**
     * @param array $data
     *
     * @return string|false
     *
     * @api
     */
    public function encode($data, array $context = [])
    {
        $context = array_merge($this->defaultContext, $context);

        return $this->concreteEncoder->encode($data, SymfonyEncoder::FORMAT, $context);
    }
}
