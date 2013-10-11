<?php
require '../vendor/autoload.php';

$app = new \Bono\App(array(
    'mode' => 'development',
    'config.path' => '../config',
    'ns' => '\\App'
));

$app->run();
