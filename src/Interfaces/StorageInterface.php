<?php
declare(strict_types=1);

namespace Ueef\Lfs\Interfaces;

interface StorageInterface
{
    public function storeFile(string $path, ?string $key = null): string;
    public function storeStream($stream, ?string $key = null): string;
    public function storeBinary(string $data, ?string $key = null): string;
    public function getUrl(string $key): string;
    public function getPath(string $key): string;
    public function isStored(string $key): bool;
}
