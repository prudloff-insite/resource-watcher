<?php

/*
 * This file is part of the Yo! Symfony Resource Watcher.
 *
 * (c) YoSymfony <http://github.com/yosymfony>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yosymfony\ResourceWatcher\Tests;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Yosymfony\ResourceWatcher\Crc32ContentHash;
use Yosymfony\ResourceWatcher\ResourceWatcher;
use Yosymfony\ResourceWatcher\ResourceCacheMemory;

class ResourceWatcherTest extends \PHPUnit_Framework_TestCase
{
    protected $tmpDir;
    protected $fs;

    public function setUp()
    {
        $this->tmpDir = sys_get_temp_dir() . '/resource-watchers-tests';
        $this->fs = new Filesystem();

        $this->fs->mkdir($this->tmpDir);
    }

    public function tearDown()
    {
        $this->fs->remove($this->tmpDir);
    }

    public function testHasChangesMustReturnFalseWithColdCache()
    {
        $finder = new Finder();
        $finder->files()
            ->name('*.txt')
            ->in($this->tmpDir);
        $resourceWatcher = $this->makeResourceWatcher($finder);

        $result = $resourceWatcher->findChanges();

        $this->assertFalse($result->hasChanges());
    }

    public function testHasChangesMustReturnTrueWhenNewFile()
    {
        $finder = new Finder();
        $finder->files()
            ->name('*.txt')
            ->in($this->tmpDir);
        $resourceWatcher = $this->makeResourceWatcher($finder);

        $resourceWatcher->findChanges();
        $this->fs->dumpFile($this->tmpDir . '/file1.txt', 'test');
        $result = $resourceWatcher->findChanges();

        $this->assertTrue($result->hasChanges());
    }

    public function testHasChangesMustReturnFalseAfterRebuildCache()
    {
        $finder = new Finder();
        $finder->files()
            ->name('*.txt')
            ->in($this->tmpDir);
        $resourceWatcher = $this->makeResourceWatcher($finder);

        $resourceWatcher->findChanges();
        $this->fs->dumpFile($this->tmpDir . '/file1.txt', 'test');
        $resourceWatcher->rebuild();
        $result = $resourceWatcher->findChanges();

        $this->assertFalse($result->hasChanges());
    }

    public function testFindChangesMustReturnANewFileWhenItIsCreated()
    {
        $finder = new Finder();
        $finder->files()
            ->name('*.txt')
            ->in($this->tmpDir);
        $resourceWatcher = $this->makeResourceWatcher($finder);

        $resourceWatcher->findChanges();
        $this->fs->dumpFile($this->tmpDir . '/file1.txt', 'test');
        $result = $resourceWatcher->findChanges();

        $this->assertCount(1, $result->getNewFiles());
    }

    public function testFindChangesMustReturnADeletedFileWhenItIsDeleted()
    {
        $finder = new Finder();
        $finder->files()
            ->name('*.txt')
            ->in($this->tmpDir);
        $resourceWatcher = $this->makeResourceWatcher($finder);

        $this->fs->dumpFile($this->tmpDir . '/file1.txt', 'test');
        $resourceWatcher->findChanges();
        $this->fs->remove($this->tmpDir . '/file1.txt');
        $result = $resourceWatcher->findChanges();

        $this->assertCount(1, $result->getDeletedFiles());
    }

    public function testFindChangesMustReturnAUpdatedFileWhenItIsModified()
    {
        $filename = $this->tmpDir . '/file1.txt';
        $finder = new Finder();
        $finder->files()
            ->name('*.txt')
            ->in($this->tmpDir);
        $resourceWatcher = $this->makeResourceWatcher($finder);

        $this->fs->dumpFile($filename, 'test');
        $resourceWatcher->findChanges();
        $this->fs->appendToFile($filename, 'update1');
        $result = $resourceWatcher->findChanges();

        $this->assertCount(1, $result->getUpdatedFiles());
    }

    public function testFindChangesMustReturnANewFileWhenANewDirectoryIsCreated()
    {
        $finder = new Finder();
        $finder->in($this->tmpDir);
        $resourceWatcher = $this->makeResourceWatcher($finder);

        $resourceWatcher->findChanges();
        $this->fs->mkdir($this->tmpDir . '/dir-test');
        $result = $resourceWatcher->findChanges();
        $newFiles = $result->getNewFiles();

        $this->assertCount(1, $newFiles);
        $this->assertEquals($this->tmpDir . '/dir-test', $newFiles[0]);
    }

    private function makeResourceWatcher(Finder $finder)
    {
        $cacheMemory = new ResourceCacheMemory();
        $contentHashCrc32 = new Crc32ContentHash();

        return new ResourceWatcher($cacheMemory, $finder, $contentHashCrc32);
    }
}
