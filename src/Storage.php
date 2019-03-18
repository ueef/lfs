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
        $this->copy($path, $key);
    }

    public function isStored(string $key): bool
    {
        return is_dir($this->getDirPath($key));
    }

    public function delete(string $key)
    {
        $dir = $this->getDirPath($key);
        foreach (array_diff(scandir($dir), ['.', '..']) as $file) {
            unlink($dir . '/' . $file);
        }

        return rmdir($dir);
    }

    public function getUrl(string $key): string
    {
        return $this->getDirUrl($key) . '/' . $key;
    }

    public function getPath(string $key): string
    {
        return $this->getDirPath($key) . '/' . $key;
    }

    private function getDirUrl(string $key): string
    {
        return $this->dir . '/' . substr($key, 0, 2) . '/' . substr($key, 2, 2) . '/' . substr($key, 4);
    }

    private function getDirPath(string $key): string
    {
        return $this->root . $this->getDirUrl($key);
    }

    private function copy(string $path, string $key): void
    {
        if (!@copy($path, $this->getPath($key))) {
            throw new CannotCopyException(["cannot copy \"%s\" to \"%s\"", $path, $this->getPath($key)]);
        }
    }

    private function mkdir(string $key): void
    {
        $path = $this->getDirPath($key);
        if (is_dir($path)) {
            return;
        }

        if (!@mkdir($path, $this->making_mode, true) && !is_dir($path)) {
            throw new CannotMakeDirectoryException(["cannot make directory \"%s\"", $path]);
        }
    }

    private function correctPath(string $path): string
    {
        return '/' . trim($path, '/');
    }
}