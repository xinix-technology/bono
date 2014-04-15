<?php

require __DIR__ . '/../../vendor/autoload.php';

$list   = null;
$app    = \Bono\App::getInstance();
$config = array();
$path   = __DIR__ . '/../config/chunks';

if ($dh = opendir($path)) {
    while (($fileName = readdir($dh)) !== false) {
        if (is_file($path . DIRECTORY_SEPARATOR . $fileName)) {
            $content = require($path . DIRECTORY_SEPARATOR . $fileName);
            $config = array_merge_recursive($config, $content);
        }
    }

    closedir($dh);
}

$config = array_merge_recursive(array(
    'autorun'            => false,
    'app.templates.path' => __DIR__ . '/../templates',
    'config.path'        => __DIR__ . '/../config',
), $config);

$app = new Bono\App($config);

$app->get('/', function () {
    return true;
});

$app->run();
