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

class ChainedEncoder implements EncoderInterface
{
    /** @var array<int, EncoderInterface> */
    private array $encoders = [];

    /**
     * @api
     */
    public function __construct(array $encoders = [])
    {
        foreach ($encoders as $encoder) {
            if (!$encoder instanceof EncoderInterface) {
                continue;
            }
            $this->encoders[] = $encoder;
        }
    }

    /**
     * @param mixed $data
     *
     * @return mixed
     *
     * @api
     */
    public function decode($data, array $context = [])
    {
        foreach ($this->encoders as $encoder) {
            /** @var mixed $data */
            $data = $encoder->decode($data, $context);
        }

        return $data;
    }

    /**
     * @param mixed $data
     *
     * @return mixed
     *
     * @api
     */
    public function encode($data, array $context = [])
    {
        foreach (array_reverse($this->encoders) as $encoder) {
            /** @var mixed $data */
            $data = $encoder->encode($data, $context);
        }

        return $data;
    }

    /**
     * @api
     */
    public function hasEncoder(int $index): bool
    {
        return isset($this->encoders[$index]);
    }

    /**
     * @api
     */
    public function getEncoder(int $index): EncoderInterface
    {
        if (!$this->hasEncoder($index)) {
            throw new MissingEncoderException(sprintf('missing encoder "%s"', $index), 1621656967);
        }

        return $this->encoders[$index];
    }

    /**
     * @api
     */
    public function addEncoder(EncoderInterface $encoder): ChainedEncoder
    {
        $this->encoders[] = $encoder;

        return $this;
    }

    /**
     * @api
     */
    public function removeEncoder(int $index): ChainedEncoder
    {
        unset($this->encoders[$index]);
        $this->encoders = array_values($this->encoders);

        return $this;
    }

    /**
     * @return array<int, EncoderInterface>
     *
     * @api
     */
    public function getEncoders(): array
    {
        return $this->encoders;
    }
}
