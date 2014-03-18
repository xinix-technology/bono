<?php

namespace Bono\Theme;

use Bono\App;

abstract class Theme {

    protected $extension = '.php';

    protected $overwrite = false;

    protected $baseDirectories = array(
        array(),
        array(),
        array(),
        array(),
        array(),
        array(),
        array(),
        array(),
        array(),
        array(),
    );

    public static function base($path = '') {
        return App::getInstance()->theme->resolveAssetPath($path);
    }

    public function __construct($config = array()) {
        $app = App::getInstance();

        $app->filter('about', function($key) use ($app) {
            $about = $app->config('app.about');
            return @$about[$key];
        });

        foreach ($config as $key => $value) {
            if (isset($this->$key)) {
                $this->$key = $value;
            }
        }


        if ($p = realpath(rtrim(App::getInstance()->config('bono.base.path'), DIRECTORY_SEPARATOR))) {
            $this->addBaseDirectory($p, 1);
        }

        $d = explode(DIRECTORY_SEPARATOR.'src', __DIR__);
        $this->addBaseDirectory($d[0]);

    }

    public function addBaseDirectory($p, $priority = 10) {
        $this->baseDirectories[$priority][] = $p;
    }

    public function resolve($template, $view = null) {
        $segments = explode('/', $template);
        $page = end($segments);


        foreach ($this->baseDirectories as $dirs) {
            foreach ($dirs as $dir) {
                if ($t = $this->tryTemplate($dir, $template, $view)) {
                    return $t;
                }

                if ($t = $this->tryTemplate($dir, $template.$this->extension, $view)) {
                    return $t;
                }
            }
        }

        if ($template[0] !== '_') {

            foreach ($this->baseDirectories as $dirs) {
                foreach ($dirs as $dir) {
                    if ($t = $this->tryTemplate($dir, 'shared'.DIRECTORY_SEPARATOR.$page, $view)) {
                        return $t;
                    }

                    if ($t = $this->tryTemplate($dir, 'shared'.DIRECTORY_SEPARATOR.$page.$this->extension, $view)) {
                        return $t;
                    }
                }
            }

        }

    }

    public function tryTemplate($dir, $template, $view) {
        $dir = rtrim($dir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'templates';

        if (is_readable($f = $dir.DIRECTORY_SEPARATOR.$template)) {
            if (isset($view)) {
                $view->setTemplatesDirectory($dir);
            }
            return $template;
        }
    }


    public function partial($template, $data) {
        if (empty($template)) {
            throw new \Exception('Cannot render null partial template.');
        }
        $app = App::getInstance();
        $Clazz = $app->config('bono.partial.view');

        $view = new $Clazz;
        $template = $this->resolve($template, $view);
        $view->replace($data);
        return $view->fetch($template);
    }

    public function resolveAssetPath($path = '') {
        $cwd = getcwd();

        foreach ($this->baseDirectories as $dirs) {
            foreach ($dirs as $dir) {
                $file = $dir.'/www/'.$path;

                $srcDir = dirname($file);
                $destDir = dirname($cwd.'/'.$path);

                if ($srcDir != $destDir) {
                    $this->copy($srcDir, $destDir);
                    break 2;
                }
            }
        }

        foreach ($this->baseDirectories as $dirs) {
            foreach ($dirs as $dir) {
                $file = $dir.'/www/'.$path;

                if (is_readable($file)) {
                    return \URL::base().$path;
                }
            }
        }
    }

    public function copy($source, $dest) {
        // Simple copy for a file
        if (is_file($source)) {
            if (file_exists($dest) && (!$this->overwrite || fileatime($source) < fileatime($dest))) {
                return true;
            }
            return copy($source, $dest);
        }

        // Make destination directory
        if (!is_dir($dest)) {
            @mkdir($dest, 0777, true);
        }

        // Loop through the folder
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            // Deep copy directories
            if ($dest !== "$source/$entry") {
                $this->copy("$source/$entry", "$dest/$entry");
            }
        }

       // Clean up
       $dir->close();
       return true;
    }
}