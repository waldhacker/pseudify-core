-- https://docs.microsoft.com/en-us/sql/t-sql/statements/create-user-transact-sql?view=sql-server-ver15
CREATE LOGIN pseudify WITH PASSWORD = 'pseudify(!)w4ldh4ck3r';
GO

-- https://docs.microsoft.com/en-us/sql/t-sql/statements/create-database-transact-sql?view=sql-server-ver15
CREATE DATABASE pseudify;
GO

-- https://docs.microsoft.com/en-us/sql/t-sql/statements/create-user-transact-sql?view=sql-server-ver15
USE pseudify;
CREATE USER pseudify FOR LOGIN pseudify WITH DEFAULT_SCHEMA=[dbo];
GO

-- https://docs.microsoft.com/en-us/sql/t-sql/statements/grant-database-permissions-transact-sql?view=sql-server-ver15#examples
GRANT CREATE TABLE, SELECT, UPDATE TO pseudify;
ALTER ROLE db_owner ADD MEMBER pseudify;  
GO

EXIT
