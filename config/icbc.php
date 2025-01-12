<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ICBC 配置
    |--------------------------------------------------------------------------
    */

    // 应用ID
    'app_id' => env('ICBC_APP_ID', ''),

    // 商户私钥
    'private_key' => env('ICBC_PRIVATE_KEY', dirname(__DIR__) . '/private.pem'),

    // ICBC公钥
    'icbc_public_key' => env('ICBC_PUBLIC_KEY', dirname(__DIR__) . '/public.pem'),

    // 签名方式: RSA/RSA2/SM2/CA
    'sign_type' => env('ICBC_SIGN_TYPE', 'RSA2'),

    // 字符集
    'charset' => 'UTF-8',

    // 接口格式
    'format' => 'json',

    // 加密密钥
    'encrypt_key' => env('ICBC_ENCRYPT_KEY', ''),

    // 加密方式
    'encrypt_type' => 'AES',

    // 证书
    'ca' => env('ICBC_CA', ''),

    // 证书密码
    'password' => env('ICBC_PASSWORD', ''),

    // 是否沙箱环境
    'sandbox' => env('ICBC_SANDBOX', false),

    // API 网关
    'gateway' => env('ICBC_GATEWAY', env('ICBC_SANDBOX', false) 
        ? 'https://apipcs.dccnet.com.cn'  // 测试环境网关
        : 'https://gw.open.icbc.com.cn'),  // 生产环境网关
]; 