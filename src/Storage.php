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
        return file_exists($this->getPath($key));
    }

    public function delete(string $key): void
    {
        $path = dirname($this->getPath($key));
        $dir = opendir($path);
        while (true) {
            $file = readdir($dir);
            if (false === $file) {
                break;
            } elseif ('.' == $file || '..' == $file) {
                continue;
            }

            if (0 == strpos($file, $key)) {
                unlink($path . '/' . $file);
            }
        }
    }

    public function getUrl(string $key): string
    {
        return $this->dir . '/' . substr($key, 0, 2) . '/' . $key;
    }

    public function getPath(string $key): string
    {
        return $this->root . $this->getUrl($key);
    }

    private function copy(string $path, string $key): void
    {
        if (!@copy($path, $this->getPath($key))) {
            throw new CannotCopyException(["cannot copy \"%s\" to \"%s\"", $path, $this->getPath($key)]);
        }
    }

    private function mkdir(string $key): void
    {
        $path = dirname($this->getPath($key));
        if (!is_dir($path) && !@mkdir($path, $this->making_mode, true) && !is_dir($path)) {
            throw new CannotMakeDirectoryException(["cannot make directory \"%s\"", $path]);
        }
    }

    private function correctPath(string $path): string
    {
        return '/' . trim($path, '/');
    }
}