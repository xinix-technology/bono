<?php

namespace Bono\Provider;

class CLIProvider extends Provider {

    public function initialize() {
        if (PHP_SAPI === 'cli') {
            $this->app->container->singleton('environment', function ($c) {
                return \Bono\CLI\Environment::getInstance();
            });

            $this->app->notFound(function() {
                $argv = $GLOBALS['argv'];

                echo "Undefined command\n";
            });


            $this->app->error(function($err) {
                echo $err->getTraceAsString();
                echo "\n\n";
                echo "Done with errors\n";
            });

            $commands = $this->app->config('bonocli.commands');
            if ($commands) {
                foreach ($commands as $commandClass) {
                    $command = new $commandClass();
                    $command->initialize($this->app);
                }
            }
        }
    }

}
