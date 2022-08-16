<?php

declare(strict_types=1);

namespace Ankor;

use Symfony\Component\Console\Application as BaseApplication;

final class App extends BaseApplication
{
    private string $name = 'Ankor';
    private string $version = '@package_version@';

    public function __construct(iterable $commands)
    {
        $commands = $commands instanceof \Traversable ? \iterator_to_array($commands) : $commands;

        foreach ($commands as $command) {
            $this->add($command);
        }
        parent::__construct($this->name, $this->version);
    }
}
