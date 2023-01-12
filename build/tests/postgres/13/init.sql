DO
$do$
BEGIN
   IF EXISTS (
      SELECT FROM pg_catalog.pg_roles WHERE  rolname = 'pseudify') THEN

      RAISE NOTICE 'Role "pseudify" already exists. Skipping.';
   ELSE
      -- https://www.postgresql.org/docs/13/sql-createrole.html
      CREATE ROLE pseudify WITH LOGIN ENCRYPTED PASSWORD 'pseudify(!)w4ldh4ck3r';
   END IF;
END
$do$;

-- https://www.postgresql.org/docs/13/sql-createdatabase.html
-- https://www.postgresql.org/docs/13/multibyte.html#CHARSET-TABLE
SELECT 'CREATE DATABASE pseudify_utf8 ENCODING "UTF8"' WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'pseudify_utf8')\gexec

-- https://www.postgresql.org/docs/13/sql-grant.html
REVOKE CONNECT ON DATABASE pseudify_utf8 FROM PUBLIC;
GRANT CONNECT ON DATABASE pseudify_utf8 TO pseudify;

REVOKE ALL ON ALL TABLES IN SCHEMA public FROM PUBLIC;
GRANT SELECT, UPDATE ON ALL TABLES IN SCHEMA public TO pseudify;

ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT, UPDATE ON TABLES TO pseudify;
