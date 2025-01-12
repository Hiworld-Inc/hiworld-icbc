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
        try {
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
        } catch (\Exception $e) {
            throw new \Exception('Failed to initialize ICBC client: ' . $e->getMessage());
        }
    }

    /**
     * 格式化私钥
     *
     * @param string $privateKey
     * @return string
     * @throws \Exception
     */
    protected function formatPrivateKey($privateKey)
    {
        if (empty($privateKey)) {
            throw new \Exception('Private key cannot be empty');
        }

        // 如果是文件路径，则读取文件内容
        if (file_exists($privateKey)) {
            $privateKey = file_get_contents($privateKey);
            if ($privateKey === false) {
                throw new \Exception('Failed to read private key file');
            }
        }

        // 清理密钥内容
        $privateKey = $this->cleanKeyContent($privateKey);

        // 尝试直接验证原始密钥
        $res = @openssl_pkey_get_private($privateKey);
        if ($res) {
            openssl_pkey_free($res);
            return $privateKey;
        }

        // 尝试不同的密钥格式
        $formats = [
            ["-----BEGIN PRIVATE KEY-----\n", "\n-----END PRIVATE KEY-----"],
            ["-----BEGIN RSA PRIVATE KEY-----\n", "\n-----END RSA PRIVATE KEY-----"],
            ["-----BEGIN ENCRYPTED PRIVATE KEY-----\n", "\n-----END ENCRYPTED PRIVATE KEY-----"]
        ];

        foreach ($formats as $format) {
            $pem = $format[0] . chunk_split($privateKey, 64, "\n") . $format[1];
            $res = @openssl_pkey_get_private($pem);
            if ($res) {
                openssl_pkey_free($res);
                return $pem;
            }
        }

        // 如果所有尝试都失败，记录错误并抛出异常
        $errors = [];
        while ($error = openssl_error_string()) {
            $errors[] = $error;
        }

        // 尝试解码 base64 看看是否有效
        $decoded = base64_decode($privateKey, true);
        if ($decoded === false) {
            throw new \Exception('Private key is not a valid base64 string');
        }

        throw new \Exception('Invalid private key format. OpenSSL errors: ' . implode('; ', $errors));
    }

    /**
     * 格式化公钥
     *
     * @param string $publicKey
     * @return string
     * @throws \Exception
     */
    protected function formatPublicKey($publicKey)
    {
        if (empty($publicKey)) {
            throw new \Exception('Public key cannot be empty');
        }

        // 如果是文件路径，则读取文件内容
        if (file_exists($publicKey)) {
            $publicKey = file_get_contents($publicKey);
            if ($publicKey === false) {
                throw new \Exception('Failed to read public key file');
            }
        }

        // 清理密钥内容
        $publicKey = $this->cleanKeyContent($publicKey);

        // 尝试直接验证原始密钥
        $res = @openssl_pkey_get_public($publicKey);
        if ($res) {
            openssl_pkey_free($res);
            return $publicKey;
        }

        // 格式化为 PEM 格式
        $pem = "-----BEGIN PUBLIC KEY-----\n" .
            chunk_split($publicKey, 64, "\n") .
            "-----END PUBLIC KEY-----";

        $res = @openssl_pkey_get_public($pem);
        if (!$res) {
            // 获取 OpenSSL 错误信息
            $errors = [];
            while ($error = openssl_error_string()) {
                $errors[] = $error;
            }
            throw new \Exception('Invalid public key format. OpenSSL errors: ' . implode('; ', $errors));
        }
        openssl_pkey_free($res);

        return $pem;
    }

    /**
     * 清理密钥内容
     *
     * @param string $key
     * @return string
     */
    protected function cleanKeyContent($key)
    {
        // 移除 UTF-8 BOM
        $key = str_replace("\xEF\xBB\xBF", '', $key);
        
        // 统一换行符
        $key = str_replace(["\r\n", "\r"], "\n", $key);
        
        // 如果不是 PEM 格式，移除所有空白字符
        if (strpos($key, '-----BEGIN') === false) {
            $key = preg_replace('/\s+/', '', $key);
            // 移除可能存在的 PEM 头尾
            $key = preg_replace('/-+[^-]+-+/', '', $key);
        }
        
        return $key;
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