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
    private static $storage;

    /** @var string */
    private static $root_dir;

    /** @var string */
    private static $file_to_store;

    public static function setUpBeforeClass()
    {
        self::$root_dir = sys_get_temp_dir() . '/storage';
        self::$file_to_store = sys_get_temp_dir() . '/file_to_store';

        mkdir(self::$root_dir, 0755, true);
        touch(self::$file_to_store);

        self::$storage = new Storage(self::$root_dir);
    }

    public static function tearDownAfterClass()
    {
        self::delTree(self::$root_dir);
        unlink(self::$file_to_store);
    }

    private static function delTree($dir)
    {
        $files = array_diff(scandir($dir), ['.','..']);
        foreach ($files as $file) {
            if (is_dir("$dir/$file")) {
                self::delTree("$dir/$file");
            } else {
                unlink("$dir/$file");
            }
        }

        return rmdir($dir);
    }

    public function testCannotStoreNonexistentFile(): void
    {
        $this->expectException(NotExistsException::class);
        self::$storage->store(uniqid());
    }

    public function testCannotStoreInRootDirWithoutPermission(): void
    {
        chmod(self::$root_dir, 0444);
        $this->expectException(CantMakeDirectoryException::class);

        self::$storage->store(self::$file_to_store);
    }

    public function testKeyIsNotEmptyString()
    {
        chmod(self::$root_dir, 0755);
        $key = self::$storage->store(self::$file_to_store);
        $this->assertInternalType('string', $key);
        $this->assertNotEmpty($key);

        return $key;
    }

    /**
     * @depends testKeyIsNotEmptyString
     */
    public function testUrlIsNotEmptyString($key)
    {
        $url = self::$storage->getUrl($key);
        $this->assertInternalType('string', $url);
        $this->assertNotEmpty($url);
    }

    /**
     * @depends testKeyIsNotEmptyString
     */
    public function testPathIsNotEmptyString($key)
    {
        $path = self::$storage->getPath($key);
        $this->assertInternalType('string', $path);
        $this->assertNotEmpty($path);
    }
}