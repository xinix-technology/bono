<?php
require '../vendor/autoload.php';

$app = new \Bono\App(array(
    'autorun' => false,         // avoid autorun to explicit invoke run method
    'mode' => 'development',    // change this to production to release
));

$app->get('/', function() use ($app) {
    echo 'Hello world!';
});

$app->run();
