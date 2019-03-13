<?php

namespace Ueef\Lfs;

use Ueef\Lfs\Interfaces\StorageInterface;
use Ueef\Lfs\Exceptions\CannotCopyException;
use Ueef\Lfs\Exceptions\CannotMakeDirectoryException;

class Storage implements StorageInterface
{
    /** @var string */
    private $root;

    /** @var string */
    private $dir;

    /** @var integer */
    private $key_length;

    /** @var integer */
    private $making_mode;


    public function __construct(string $root, string $dir, int $makingMode = 0755)
    {
        $this->dir = $this->correctPath($dir);
        $this->root = $this->correctPath($root);
        $this->making_mode = $makingMode;
    }

    public function store(string $path, string $key): void
    {
        $this->mkdir($key);
        $this->link($path, $key);
    }

    public function isStored(string $key): bool
    {
        return file_exists($this->getPath($key));
    }

    public function getUrl(string $key): string
    {
        return $this->dir . preg_replace('/^(.{2})(.{2})(.+)$/', '/$1/$2/$3', $key);
    }

    public function getPath(string $key): string
    {
        return $this->root . $this->getUrl($key);
    }

    protected function link(string $path, string $key): void
    {
        if (!@copy($path, $this->getPath($key))) {
            throw new CannotCopyException(["cannot copy \"%s\" to \"%s\"", $path, $this->getPath($key)]);
        }
    }

    protected function mkdir(string $key): void
    {
        $dir = dirname($this->getPath($key));
        if (is_dir($dir)) {
            return;
        }

        if (!@mkdir($dir, $this->making_mode, true) && !is_dir($dir)) {
            throw new CannotMakeDirectoryException(["cannot make directory \"%s\"", $dir]);
        }
    }

    protected function correctPath(string $path): string
    {
        return '/' . trim($path, '/');
    }

    protected function generateKey(): string
    {
        $key = random_bytes($this->key_length);
        $key = bin2hex($key);

        return $key;
    }
}