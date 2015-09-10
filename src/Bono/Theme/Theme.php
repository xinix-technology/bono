<?php

/**
 * Bono - PHP5 Web Framework
 *
 * MIT LICENSE
 *
 * Copyright (c) 2014 PT Sagara Xinix Solusitama
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @category   PHP_Framework
 * @package    Bono
 * @subpackage Theme
 * @author     Ganesha <reekoheek@gmail.com>
 * @copyright  2014 PT Sagara Xinix Solusitama
 * @license    https://raw.github.com/xinix-technology/bono/master/LICENSE MIT
 * @version    0.10.0
 * @link       http://xinix.co.id/products/bono
 */
namespace Bono\Theme;

use Bono\App;
use Bono\Helper\URL;

/**
 * Theme
 *
 * @category   PHP_Framework
 * @package    Bono
 * @subpackage View
 * @author     Ganesha <reekoheek@gmail.com>
 * @copyright  2014 PT Sagara Xinix Solusitama
 * @license    https://raw.github.com/xinix-technology/bono/master/LICENSE MIT
 * @version    0.10.0
 * @link       http://xinix.co.id/products/bono
 */
abstract class Theme
{
    protected $view = null;

    protected $resources = array(
        'head.css' => array(),
        'head.js' => array(),
        'foot.css' => array(),
        'foot.js' => array(),
    );

    protected $extension = '.php';

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

    protected $options = array();

    /**
     * [base description]
     *
     * @param string $path [description]
     *
     * @return [type] [description]
     */
    public static function base($path = '')
    {
        return App::getInstance()->theme->resolveAssetPath($path);
    }

    /**
     * Constructor
     *
     * @param array $options Options of theme
     */
    public function __construct(array $options = array())
    {
        $app = App::getInstance();
        $that = $this;

        // $appConfig = $app->config('application');

        // $app->filter('page.title', function ($key) use ($appConfig) {
        //     if (isset($appConfig['title'])) {
        //         return $appConfig['title'];
        //     }

        //     return $key;
        // });

        // foreach ($options as $key => $value) {
        //     if (isset($this->$key)) {
        //         $this->$key = $value;
        //         unset($options[$key]);
        //     }
        // }

        unset($options['class']);
        $this->options = $options;

        if ($p = realpath(rtrim($app->config('bono.base.path'), DIRECTORY_SEPARATOR))) {
            $this->addBaseDirectory($p, 1);
        }

        $d = explode(DIRECTORY_SEPARATOR.'src', __DIR__);
        $this->addBaseDirectory($d[0]);

        $app->filter('theme.head.css', function ($data) use ($that) {
            $html = array(
                "\n<!-- head.css -->",
            );
            foreach ($that->resources['head.css'] as $res) {
                $html[] = '<link rel="stylesheet" href="'.Theme::base($res).'">';
            }

            return implode("\n", $html)."\n";
        });

        $app->filter('theme.foot.css', function ($data) use ($that) {
            $html = array(
                "\n<!-- foot.css -->",
            );
            foreach ($that->resources['foot.css'] as $res) {
                $html[] = '<link rel="stylesheet" href="'.Theme::base($res).'">';
            }

            return implode("\n", $html)."\n";
        });

        $app->filter('theme.head.js', function ($data) use ($that) {
            $html = array(
                "\n<!-- head.js -->",
            );
            foreach ($that->resources['head.js'] as $res) {
                $html[] = '<script type"text/javascript" src="'.Theme::base($res).'"></script>';
            }

            return implode("\n", $html)."\n";
        });

        $app->filter('theme.foot.js', function ($data) use ($that) {
            $html = array(
                "\n<!-- foot.js -->",
            );
            foreach ($that->resources['foot.js'] as $res) {
                $html[] = '<script type"text/javascript" src="'.Theme::base($res).'"></script>';
            }

            return implode("\n", $html)."\n";
        });

    }

    /**
     * [addBaseDirectory description]
     *
     * @param [type]  $p        [description]
     * @param integer $priority [description]
     *
     * @return [type] [description]
     */
    public function addBaseDirectory($p, $priority = 10)
    {
        $this->baseDirectories[$priority][] = $p;
    }

    /**
     * [resolve description]
     *
     * @param [type] $template [description]
     * @param [type] $view     [description]
     *
     * @return [type] [description]
     */
    public function resolve($template, $view = null)
    {
        if (!$template) {
            return '';
        }

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

    /**
     * [tryTemplate description]
     *
     * @param [type] $dir      [description]
     * @param [type] $template [description]
     * @param [type] $view     [description]
     *
     * @return [type] [description]
     */
    public function tryTemplate($dir, $template, $view)
    {
        $dir = rtrim($dir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'templates';

        if (is_readable($dir.DIRECTORY_SEPARATOR.$template) && is_file($dir.DIRECTORY_SEPARATOR.$template)) {
            if (isset($view)) {
                $view->setTemplatesDirectory($dir);
            }

            return $template;
        }
    }

    /**
     * Partial generate view
     *
     * @param string $template Template slug
     * @param array  $data     Data
     *
     * @return [type] [description]
     */
    public function partial($template, array $data = array())
    {
        $app = App::getInstance();
        $Clazz = $app->config('bono.partial.view');

        $view = new $Clazz();
        $t = $this->resolve($template, $view);

        if (empty($t)) {
            throw new \Exception('Cant resolve template "'.$template.'"');
        }
        $view->replace($data);

        return $view->fetch($t);
    }

    /**
     * [resolveAssetPath description]
     *
     * @param string $path [description]
     *
     * @return [type] [description]
     */
    public function resolveAssetPath($path = '')
    {
        $cwd = getcwd();

        foreach ($this->baseDirectories as $dirs) {
            foreach ($dirs as $dir) {
                $file = $dir.'/www/'.$path;

                $srcDir = dirname($file);
                $destDir = dirname($cwd.'/'.$path);

                if (!is_dir($srcDir)) {
                    continue;
                }

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
                    return URL::base().$path;
                }
            }
        }
    }

    /**
     * [copy description]
     *
     * @param [type] $source [description]
     * @param [type] $dest   [description]
     *
     * @return [type] [description]
     */
    public function copy($source, $dest)
    {
        // Simple copy for a file
        if (is_file($source)) {
            if (file_exists($dest) && (!empty($this->options['overwrite']) || fileatime($source) < fileatime($dest))) {
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

    public function getView()
    {
        if (is_null($this->view)) {
            $app = \App::getInstance();
            $viewClass = $app->config('view');
            $this->view = ($viewClass instanceof \Slim\View) ? $viewClass : new $viewClass();
        }

        return $this->view;
    }

    public function option($key = null)
    {
        if (func_num_args() ===  0) {
            return $this->options;
        } elseif (isset($this->options[$key])) {
            return $this->options[$key];
        }
    }
}
