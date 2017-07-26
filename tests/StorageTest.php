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
        $this->file_to_store = sys_get_temp_dir() . '/file_to_store';

        mkdir($this->root_dir, 0755, true);
        touch($this->file_to_store);

        $this->storage = new Storage($this->root_dir);
    }

    public function tearDown()
    {
        $this->delTree($this->root_dir);
        unlink($this->file_to_store);
    }

    private function delTree($dir)
    {
        $files = array_diff(scandir($dir), ['.','..']);
        foreach ($files as $file) {
            if (is_dir($dir/$file)) {
                $this->delTree("$dir/$file");
            } else {
                unlink("$dir/$file");
            }
        }

        return rmdir($dir);
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

    public function testKeyIsNotEmptyString()
    {
        $key = $this->storage->store($this->file_to_store);
        $this->assertInternalType('string', $key);
        $this->assertNotEmpty($key);

        return $key;
    }

    public function testUrlIsNotEmptyString($key)
    {
        $url = $this->storage->getUrl($key);
        $this->assertInternalType('string', $url);
        $this->assertNotEmpty($url);
    }

    public function testPathIsNotEmptyString($key)
    {
        $path = $this->storage->getPath($key);
        $this->assertInternalType('string', $path);
        $this->assertNotEmpty($path);
    }

    public function testPathIsHardLinkOfOriginFile()
    {
        $inodeOfOriginalFile = stat($this->file_to_store)['ino'];
        $keyOfStoredFile = $this->storage->store($this->file_to_store);
        $pathOfStoredFile = $this->storage->getPath($keyOfStoredFile);
        $inodeOfStoredFile = stat($pathOfStoredFile)['ino'];

        $this->assertEquals($inodeOfOriginalFile, $inodeOfStoredFile);
    }
}