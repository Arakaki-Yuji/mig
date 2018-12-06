# Mig

Mig is a simple migration tool for SQL scripts, created by php.

It was tested by only MySQL.


## Quickstart

Install mig by composer global

``` shell
$ composer global require arakaki-yuji/mig
```

add execution path for composer to $PATH.

``` shell
$ export PATH=$PATH:$HOME/.composer/vendor/bin

```


Set mig.config.php to project root directory.

``` php
<?php

return [
    'db_dsn' => '', // example: mysql:host=localhost:3306;dbname=project_db;
    'db_username' => '',
    'db_passwd' => '', 
    'migration_filepath' => 'migrations' // directory for place SQL scripts.
];

```


Run init command. it create migrations table.

``` shell
$ mig-cli init
```


Create SQL scripts for migration.


``` shell
$ mig-cli create create_user_table 
Create a new migration file.
=================

create migrations/20180907025457_create_user_table.up.sql
create migrations/20180907025457_create_user_table.down.sql

```

Add SQL scripts like the below to migrations/20180907025457_create_user_table.up.sql.

``` sql
CREATE TABLE IF NOT EXISTS user(id BIGINT);
```

Add SQL scripts like the below to migrations/20180907025457_create_user_table.down.sql.

``` sql
DROP TABLE IF EXISTS user;

```

Apply pending migrations.

``` shell
$ mig-cli migrate 
Start migration.
=================

Migrate migrations/20180907025457_create_user_table.up.sql

```

Rollback the latest migration applied.

``` shell
$ mig-cli rollback
Rollback a migration.
======================

Rollback migrations/20180907025457_create_user_table.down.sql

```


## Set config values by environment values

if you want to set config values by environment values, you can do it.

``` shell
$ MIG_DB_DSN="mysql:host=127.0.0.1:3306;dbname=migrationdbname;" MIG_DB_USERNAME="admin" MIG_DB_PASSWD="password" MIG_MIGRATION_FILEPATH="migrations" bin/mig migrate
Start migration.
=================

Migrate migrations/20180907025457_create_user_table.up.sql


```

