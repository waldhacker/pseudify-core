<?php

declare(strict_types=1);

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Query\QueryBuilder;
use Faker\Factory;

require __DIR__.'/../../vendor/autoload.php';

mt_srand();

function mysqlEscape($data, string $driver, string $columnType)
{
    if (!is_string($data)) {
        return $data;
    }

    switch ($driver) {
        case 'pdo_mysql':
             // https://github.com/php/php-src/blob/PHP-5.6.40/ext/mysqlnd/mysqlnd_charset.c#L783
             // https://dev.mysql.com/doc/refman/8.0/en/string-literals.html#character-escape-sequences
             // https://mariadb.com/kb/en/string-literals/
            $replacements = [
                chr(0) => '\\0',
                chr(8) => '\\b',
                chr(9) => '\\t',
                chr(10) => '\\n',
                chr(13) => '\\r',
                chr(26) => '\\Z',
                '\\' => '\\\\',
                "'" => "\'",
                '"' => '\\"',
            ];

            return "'".strtr($data, $replacements)."'";
            break;
        case 'pdo_sqlsrv':
            // https://docs.microsoft.com/de-de/sql/t-sql/functions/string-escape-transact-sql?view=sql-server-ver15
            $replacements = [];
            // for ($a = 0; $a < 32; $a++) {
            //     $replacements[chr($a)] = '\\u00' . str_pad(dechex($a), 2, '0', STR_PAD_LEFT);
            // }

            $replacements = [
                chr(8) => '\\b',
                chr(9) => '\\t',
                chr(10) => '\\n',
                chr(12) => '\\f',
                chr(13) => '\\r',
                // '\\' => '\\\\',
                // '/' => '\\/',
                "'" => "''",
                // '"' => '\"',
            ];

            if ('blob' === $columnType) {
                return '0x'.bin2hex($data);
            } else {
                // https://docs.microsoft.com/de-de/sql/t-sql/statements/insert-transact-sql?view=sql-server-ver15#arguments
                return "N'".strtr($data, $replacements)."'";
            }
            break;
        case 'pdo_pgsql':
            // https://www.postgresql.org/docs/9.5/sql-syntax-lexical.html#SQL-SYNTAX-STRINGS-ESCAPE
            $replacements = [
                chr(8) => '\\b',
                chr(9) => '\\t',
                chr(10) => '\\n',
                chr(12) => '\\f',
                chr(13) => '\\r',
                "'" => "''",
            ];

            if ('blob' === $columnType) {
                return "'".$data."'::bytea";
            } else {
                return "'".strtr($data, $replacements)."'";
            }

            break;
        case 'pdo_sqlite':
            // https://www.sqlite.org/lang_expr.html#literal_values_constants_
            $replacements = [
                "'" => "''",
            ];

            if ('blob' === $columnType) {
                return "X'".bin2hex($data)."'";
            } else {
                return "'".strtr($data, $replacements)."'";
            }
            break;
    }

    return $data;
}

function createUserRecords(): array
{
    $faker = Factory::create();
    $passwordTypes = ['bcrypt', 'argon2i', 'argon2id'];

    $records = [];
    for ($a = 0; $a < 5; ++$a) {
        $passwordType = $passwordTypes[array_rand($passwordTypes, 1)];
        $plainText = $faker->password();
        switch ($passwordType) {
            case 'bcrypt':
                $password = password_hash($plainText, PASSWORD_BCRYPT, ['cost' => 4]);
                break;
            case 'argon2i':
                $password = password_hash($plainText, PASSWORD_ARGON2I, ['memory_cost' => 8, 'time_cost' => 1]);
                break;
            case 'argon2id':
                $password = password_hash($plainText, PASSWORD_ARGON2I, ['memory_cost' => 8, 'time_cost' => 1]);
                break;
        }

        $records[] = [
            'id' => $a + 1,
            'username' => $faker->userName(),
            'password' => $password,
            'password_hash_type' => $passwordType,
            'password_plaintext' => $plainText,
            'first_name' => $faker->firstName(),
            'last_name' => $faker->lastName(),
            'email' => $faker->safeEmail(),
            'city' => $faker->city(),
        ];
    }

    return $records;
}

