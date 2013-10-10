<?php

namespace Bono\Provider;

class CLIProvider implements Provider {

    public function initialize($app) {
        if (PHP_SAPI == 'cli') {
            $app->container->singleton('environment', function ($c) {
                return \Bono\CLI\Environment::getInstance();
            });

            $app->notFound(function() use ($app) {
                $argv = $GLOBALS['argv'];

                echo "Command is not defined\n";
            });

            $commands = $app->config('bonocli.commands');

            foreach ($commands as $commandClass) {
                $command = new $commandClass();
                $command->initialize($app);
            }
        }
    }

}