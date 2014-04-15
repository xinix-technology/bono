<?php

use \Norm\Schema\String;
use \Norm\Schema\Password;

return array(
    // NORM
    'norm.databases' => array(
        'mongo' => array(
            'driver'   => '\\Norm\\Connection\\MongoConnection',
            'database' => 'devel',
        ),
    ),
    'norm.collections' => array(
        'default' => array(
            'observers' => array(
                '\\Norm\\Observer\\Ownership' => array(),
                '\\Norm\\Observer\\Timestampable' => array(),
            ),
        ),
        'mapping' => array(
            'User' => array(
                'observers' => array(
                    '\\Norm\\Observer\\Hashed' => array(
                        'fields'  => array('password'),
                        'algo'    => PASSWORD_BCRYPT,
                        'options' => array('cost' => 12),
                    )
                ),
                'hidden' => array('password'),
                'schema' => array(
                    'pengenal'   => String::getInstance('pengenal')->filter('trim|required|unique:User,pengenal'),
                    'nama_awal'  => String::getInstance('nama_awal')->filter('trim|required'),
                    'nama_akhir' => String::getInstance('nama_akhir')->filter('trim|required'),
                    'password'   => Password::getInstance('password')->filter('trim|required|confirmed')
                ),
            ),
        ),
    ),
);
