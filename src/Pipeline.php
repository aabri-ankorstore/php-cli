<?php

declare(strict_types=1);

namespace Ankor;

use Closure;
use Ankor\Exceptions\RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Throwable;

final class Pipeline implements PipelineInterface
{
    private array $passable = [];
    private ContainerInterface $container;
    private array $pipes = [];
    private string $method = 'handle';

    /**
     * Create a new class instance.
     *
     * @param ContainerInterface|null $container
     */
    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function send(array $traveler): PipelineInterface
    {
        $this->passable = $traveler;

        return $this;
    }

    public function through(array $pipes): PipelineInterface
    {
        $this->pipes = is_array($pipes) ? $pipes : func_get_args();

        return $this;
    }

    public function via(string $method): PipelineInterface
    {
        $this->method = $method;

        return $this;
    }

    public function then(Closure $destination): array
    {
        $pipeline = array_reduce(
            array_reverse($this->pipes()),
            $this->carry(),
            $this->prepareDestination($destination)
        );
        return $pipeline($this->passable);
    }

    public function thenReturn(): array
    {
        return $this->then(function (array $passable) {
            return $passable;
        });
    }

    private function prepareDestination(Closure $destination): Closure
    {
        return function (array $passable) use ($destination): ?array {
            try {
                return $destination($passable);
            } catch (Throwable $e) {
                $this->handleException($e);
            }
        };
    }

    private function carry(): Closure
    {
        return function ($stack, $pipe) {
            return function ($passable) use ($stack, $pipe): ?array {
                try {
                    if (is_callable($pipe)) {
                        return $pipe($passable, $stack);
                    } elseif (! is_object($pipe)) {
                        [$name, $parameters] = $this->parsePipeString($pipe);
                        $pipe = $this->getContainer()->get($name);
                        $parameters = array_merge([$passable, $stack], $parameters);
                    } else {
                        $parameters = [$passable, $stack];
                    }
                    if (method_exists($pipe, $this->method)) {
                        $carry = $pipe->{$this->method}(...$parameters);
                        return $this->handleCarry($carry);
                    }
                    throw new \Exception("Class not found");
                } catch (Throwable $e) {
                    return $this->handleException($e);
                }
            };
        };
    }

    private function parsePipeString(string $pipe): array
    {
        [$name, $parameters] = array_pad(explode(':', $pipe, 2), 2, []);

        if (is_string($parameters)) {
            $parameters = explode(',', $parameters);
        }

        return [$name, $parameters];
    }

    private function pipes(): array
    {
        return $this->pipes;
    }

    private function getContainer(): ContainerInterface
    {
        if (!$this->container) {
            throw new RuntimeException('A container instance has not been passed to the Pipeline.');
        }

        return $this->container;
    }

    public function setContainer(ContainerInterface $container): self
    {
        $this->container = $container;

        return $this;
    }

    private function handleCarry(?array $carry): ?array
    {
        return $carry;
    }

    /**
     * @throws Throwable
     */
    private function handleException(Throwable $e): void
    {
        throw $e;
    }
}
