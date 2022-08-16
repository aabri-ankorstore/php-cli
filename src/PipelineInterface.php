<?php

declare(strict_types=1);

namespace Ankor;

use Closure;

interface PipelineInterface
{
    public function send(array $traveler): PipelineInterface;

    public function through(array $pipes): PipelineInterface;

    public function via(string $method): PipelineInterface;

    public function then(Closure $destination): array;

    public function thenReturn(): array;
}
