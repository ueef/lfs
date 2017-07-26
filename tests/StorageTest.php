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

    /** @var string */
    private $root_dir;

    /** @var string */
    private $file_to_store;

    public function setUp()
    {
        $this->root_dir = sys_get_temp_dir() . '/storage';
        $this->file_to_store = $this->root_dir . 'file_to_store';

        mkdir($this->root_dir, 0755, true);
        touch($this->file_to_store);

        $this->storage = new Storage($this->root_dir);
    }

    public function tearDown()
    {
        rmdir($this->root_dir);
        unlink($this->file_to_store);
    }

    public function testCannotStoreNonexistentFile(): void
    {
        $this->expectException(NotExistsException::class);

        $this->storage->store(uniqid());
    }

    public function testCannotStoreInRootDirWithoutPermission(): void
    {
        chmod($this->root_dir, 0444);
        $this->expectException(CantMakeDirectoryException::class);

        $this->storage->store($this->file_to_store);
    }
}