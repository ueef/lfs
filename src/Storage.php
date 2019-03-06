<?php

namespace Ueef\Lfs;

use Ueef\Lfs\Interfaces\StorageInterface;
use Ueef\Lfs\Exceptions\CannotLinkException;
use Ueef\Lfs\Exceptions\FileDoesNotExistException;
use Ueef\Lfs\Exceptions\CannotCreateDirectoryException;

class Storage implements StorageInterface
{
    /** @var string */
    private $root;

    /** @var string */
    private $dir;

    /** @var integer */
    private $key_length;

    /** @var integer */
    private $creation_mode;


    public function __construct(string $root, string $dir, int $keyLength = 12, int $creationMode = 0755)
    {
        $this->dir = $this->correctPath($dir);
        $this->root = $this->correctPath($root);
        $this->key_length = $keyLength;
        $this->creation_mode = $creationMode;
    }

    public function store(string $srcPath): string
    {
        if (!file_exists($srcPath)) {
            throw new FileDoesNotExistException(["file \"%s\" doesn't exist", $srcPath]);
        }

        $key = $this->generateKey();
        $dstPath = $this->getPath($key);

        $dirPath = dirname($dstPath);
        if (!is_dir($dirPath) && !@mkdir($dirPath, $this->creation_mode, true) && !is_dir($dirPath)) {
            throw new CannotCreateDirectoryException(["cannot created directory \"%s\"", $dirPath]);
        }

        if (!@link($srcPath, $dstPath)) {
            throw new CannotLinkException(["cannot link \"%s\" to \"%s\"", $srcPath, $dstPath]);
        }

        return $key;
    }

    public function getUrl(string $key): string
    {
        return $this->dir . preg_replace('/^(.{2})(.{2})(.+)$/', '/$1/$2/$3', $key);
    }

    public function getPath(string $key): string
    {
        return $this->root . $this->getUrl($key);
    }

    private function generateKey(): string
    {
        $key = random_bytes($this->key_length);
        $key = base64_encode($key);
        $key = strtr($key, '+/', '-_');
        $key = rtrim($key, '=');

        return $key;
    }

    private function correctPath(string $path): string
    {
        return '/' . trim($path, '/');
    }
}