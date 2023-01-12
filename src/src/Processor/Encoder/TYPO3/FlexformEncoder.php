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

namespace Waldhacker\Pseudify\Core\Processor\Encoder\TYPO3;

use Waldhacker\Pseudify\Core\Processor\Encoder\XmlEncoder;

class FlexformEncoder extends XmlEncoder
{
    protected array $defaultContext = [
        self::ENCODING => 'utf-8',
        self::AS_COLLECTION => false,
        self::DECODER_IGNORED_NODE_TYPES => [\XML_PI_NODE, \XML_COMMENT_NODE],
        self::ENCODER_IGNORED_NODE_TYPES => [],
        self::FORMAT_OUTPUT => true,
        self::LOAD_OPTIONS => \LIBXML_NONET | \LIBXML_NOBLANKS,
        self::REMOVE_EMPTY_TAGS => false,
        self::ROOT_NODE_NAME => 'T3FlexForms',
        self::STANDALONE => true,
        self::TYPE_CAST_ATTRIBUTES => true,
    ];
}
