<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Ueef\Lfs\Exceptions\CantMakeDirectoryException;
use Ueef\Lfs\Storage;
use Ueef\Lfs\Exceptions\NotExistsException;

/**
 * @covers Email
 */
final class StorageTest extends TestCase
{
    /**
     * @var \Ueef\Lfs\Storage
     */
    private $storage;

    public function setUp()
    {
        $this->storage = new Storage(sys_get_temp_dir());
    }

    public function testCannotStoreNonexistentFile(): void
    {
        $this->expectException(NotExistsException::class);

        $this->storage->store(uniqid());
    }

    public function testCannotStoreInRootDirWithoutPermission(): void
    {
        $rootDir = sys_get_temp_dir() . '/storage';
        mkdir($rootDir, 0444, true);
        $fileToStore = sys_get_temp_dir() . '/file_to_store';
        touch($fileToStore);

        $this->expectException(CantMakeDirectoryException::class);

        $storage = new Storage($rootDir);
        $storage->store($fileToStore);
    }
}