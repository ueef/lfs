<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
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
}