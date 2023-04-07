# Laravel Model joins

[![Latest Version on Packagist](https://img.shields.io/packagist/v/joelharkes/laravel-model-joins.svg?style=flat-square)](https://packagist.org/packages/joelharkes/laravel-model-joins)
[![Build status](https://github.com/joelharkes/laravel-model-joins/actions/workflows/run-tests.yml/badge.svg)](https://github.com/joelharkes/laravel-model-joins/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/joelharkes/laravel-model-joins.svg?style=flat-square)](https://packagist.org/packages/joelharkes/laravel-model-joins)

```php
User::query()->joinMany(Transaction::class);
```

## Installation

You can install the package via composer:

```bash
composer require joelharkes/laravel-model-joins
```

## Usage

examples:
```php
User::query()->joinMany(Transaction::class);
User::query()->joinMany(Transaction::query()->withoutTrashed());
Transaction::query()->joinOne(User::class);
Transaction::query()->joinOne(User::query()->where('is_manager', true));
```

### types of joins

Join a one-to-many relationship:

```php
User::query()->joinMany(Transaction::class);
```

Join a x-to-one relationship:

```php
Transaction::query()->joinOne(User::class);
```

All of these work well with `SoftDeletes` no matter if you join left, write or inner.

### Join queries

```php
Transaction::query()
    ->joinOne(User::query()->where('is_manager', true));
```
