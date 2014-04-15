<?php

return array(
    // BONO
    'bono.prettifyURL' => true,
    'bono.providers' => array(
        '\\Norm\\Provider\\NormProvider',
    ),
    'bono.middlewares' => array(
        '\\Bono\\Middleware\\ControllerMiddleware',
        '\\Bono\\Middleware\\ContentNegotiatorMiddleware',
    ),
    'bono.contentNegotiator' => array(
        'extensions' => array(
            'json' => 'application/json',
        ),
        'views' => array(
            'application/json' => '\\Bono\\View\\JsonView',
        ),
    ),
    'bono.controllers' => array(
        'default' => '\\Norm\\Controller\\NormController',
        'mapping' => array(
            '/user'   => null,
        ),
    ),
);
