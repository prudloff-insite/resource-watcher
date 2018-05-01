A simple resource watcher for getting changes of your filesystem.

[![Build Status](https://travis-ci.org/yosymfony/resource-watcher.png?branch=master)](https://travis-ci.org/yosymfony/resource-watcher)
[![Latest Stable Version](https://poser.pugx.org/yosymfony/resource-watcher/v/stable.png)](https://packagist.org/packages/yosymfony/resource-watcher)

## Installation

Use [Composer](http://getcomposer.org/) to install this package:

```bash
composer require yosymfony/resource-watcher
```

## How to use?

This package uses [Symfony Finder](http://symfony.com/doc/current/components/finder.html)
to set the criteria to discover file changes.

```php
use Symfony\Component\Finder\Finder;
use Yosymfony\ResourceWatcher\Crc32ContentHash;
use Yosymfony\ResourceWatcher\ResourceWatcher;
use Yosymfony\ResourceWatcher\ResourceCacheFile;

$finder = new Finder();
$finder->files()
    ->name('*.php')
    ->depth(0)
    ->in(__DIR__);

$hashContent = new Crc32ContentHash();
$resourceCache = new ResourceCacheFile('/path-to-cache-file.php');
$watcher = new ResourceWatcher($resourceCache, $finder, $hashContent);

$watcher->findChanges();

// delete a file

$result = $watcher->findChanges();

$result->getDeletedResources() // array with the filename of deleted files. e.g: "/home/yosymfony/README.md"
```

## Finding changes

Every time the method `findChanges()` from the class `ResourceWatcher` is invoked,
it returns an object type `ResourceWatcherResult` with information about the
changes producced by the filesystem. The `ResourceWatcherResult` class has the following methods:

* `getNewFiles()`: Return an array with the paths of the new resources.
* `getDeteledFiles()`: Return an array with the paths of deleted resources.
* `getUpdatedFiles()`: Return an array with the paths of the updated resources.
* `hasChanges()`: Has changes in your resources?.

## Rebuild cache

To rebuild the resource cache uses `rebuild()` method from `ResourceWatcher`.

## Unit tests

You can run the unit tests with the following command:

```bash
$ cd your-path/resource-watcher
$ composer.phar install --dev
$ phpunit
```
