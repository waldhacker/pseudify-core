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

use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder as SymfonyEncoder;

class JsonEncoder implements EncoderInterface
{
    public const DECODE_ASSOCIATIVE = JsonDecode::ASSOCIATIVE;
    public const DECODE_OPTIONS = JsonDecode::OPTIONS;
    public const DECODE_RECURSION_DEPTH = JsonDecode::RECURSION_DEPTH;
    public const ENCODE_OPTIONS = JsonEncode::OPTIONS;

    private array $defaultContext = [
        self::DECODE_ASSOCIATIVE => true,
        self::DECODE_OPTIONS => 0,
        self::DECODE_RECURSION_DEPTH => 512,
        self::ENCODE_OPTIONS => 0,
    ];

    private JsonDecode $concreteDecoder;
    private JsonEncode $concreteEncoder;

    /**
     * @api
     */
    public function __construct(array $defaultContext = [])
    {
        $this->defaultContext = array_merge($this->defaultContext, $defaultContext);
        $this->concreteEncoder = new JsonEncode($this->defaultContext);
        $this->concreteDecoder = new JsonDecode($this->defaultContext);
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

        return $this->concreteDecoder->decode($data, SymfonyEncoder::FORMAT, $context);
    }

    /**
     * @param string|int|float|bool|array $data
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
