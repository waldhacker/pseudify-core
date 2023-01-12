-- https://dev.mysql.com/doc/refman/8.0/en/create-user.html
-- "WITH mysql_native_password" because of https://stackoverflow.com/questions/49083573/php-7-2-2-mysql-8-0-pdo-gives-authentication-method-unknown-to-the-client-ca
CREATE USER IF NOT EXISTS 'pseudify'@'%' IDENTIFIED WITH mysql_native_password BY 'pseudify(!)w4ldh4ck3r';

-- https://dev.mysql.com/doc/refman/8.0/en/create-database.html
-- https://dev.mysql.com/doc/refman/8.0/en/charset-charsets.html
CREATE DATABASE IF NOT EXISTS pseudify_cp1252 CHARACTER SET = 'latin1' COLLATE = 'latin1_swedish_ci';
CREATE DATABASE IF NOT EXISTS pseudify_iso8859_2 CHARACTER SET = 'latin2' COLLATE = 'latin2_general_ci';
CREATE DATABASE IF NOT EXISTS pseudify_utf8 CHARACTER SET = 'utf8' COLLATE = 'utf8_general_ci';
CREATE DATABASE IF NOT EXISTS pseudify_utf8mb4 CHARACTER SET = 'utf8mb4' COLLATE = 'utf8mb4_general_ci';

-- https://dev.mysql.com/doc/refman/8.0/en/grant.html
GRANT SELECT, UPDATE ON pseudify_cp1252.* TO 'pseudify'@'%';
GRANT SELECT, UPDATE ON pseudify_iso8859_2.* TO 'pseudify'@'%';
GRANT SELECT, UPDATE ON pseudify_utf8.* TO 'pseudify'@'%';
GRANT SELECT, UPDATE ON pseudify_utf8mb4.* TO 'pseudify'@'%';

FLUSH PRIVILEGES;
