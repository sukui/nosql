<?php

return [
    'default_write' => [
        'engine'=> 'redis',
        'host' => '127.0.0.1',
        'port' => 6379,
        'pool'  => [
            'maximum-connection-count' => 10,
            'minimum-connection-count' => 1,
            'keeping-sleep-time' => 10,
            'init-connection'=> 1,
        ],
    ],
    'default_timeout' => [
        'engine'=> 'redis',
        'host' => '10.9.22.85',
        'port' => 6602,
        'timeout' => 1,
        'pool'  => [
            'maximum-connection-count' => 10,
            'minimum-connection-count' => 1,
            'keeping-sleep-time' => 10,
            'init-connection'=> 1,
        ],
    ],
//    'uuid' => [
//        'engine'=> 'redis',
//        'host' =>  UUID_HOST,
//        'port' => UUID_PORT,
//        'pool'  => [
//            'maximum-connection-count' => '50',
//            'minimum-connection-count' => 1,
//            'keeping-sleep-time' => '10',
//            'init-connection'=> 1,
//        ],
//    ],
];
