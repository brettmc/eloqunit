# eloqunit
[![Latest Stable Version](https://img.shields.io/packagist/v/eloqunit/eloqunit.svg?style=flat-square)](https://packagist.org/packages/eloqunit/eloqunit)
[![Build Status](https://travis-ci.com/brettmc/eloqunit.svg?branch=master)](https://travis-ci.com/brettmc/eloqunit)
[![Coverage Status](https://coveralls.io/repos/github/brettmc/eloqunit/badge.svg?branch=master)](https://coveralls.io/github/brettmc/eloqunit?branch=master)

An eloquent-based database testing library for phpunit, inspired by dbunit.

This works well with Slim Framework (and possibly others) which allows you to create an application, then dispatch multiple requests
to it. By using database transactions for each test, you do not need to "tear down" data from prior tests, nor deal with artifacts
left over from failed tests.

## Features

* runs test cases inside a database transaction
* provides assertions
* provides a `seed` method to quickly populate database tables
* support for null/not null on assertions

## Installation

Install via composer:

```
$ composer require eloqunit/eloqunit
```

## Usage

Instead of extending `PHPUnit\Framework\TestCase`, have your tests extend `Eloqunit\TestCase`.
You need to provide a `getDatabase()` method, which returns a `Illuminate\Database\Capsule\Manager`, assertions, seeding etc
are executed against this database.
This only works if the database is the same one as used in the the system under test (eg, they are shared through a DI container).

## Methods

### `seed(string $table, array $data)`:

Seed $table with rows contained in $data.

```php
$this->seed('mytable', [
  ['id' => '1', 'name' => 'row.one', 'active' => true, 'foo' => null],
  ['2', 'row.two', false, 'bar'],
]);

```

### `seedTables(array $data)`:

Seed multiple tables. Array keys represent table names, and array values represent table rows.

```php
$this->seedTables(
    'mytable' => [
        ['id' => 1, 'name' => 'row.one', 'active' => true],
        [2, 'row.two', true],
        [3, 'row.three', true],
    ],
    'myothertable' => [
        ['key' => 'foo', 'description' => 'description of foo'],
        ['bar', 'bar description'],
        ['baz', 'baz description'],
    ],
);
```

### `assertRowCount(int $expected, string $table, array $where, string $message)`:

Assert that `$table` contains `$expected` number of rows. Optionally, filtered by $where.

```php
$this->assertRowCount(1, 'mytable', ['active' => true, 'foo' => Eloqunit\Constraint::IsNull]);
$this->assertRowCount(1, 'mytable', ['active' => false, 'foo' => ELoqunit\Constraint::IsNotNull];
$this->assertRowCount(2, 'mytable');
```

### `assertRowExists(string $table, array $where, string $message)`

Assert that a row exists in $table. Optionally, filtered by $where.

```php
$this->assertRowExists('mytable', ['id' => 1]);
$this->assertRowExists('mytable', ['foo' => Eloqunit\Constraint::IsNotNull]);
```

### `assertRowMatches(string $table, array $where, array $fields)`

Assert that the first row in $table which matches $where, contains fields matching $fields.

```php
$this->assertRowMatches('mytable', ['id' => 1], ['name' => 'row.one', 'active' => true]);
```
