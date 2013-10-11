<?php

namespace Bono\CLI\Command;

class Init {
    private $app;

    private function copy($dir, $toDir) {
        $dh = opendir($dir);
        if ($dh) {
            while (false !== ($entry = readdir($dh))) {
                if ($entry != '.' && $entry != '..') {
                    if (is_dir($dir.'/'.$entry)) {
                        $this->copy($dir.'/'.$entry, $toDir.'/'.$entry);
                    } else {
                        echo "Copying ".$toDir.'/'.$entry." ...\n";
                        @mkdir($toDir, 0777, true);
                        $fileContent = file_get_contents($dir.'/'.$entry);
                        file_put_contents($toDir.'/'.$entry, $fileContent);
                    }
                }
            }
            closedir($dh);
        }
    }

    public function doInit() {
        $skelDir = './vendor/xinix-technology/bono/skel';
        if (!is_dir($skelDir) || !is_readable($skelDir)) {
            $skelDir = './skel';
        }

        $this->copy($skelDir, '.');

    }

    public function initialize($app) {
        $this->app = $app;
        $that = $this;

        $app->get('/init', function() use ($that, $app) {
            $that->doInit($app);
        });
    }
}