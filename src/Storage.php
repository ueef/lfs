<?php

namespace Ueef\Lfs;

use Throwable;
use Exception;
use Ueef\Lfs\Interfaces\StorageInterface;
use Ueef\Lfs\Interfaces\GeneratorInterface;
use Ueef\Paths\Interfaces\DirInterface;

class Storage implements StorageInterface
{
    private DirInterface       $dir;
    private GeneratorInterface $generator;
    private int                $levelNumber;
    private int                $levelLength;

    public function __construct(DirInterface $dir, GeneratorInterface $generator, int $levelNumber = 2, int $levelLength = 2)
    {
        $this->dir = $dir;
        $this->generator = $generator;
        $this->levelNumber = $levelNumber;
        $this->levelLength = $levelLength;
    }

    public function store(string $path, ?string $key): string
    {
        if (null === $key) {
            $key = $this->generator->generate();
        }

        try {
            $success = copy($path, $this->getPath($key));
        } catch (Throwable $e) {
            throw new Exception(sprintf("cannot copy a file \"%s\" to \"%s\"", $path, $this->getPath($key)), 0, $e);
        }

        if (!$success) {
            throw new Exception(sprintf("cannot copy a file \"%s\" to \"%s\"", $path, $this->getPath($key)));
        }

        return $key;
    }

    public function isStored(string $key): bool
    {
        return file_exists($this->getPath($key));
    }

    public function getUrl(string $key): string
    {
        return $this->dir->getUrl($key, $this->getDir($key));
    }

    public function getPath(string $key): string
    {
        return $this->dir->getPath($key, $this->getDir($key));
    }

    private function getDir(string $key): string
    {
        if (strlen($key) < $this->levelNumber * $this->levelLength) {
            throw new Exception(sprintf("the key \"%s\" is too short, the min length of a key is %d", $key, $this->levelNumber * $this->levelLength));
        }

        $dir = "";
        for ($i=0; $i<$this->levelNumber; $i++) {
            $dir .= "/" . substr($key, $i*$this->levelLength, $this->levelLength);
        }

        return $dir;
    }
}