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

use Symfony\Component\Serializer\Encoder\CsvEncoder as SymfonyEncoder;

class CsvEncoder implements EncoderInterface
{
    public const AS_COLLECTION_KEY = SymfonyEncoder::AS_COLLECTION_KEY;
    public const DELIMITER_KEY = SymfonyEncoder::DELIMITER_KEY;
    public const ENCLOSURE_KEY = SymfonyEncoder::ENCLOSURE_KEY;
    public const ESCAPE_CHAR_KEY = SymfonyEncoder::ESCAPE_CHAR_KEY;
    public const ESCAPE_FORMULAS_KEY = SymfonyEncoder::ESCAPE_FORMULAS_KEY;
    public const HEADERS_KEY = SymfonyEncoder::HEADERS_KEY;
    public const KEY_SEPARATOR_KEY = SymfonyEncoder::KEY_SEPARATOR_KEY;
    public const NO_HEADERS_KEY = SymfonyEncoder::NO_HEADERS_KEY;
    public const OUTPUT_UTF8_BOM_KEY = SymfonyEncoder::OUTPUT_UTF8_BOM_KEY;

    private array $defaultContext = [
        self::AS_COLLECTION_KEY => true,
        self::DELIMITER_KEY => ',',
        self::ENCLOSURE_KEY => '"',
        self::ESCAPE_CHAR_KEY => '',
        self::ESCAPE_FORMULAS_KEY => false,
        self::HEADERS_KEY => [],
        self::KEY_SEPARATOR_KEY => '.',
        self::NO_HEADERS_KEY => false,
        self::OUTPUT_UTF8_BOM_KEY => false,
    ];

    private SymfonyEncoder $concreteEncoder;

    /**
     * @api
     */
    public function __construct(array $defaultContext = [])
    {
        $this->defaultContext = array_merge($this->defaultContext, $defaultContext);
        \PHP_VERSION_ID < 70400 && '' === $this->defaultContext[self::ESCAPE_CHAR_KEY] ? $this->defaultContext[self::ESCAPE_CHAR_KEY] = '\\' : null;

        $this->concreteEncoder = new SymfonyEncoder($this->defaultContext);
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
