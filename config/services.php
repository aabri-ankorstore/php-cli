<?php declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ankor\App;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Filesystem\Filesystem;

return static function (ContainerConfigurator $configurator) {
    $services = $configurator->services()->defaults()->autowire()->autoconfigure();
    $services->instanceof(Command::class)->tag('command');
    $services->load('Ankor\\', '../src/*');

    $services->set(Filesystem::class)->public();

    // register the App
    $services->set(App::class)
        ->args([tagged_iterator('command')])
        ->public();
};