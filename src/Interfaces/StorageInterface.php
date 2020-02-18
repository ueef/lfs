<?php
declare(strict_types=1);

namespace Ueef\Lfs\Interfaces;

interface StorageInterface
{
    public function store(string $path, ?string $key): string;
    public function getUrl(string $key): string;
    public function getPath(string $key): string;
    public function isStored(string $key): bool;
}
