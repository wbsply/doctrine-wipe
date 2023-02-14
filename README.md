# Drop and truncate database tables via CLI, for Neos Flow

Delete all data and tables available from the Doctrine connection. Using the configured platforms `DROP` and `TRUNCATE` query statements.

A quick help, if you need to re-apply all migrations or re-import a dataset to certain tables

These commands are **DESTRUCTIVE**.

## Installation

```
composer require websupply/doctrine-wipe --dev
```

## All commands supports `--dry-run`

All commands supports a `--dry-run` command, that will output the statements, but not execute them.

Output would look like this

````shell
> ./flow doctrine:drop --table products --dry-run
--------------------------------------
 Dry run - no statements are executed
--------------------------------------

[QUERY]: "SET foreign_key_checks = 0"
[QUERY]: "DROP TABLE products"
[QUERY]: "SET foreign_key_checks = 1"
````

## The `drop` command

You can drop one or all tables with the command

`./flow doctrine:drop`

One of the following arguments are required

`--all` will drop all tables present in the database (no looking up ORM stuff - this is direct database)

`--table <table>` will drop only the given table


**Example:**
```shell
> ./flow doctrine:drop --all

[QUERY]: "SET foreign_key_checks = 0"
[QUERY]: "DROP TABLE `companies`"
[QUERY]: "DROP TABLE `contacts`"
[QUERY]: "DROP TABLE `employees`"
[QUERY]: "DROP TABLE `flow_doctrine_migrationstatus`"
[QUERY]: "DROP TABLE `neos_flow_mvc_routing_objectpathmapping`"
[QUERY]: "DROP TABLE `neos_flow_resourcemanagement_persistentresource`"
[QUERY]: "DROP TABLE `neos_flow_security_account`"
[QUERY]: "DROP TABLE `products`"
[QUERY]: "DROP TABLE `scopes`"
[QUERY]: "DROP TABLE `tasks`"
[QUERY]: "DROP TABLE `users`"
[QUERY]: "SET foreign_key_checks = 1"
```

## The `truncate` command


## The `drop` command

You can empty/truncate the rows from one or all tables with the command

`./flow doctrine:truncate`

One of the following arguments are required

`--all` truncate all tables present in the database (no looking up ORM stuff - this is direct database)

`--table <table>` will truncate only the given table


**Example:**
```shell
> ./flow doctrine:truncate --all
[QUERY]: "SET foreign_key_checks = 0"
[QUERY]: "TRUNCATE companies"
[QUERY]: "TRUNCATE contacts"
[QUERY]: "TRUNCATE employees"
[QUERY]: "TRUNCATE neos_flow_mvc_routing_objectpathmapping"
[QUERY]: "TRUNCATE neos_flow_resourcemanagement_persistentresource"
[QUERY]: "TRUNCATE neos_flow_security_account"
[QUERY]: "TRUNCATE products"
[QUERY]: "TRUNCATE scopes"
[QUERY]: "TRUNCATE tasks"
[QUERY]: "TRUNCATE users"
[QUERY]: "SET foreign_key_checks = 1"
```

### The Doctrine migration table

The `flow_doctrine_migrationstatus` table is not truncated, even when passing the `--all` argument.

If you need to truncate this, our guess is, you looking at dropping the whole table and reapply migration.


## Support and sponsoring
Work on this package is supported by the Danish web company **WebSupply ApS** 