function createUserSessionRecords(): array
{
    $faker = Factory::create();

    $records = [];
    for ($a = 0; $a < 5; ++$a) {
        $sessionData = serialize([
            'last_ip' => 1 === mt_rand(0, 1) ? $faker->ipv4() : $faker->ipv6(),
        ]);

        $records[] = [
            'id' => $a + 1,
            'session_data' => $sessionData,
        ];
    }

    return $records;
}

function createMetaDataRecords(array $userRecords, array $userSessionRecords): array
{
    $faker = Factory::create();

    $records = [];
    for ($a = 0; $a < 5; ++$a) {
        $metaDataPlaintext = [
            'key1' => $userRecords[array_rand($userRecords, 1)],
            'key2' => $userSessionRecords[array_rand($userSessionRecords, 1)],
            'key3' => [
                'key4' => $faker->ipv4(),
            ],
        ];

        $records[] = [
            'id' => $a + 1,
            'meta_data' => bin2hex(gzencode(serialize($metaDataPlaintext), 5, ZLIB_ENCODING_GZIP)),
            'meta_data_plaintext' => serialize($metaDataPlaintext),
        ];
    }

    return $records;
}

function createLogRecords(array $userRecords, array $userSessionRecords): array
{
    $faker = Factory::create();

    $records = [];
    for ($a = 0; $a < 5; ++$a) {
        $userRecord = $userRecords[array_rand($userRecords, 1)];
        $userName = 1 === mt_rand(0, 1) ? $faker->userName() : $userRecord['username'];
        $email = 1 === mt_rand(0, 1) ? $faker->safeEmail() : $userRecord['email'];
        $lastName = 1 === mt_rand(0, 1) ? $faker->lastName() : $userRecord['last_name'];

        $userSessionRecord = $userSessionRecords[array_rand($userSessionRecords, 1)];
        $userSessionData = unserialize($userSessionRecord['session_data']);
        $userSessionIp = $userSessionData['last_ip'];
        $ip = 1 === mt_rand(0, 1) ? $faker->ipv4() : $userSessionIp;

        if (1 === mt_rand(0, 1)) {
            $logDataPlaintext = [
                'userName' => $userName,
                'email' => $email,
                'lastName' => $lastName,
                'ip' => $ip,
            ];
            $logMessage = ['message' => sprintf('foo text "%s", another "%s"', $userName, $email)];

            $logType = 'foo';
            $logDataPlaintext = json_encode($logDataPlaintext);
            $logData = bin2hex(base64_encode($logDataPlaintext));
        } else {
            $user = new \StdClass();
            $user->userName = $userName;
            $user->lastName = $lastName;
            $user->email = $email;
            $user->id = mt_rand(1, 100);
            $user->user = &$user;

            $logDataPlaintext = [
                0 => $ip,
                'user' => $user,
            ];
            $logMessage = ['message' => sprintf('bar text "%s", another "%s"', $lastName, $userName)];

            $logType = 'bar';
            $logDataPlaintext = serialize($logDataPlaintext);
            $logData = bin2hex($logDataPlaintext);
        }

        $records[] = [
            'id' => $a + 1,
            'log_type' => $logType,
            'log_data' => $logData,
            'log_data_plaintext' => $logDataPlaintext,
            'log_message' => json_encode($logMessage),
            'ip' => $ip,
        ];
    }

    return $records;
}

$userRecords = createUserRecords();
$userSessionRecords = createUserSessionRecords();
$metaDataRecords = createMetaDataRecords($userRecords, $userSessionRecords);
$logRecords = createLogRecords($userRecords, $userSessionRecords);

