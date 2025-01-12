<?php

namespace Hiworld\LaravelIcbc\Services;

use Hiworld\LaravelIcbc\lib\DefaultIcbcClient;

class IcbcService
{
    protected $config;
    protected $client;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->client = new DefaultIcbcClient(
            $config['app_id'],
            $this->formatPrivateKey($config['private_key']),
            $config['sign_type'],
            $config['charset'],
            $config['format'],
            $this->formatPublicKey($config['icbc_public_key']),
            $config['encrypt_key'],
            $config['encrypt_type'],
            $config['ca'],
            $config['password']
        );
    }

    /**
     * 格式化私钥
     *
     * @param string $privateKey
     * @return string
     */
    protected function formatPrivateKey($privateKey)
    {
        // 如果是文件路径，则读取文件内容
        if (file_exists($privateKey)) {
            $privateKey = file_get_contents($privateKey);
        }

        // 如果已经是PEM格式，直接返回
        if (strpos($privateKey, '-----BEGIN RSA PRIVATE KEY-----') !== false) {
            return $privateKey;
        }

        // 如果是PKCS#8格式，直接返回
        if (strpos($privateKey, '-----BEGIN PRIVATE KEY-----') !== false) {
            return $privateKey;
        }

        // 移除所有空白字符
        $privateKey = preg_replace('/\s+/', '', $privateKey);
        
        // 移除现有的 PEM 头尾
        $privateKey = preg_replace('/-+BEGIN.*KEY-+/', '', $privateKey);
        $privateKey = preg_replace('/-+END.*KEY-+/', '', $privateKey);

        // 尝试 PKCS#8 格式
        $pem = "-----BEGIN PRIVATE KEY-----\n" .
            chunk_split($privateKey, 64, "\n") .
            "-----END PRIVATE KEY-----";

        // 验证格式
        if (@openssl_pkey_get_private($pem)) {
            return $pem;
        }

        // 尝试 RSA 格式
        $pem = "-----BEGIN RSA PRIVATE KEY-----\n" .
            chunk_split($privateKey, 64, "\n") .
            "-----END RSA PRIVATE KEY-----";

        // 验证格式
        if (@openssl_pkey_get_private($pem)) {
            return $pem;
        }

        throw new \Exception('Invalid private key format');
    }

    /**
     * 格式化公钥
     *
     * @param string $publicKey
     * @return string
     */
    protected function formatPublicKey($publicKey)
    {
        // 如果是文件路径，则读取文件内容
        if (file_exists($publicKey)) {
            $publicKey = file_get_contents($publicKey);
        }

        // 如果已经是PEM格式，直接返回
        if (strpos($publicKey, '-----BEGIN PUBLIC KEY-----') !== false) {
            return $publicKey;
        }

        // 移除所有空白字符
        $publicKey = preg_replace('/\s+/', '', $publicKey);
        
        // 移除现有的 PEM 头尾
        $publicKey = preg_replace('/-+BEGIN.*KEY-+/', '', $publicKey);
        $publicKey = preg_replace('/-+END.*KEY-+/', '', $publicKey);

        // 格式化为 PEM 格式
        $pem = "-----BEGIN PUBLIC KEY-----\n" .
            chunk_split($publicKey, 64, "\n") .
            "-----END PUBLIC KEY-----";

        // 验证格式
        if (!@openssl_pkey_get_public($pem)) {
            throw new \Exception('Invalid public key format');
        }

        return $pem;
    }

    /**
     * 支付接口
     *
     * @param array $params 支付参数
     * @return string
     */
    public function pay(array $params)
    {
        $request = [
            'method' => 'POST',
            'api' => '/api/cardbusiness/aggregatepay/b2c/online/consumepurchase/V1',
            'biz_content' => array_merge([
                'mer_id' => $this->config['app_id'],
                'timestamp' => date('YmdHis'),
                'currency' => 'CNY',
            ], $params),
            'isNeedEncrypt' => false,
            'extraParams' => []
        ];

        return $this->execute($request, uniqid());
    }

    /**
     * 订单查询
     *
     * @param array $params 查询参数
     * @return string
     */
    public function query(array $params)
    {
        $request = [
            'method' => 'POST',
            'api' => '/api/cardbusiness/aggregatepay/b2c/online/orderqry/V1',
            'biz_content' => array_merge([
                'mer_id' => $this->config['app_id'],
                'timestamp' => date('YmdHis'),
            ], $params),
            'isNeedEncrypt' => false,
            'extraParams' => []
        ];

        return $this->execute($request, uniqid());
    }

    /**
     * 退款
     *
     * @param array $params 退款参数
     * @return string
     */
    public function refund(array $params)
    {
        $request = [
            'method' => 'POST',
            'api' => '/api/cardbusiness/aggregatepay/b2c/online/merrefund/V1',
            'biz_content' => array_merge([
                'mer_id' => $this->config['app_id'],
                'timestamp' => date('YmdHis'),
                'currency' => 'CNY',
            ], $params),
            'isNeedEncrypt' => false,
            'extraParams' => []
        ];

        return $this->execute($request, uniqid());
    }

    /**
     * 订单撤销
     *
     * @param array $params 撤销参数
     * @return string
     */
    public function cancel(array $params)
    {
        $request = [
            'method' => 'POST',
            'api' => '/api/mybankpay/cpaycp/preservation/cancel/V2',
            'biz_content' => array_merge([
                'mer_id' => $this->config['app_id'],
                'timestamp' => date('YmdHis'),
            ], $params),
            'isNeedEncrypt' => false,
            'extraParams' => []
        ];

        return $this->execute($request, uniqid());
    }

    /**
     * 执行API请求
     *
     * @param array $request 请求参数
     * @param string $msgId 消息ID
     * @param string|null $appAuthToken 应用授权令牌
     * @return string
     */
    public function execute(array $request, string $msgId, ?string $appAuthToken = null)
    {
        // 处理API地址
        $gateway = $this->config['sandbox'] ? 
            str_replace('gw.', 'gw.test.', $this->config['gateway']) : 
            $this->config['gateway'];
            
        $request['serviceUrl'] = $gateway . $request['api'];
        
        return $this->client->execute($request, $msgId, $appAuthToken);
    }

    /**
     * 获取客户端实例
     *
     * @return DefaultIcbcClient
     */
    public function getClient(): DefaultIcbcClient
    {
        return $this->client;
    }
} 