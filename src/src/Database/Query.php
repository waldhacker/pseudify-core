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

namespace Waldhacker\Pseudify\Core\Database;

use Doctrine\DBAL\Platforms\SQLServer2012Platform;

/**
 * @internal
 */
class Query
{
    public function __construct(private ConnectionManager $connectionManager)
    {
    }

    public function unquoteSingleIdentifier(string $identifier): string
    {
        $identifier = trim($identifier);
        $platform = $this->connectionManager->getConnection()->getDatabasePlatform();
        if ($platform instanceof SQLServer2012Platform) {
            // mssql quotes identifiers with [ and ], not a single character
            $identifier = ltrim($identifier, '[');
            $identifier = rtrim($identifier, ']');
        } else {
            /**
             * @psalm-suppress DeprecatedMethod
             */
            $quoteChar = $platform->getIdentifierQuoteCharacter();
            $identifier = trim($identifier, $quoteChar);
            $identifier = str_replace($quoteChar.$quoteChar, $quoteChar, $identifier);
        }

        return $identifier;
    }

    /**
     * @param array<int, string> $input
     *
     * @return array<int, string>
     */
    public function quoteIdentifiersForSelect(array $input): array
    {
        $connection = $this->connectionManager->getConnection();
        foreach ($input as &$select) {
            [$fieldName, $alias, $suffix] = array_pad(
                $this->trimExplode(
                    ' AS ',
                    str_ireplace(' as ', ' AS ', $select)
                ),
                3,
                null
            );

            if (!empty($suffix) || null === $fieldName) {
                throw new InvalidStatementException(sprintf('could not parse the select statement "%s"', $select), 1619594365);
            }

            if ('.*' === substr($fieldName, -2)) {
                $select = $connection->quoteIdentifier(substr($fieldName, 0, -2)).'.*';
            } elseif ('*' !== $fieldName) {
                $select = $connection->quoteIdentifier($fieldName);
            }

            if (!empty($alias)) {
                $select .= ' AS '.$connection->quoteIdentifier($alias);
            }
        }

        return $input;
    }

    /**
     * @return array<int, string>
     */
    private function trimExplode(string $delim, string $string): array
    {
        $result = explode($delim, $string) ?: [];

        $temp = [];
        foreach ($result as $value) {
            if ('' !== trim($value)) {
                $temp[] = $value;
            }
        }
        $result = $temp;
        $result = array_map('trim', $result);

        return $result;
    }
}
