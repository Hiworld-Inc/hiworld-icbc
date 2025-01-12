<?php

return [
    'app_id' => env('ICBC_APP_ID', ''),
    'private_key' => env('ICBC_PRIVATE_KEY', ''),
    'icbc_public_key' => env('ICBC_PUBLIC_KEY', ''),
    'sign_type' => env('ICBC_SIGN_TYPE', 'RSA'),
    'charset' => env('ICBC_CHARSET', 'UTF-8'),
    'format' => env('ICBC_FORMAT', 'json'),
    'encrypt_key' => env('ICBC_ENCRYPT_KEY', ''),
    'encrypt_type' => env('ICBC_ENCRYPT_TYPE', ''),
    'ca' => env('ICBC_CA', ''),
    'password' => env('ICBC_PASSWORD', ''),
    
    // API 接口地址
    'api_url' => [
        'production' => 'https://gw.open.icbc.com.cn',
        'sandbox' => 'https://gw.open.icbc.com.cn/api/test', // 测试环境地址
    ],
    
    // 是否使用测试环境
    'sandbox' => env('ICBC_SANDBOX', false),
]; 