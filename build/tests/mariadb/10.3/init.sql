-- https://mariadb.com/kb/en/create-user/
CREATE USER IF NOT EXISTS 'pseudify'@'%' IDENTIFIED BY 'pseudify(!)w4ldh4ck3r';

-- https://mariadb.com/kb/en/create-database/
-- https://mariadb.com/kb/en/supported-character-sets-and-collations/
CREATE DATABASE IF NOT EXISTS pseudify_utf8mb4 CHARACTER SET = 'utf8mb4' COLLATE = 'utf8mb4_general_ci';

-- https://mariadb.com/kb/en/grant/
GRANT SELECT, UPDATE ON pseudify_utf8mb4.* TO 'pseudify'@'%';

FLUSH PRIVILEGES;
