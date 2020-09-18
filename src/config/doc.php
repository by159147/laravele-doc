<?php

return [
    //版本
    'v'=>1,
    //名称
    'name'=>env('APP_NAME'),
    //app名称
    'app_name'=>env('APP_NAME'),
    //请求地址
    'path'=>env('APP_URL'),
    //发送地址
    'send'=>'http://127.0.0.1:8000',

    'only'=>'api',

    //laravle版本
    'laravle_versions'=>8,

    'mysql' => ['mysql'],
    //是否执行迁移
    'is_migration'=>true,
];
