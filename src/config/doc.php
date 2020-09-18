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

    //是否继续记录返回缓存
    'is_cache'=>true,


    /**
     * 自动为你生成方法注释　解放你的双手
     * 方法替换　匹配到方法名称的时候会将　:value 替换成你的分组名称　key 为方法名称 value 为你制定的模板
     */
    'fun' => [
//        'index'=>'列表 :value (详细)',
    ],
];
