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

namespace Waldhacker\Pseudify\Core\Profile\Model\Analyze;

use Waldhacker\Pseudify\Core\Processor\Encoder\Base64Encoder;
use Waldhacker\Pseudify\Core\Processor\Encoder\EncoderInterface;
use Waldhacker\Pseudify\Core\Processor\Encoder\GzCompressEncoder;
use Waldhacker\Pseudify\Core\Processor\Encoder\GzDeflateEncoder;
use Waldhacker\Pseudify\Core\Processor\Encoder\GzEncodeEncoder;
use Waldhacker\Pseudify\Core\Processor\Encoder\HexEncoder;
use Waldhacker\Pseudify\Core\Processor\Encoder\ScalarEncoder;
use Waldhacker\Pseudify\Core\Processor\Encoder\ZlibEncodeEncoder;
use Waldhacker\Pseudify\Core\Processor\Processing\DataProcessingInterface;

class TargetColumn
{
    public const DATA_TYPE_BASE64 = 'base64';
    public const DATA_TYPE_GZCOMPRESS = 'gzcompress';
    public const DATA_TYPE_GZDEFLATE = 'gzdeflate';
    public const DATA_TYPE_GZENCODE = 'gzencode';
    public const DATA_TYPE_HEX = 'hex';
    public const DATA_TYPE_SCALAR = 'scalar';
    public const DATA_TYPE_ZLIBENCODE = 'zlib_encode';

    private ?EncoderInterface $encoder = null;
    /** @var array<string, DataProcessingInterface> */
    private array $dataProcessings = [];

    /**
     * @internal
     */
    public function __construct(private string $identifier, string $dataType = self::DATA_TYPE_SCALAR, array $encoderContext = [])
    {
        switch ($dataType) {
            case static::DATA_TYPE_BASE64:
                $this->setEncoder(new Base64Encoder($encoderContext));
                break;
            case static::DATA_TYPE_GZCOMPRESS:
                $this->setEncoder(new GzCompressEncoder($encoderContext));
                break;
            case static::DATA_TYPE_GZDEFLATE:
                $this->setEncoder(new GzDeflateEncoder($encoderContext));
                break;
            case static::DATA_TYPE_GZENCODE:
                $this->setEncoder(new GzEncodeEncoder($encoderContext));
                break;
            case static::DATA_TYPE_HEX:
                $this->setEncoder(new HexEncoder($encoderContext));
                break;
            case static::DATA_TYPE_SCALAR:
                $this->setEncoder(new ScalarEncoder($encoderContext));
                break;
            case static::DATA_TYPE_ZLIBENCODE:
                $this->setEncoder(new ZlibEncodeEncoder($encoderContext));
                break;
            default:
        }
    }

    /**
     * @api
     */
    public static function create(string $identifier, string $dataType = self::DATA_TYPE_SCALAR, array $encoderContext = []): TargetColumn
    {
        return new self($identifier, $dataType, $encoderContext);
    }

    /**
     * @api
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @api
     */
    public function setEncoder(EncoderInterface $encoder): TargetColumn
    {
        $this->encoder = $encoder;

        return $this;
    }

    /**
     * @api
     */
    public function getEncoder(): EncoderInterface
    {
        return $this->encoder ?? new ScalarEncoder([]);
    }

    /**
     * @api
     */
    public function hasDataProcessing(string $identifier): bool
    {
        return isset($this->dataProcessings[$identifier]);
    }

    /**
     * @api
     */
    public function getDataProcessing(string $identifier): DataProcessingInterface
    {
        if (!$this->hasDataProcessing($identifier)) {
            throw new MissingDataProcessingException(sprintf('missing dataProcessing "%s" for column "%s"', $identifier, $this->identifier), 1621686502);
        }

        return $this->dataProcessings[$identifier];
    }

    /**
     * @api
     */
    public function addDataProcessing(DataProcessingInterface $dataProcessing): TargetColumn
    {
        $this->dataProcessings[$dataProcessing->getIdentifier()] = $dataProcessing;

        return $this;
    }

    /**
     * @api
     */
    public function removeDataProcessing(string $identifier): TargetColumn
    {
        unset($this->dataProcessings[$identifier]);

        return $this;
    }

    /**
     * @return array<int, DataProcessingInterface>
     *
     * @api
     */
    public function getDataProcessings(): array
    {
        return array_values($this->dataProcessings);
    }

    /**
     * @return array<int, string>
     *
     * @api
     */
    public function getDataProcessingIdentifiers(): array
    {
        return array_keys($this->dataProcessings);
    }
}
