<?php

namespace Bono\Provider;

class CLIProvider extends Provider {

    public function initialize() {
        if (PHP_SAPI == 'cli') {
            $this->app->container->singleton('environment', function ($c) {
                return \Bono\CLI\Environment::getInstance();
            });

            $this->$app->notFound(function() use ($app) {
                $argv = $GLOBALS['argv'];

                echo "Undefined command\n";
            });

            $commands = $this->$app->config('bonocli.commands');

            foreach ($commands as $commandClass) {
                $command = new $commandClass();
                $command->initialize($app);
            }
        }
    }

}