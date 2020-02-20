<?php

declare(strict_types=1);

namespace Ueef\Lfs\Generators;

use Ueef\Lfs\Interfaces\GeneratorInterface;

class RandomGenerator implements GeneratorInterface
{
    private int $length;

    public function __construct(int $length)
    {
        $this->length = $length;
    }

    public function generate(): string
    {
        $key = random_bytes($this->length*2);
        $key = base64_encode($key);
        $key = strtr($key, '+/', '-_');
        $key = rtrim($key, '=');

        return substr($key, 0, $this->length);
    }
}