<?php

namespace Ueef\Lfs\Interfaces {

    interface StorageInterface
    {
        public function store(string $tmpPath): string;
        public function getUrl(string $key): string;
        public function getPath(string $key, bool $makeDir = false): string;
    }
}
