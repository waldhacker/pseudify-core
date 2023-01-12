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

class GzCompressEncoder implements EncoderInterface
{
    public const DECODE_MAX_LENGTH = 'gzuncompress_max_length';
    public const ENCODE_ENCODING = 'gzcompress_encoding';
    public const ENCODE_LEVEL = 'gzcompress_level';

    private array $defaultContext = [
        self::DECODE_MAX_LENGTH => 0,
        self::ENCODE_ENCODING => ZLIB_ENCODING_DEFLATE,
        self::ENCODE_LEVEL => -1,
    ];

    /**
     * @api
     */
    public function __construct(array $defaultContext = [])
    {
        $this->defaultContext = array_merge($this->defaultContext, $defaultContext);
    }

    /**
     * @param string $data
     *
     * @return string|false
     *
     * @api
     */
    public function decode($data, array $context = [])
    {
        $maxLength = is_int($context[self::DECODE_MAX_LENGTH] ?? null) ? (int) $context[self::DECODE_MAX_LENGTH] : (int) $this->defaultContext[self::DECODE_MAX_LENGTH];

        return @gzuncompress($data, $maxLength);
    }

    /**
     * @param mixed $data
     *
     * @return string|false
     *
     * @api
     */
    public function encode($data, array $context = [])
    {
        $level = is_int($context[self::ENCODE_LEVEL] ?? null) ? (int) $context[self::ENCODE_LEVEL] : (int) $this->defaultContext[self::ENCODE_LEVEL];
        $encoding = is_int($context[self::ENCODE_ENCODING] ?? null) ? (int) $context[self::ENCODE_ENCODING] : (int) $this->defaultContext[self::ENCODE_ENCODING];

        if (!is_string($data)) {
            return false;
        }

        try {
            return @gzcompress($data, $level, $encoding);
        } catch (\ValueError $e) {
            return false;
        }
    }
}