$schema = [
    'wh_user' => [
        'id' => 'int',
        'username' => 'string',
        'password' => 'string',
        'password_hash_type' => 'string',
        'password_plaintext' => 'string',
        'first_name' => 'string',
        'last_name' => 'string',
        'email' => 'string',
        'city' => 'string',
    ],
    'wh_user_session' => [
        'id' => 'int',
        'session_data' => 'blob',
    ],
    'wh_meta_data' => [
        'id' => 'int',
        'meta_data' => 'blob',
        'meta_data_plaintext' => 'blob',
    ],
    'wh_log' => [
        'id' => 'int',
        'log_type' => 'string',
        'log_data' => 'blob',
        'log_data_plaintext' => 'blob',
        'log_message' => 'string',
        'ip' => 'string',
    ],
];

$tableRecords = [
    'wh_user' => $userRecords,
    'wh_user_session' => $userSessionRecords,
    'wh_meta_data' => $metaDataRecords,
    'wh_log' => $logRecords,
];

echo PHP_EOL.PHP_EOL.'mysql / mariadb'.PHP_EOL.PHP_EOL;
$connection = DriverManager::getConnection(['driver' => 'pdo_mysql', 'serverVersion' => '5.6']);
foreach ($tableRecords as $table => $records) {
    foreach ($records as $record) {
        $queryBuilder = new QueryBuilder($connection);
        $queryBuilder->insert($connection->quoteIdentifier($table));
        foreach ($record as $column => $value) {
            $queryBuilder->setValue($connection->quoteIdentifier($column), mysqlEscape($value, 'pdo_mysql', $schema[$table][$column]));
        }
        echo $queryBuilder->getSQL().';'.PHP_EOL;
    }
    echo PHP_EOL;
}

echo PHP_EOL.PHP_EOL.'mssql'.PHP_EOL.PHP_EOL;
$connection = DriverManager::getConnection(['driver' => 'pdo_sqlsrv', 'serverVersion' => '2017']);
foreach ($tableRecords as $table => $records) {
    foreach ($records as $record) {
        $queryBuilder = new QueryBuilder($connection);
        $queryBuilder->insert($connection->quoteIdentifier($table));
        foreach ($record as $column => $value) {
            $queryBuilder->setValue($connection->quoteIdentifier($column), mysqlEscape($value, 'pdo_sqlsrv', $schema[$table][$column]));
        }
        echo $queryBuilder->getSQL().';'.PHP_EOL;
    }
    echo PHP_EOL;
}

echo PHP_EOL.PHP_EOL.'postgresql'.PHP_EOL.PHP_EOL;
$connection = DriverManager::getConnection(['driver' => 'pdo_pgsql', 'serverVersion' => '9.5']);
foreach ($tableRecords as $table => $records) {
    foreach ($records as $record) {
        $queryBuilder = new QueryBuilder($connection);
        $queryBuilder->insert($connection->quoteIdentifier($table));
        foreach ($record as $column => $value) {
            $queryBuilder->setValue($connection->quoteIdentifier($column), mysqlEscape($value, 'pdo_pgsql', $schema[$table][$column]));
        }
        echo $queryBuilder->getSQL().';'.PHP_EOL;
    }
    echo PHP_EOL;
}

echo PHP_EOL.PHP_EOL.'sqlite3'.PHP_EOL.PHP_EOL;
$connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'serverVersion' => '3']);
foreach ($tableRecords as $table => $records) {
    foreach ($records as $record) {
        $queryBuilder = new QueryBuilder($connection);
        $queryBuilder->insert($connection->quoteIdentifier($table));
        foreach ($record as $column => $value) {
            $queryBuilder->setValue($connection->quoteIdentifier($column), mysqlEscape($value, 'pdo_sqlite', $schema[$table][$column]));
        }
        echo $queryBuilder->getSQL().';'.PHP_EOL;
    }
    echo PHP_EOL;
}
