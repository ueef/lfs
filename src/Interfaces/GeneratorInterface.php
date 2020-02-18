<?php

declare(strict_types=1);

namespace Ueef\Lfs\Interfaces;

interface GeneratorInterface
{
    public function generate(): string;
}