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

use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Converter\StringConverter;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Node;
use Waldhacker\Pseudify\Core\Processor\Encoder\Serialized\Parser;

class SerializedEncoder implements EncoderInterface
{
    private array $defaultContext = [];
    private Parser $parser;
    private StringConverter $stringConverter;

    /**
     * @api
     */
    public function __construct(array $defaultContext = [])
    {
        $this->defaultContext = array_merge($this->defaultContext, $defaultContext);
        $this->parser = new Parser();
        $this->stringConverter = new StringConverter();
    }

    /**
     * @param string $data
     *
     * @return Node
     *
     * @api
     */
    public function decode($data, array $context = [])
    {
        return $this->parser->parse($data);
    }

    /**
     * @param Node $data
     *
     * @return string
     *
     * @api
     */
    public function encode($data, array $context = [])
    {
        return $this->stringConverter->convert($data);
    }
}
