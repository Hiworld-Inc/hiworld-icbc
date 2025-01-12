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

        // 验证必需的配置项
        if (empty($config['app_id'])) {
            throw new \Exception('ICBC app_id is required');
        }
        if (empty($config['private_key'])) {
            throw new \Exception('ICBC private_key is required');
        }
        if (empty($config['icbc_public_key'])) {
            throw new \Exception('ICBC public_key is required');
        }
        if (empty($config['sign_type'])) {
            throw new \Exception('ICBC sign_type is required');
        }

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

        // 如果已经是 PEM 格式，直接返回
        if (strpos($privateKey, '-----BEGIN') !== false) {
            return $privateKey;
        }

        // 移除所有非 base64 字符
        $privateKey = preg_replace('/[^A-Za-z0-9+\/=]/', '', $privateKey);

        // 验证 base64 字符串
        if (!$this->isValidBase64($privateKey)) {
            throw new \Exception('Invalid base64 format for private key');
        }

        // 添加 PEM 格式头尾
        return "-----BEGIN RSA PRIVATE KEY-----\n" .
            chunk_split($privateKey, 64, "\n") .
            "-----END RSA PRIVATE KEY-----";
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

        // 如果已经是 PEM 格式，需要确保是公钥格式
        if (strpos($publicKey, '-----BEGIN') !== false) {
            // 如果是私钥格式，转换为公钥格式
            if (strpos($publicKey, 'PRIVATE KEY') !== false) {
                $publicKey = preg_replace('/PRIVATE KEY/', 'PUBLIC KEY', $publicKey);
            }
            return $publicKey;
        }

        // 移除所有非 base64 字符
        $publicKey = preg_replace('/[^A-Za-z0-9+\/=]/', '', $publicKey);

        // 验证 base64 字符串
        if (!$this->isValidBase64($publicKey)) {
            throw new \Exception('Invalid base64 format for public key');
        }

        // 添加 PEM 格式头尾
        return "-----BEGIN PUBLIC KEY-----\n" .
            chunk_split($publicKey, 64, "\n") .
            "-----END PUBLIC KEY-----";
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
     * 验证是否为有效的 base64 字符串
     *
     * @param string $str
     * @return bool
     */
    protected function isValidBase64($str)
    {
        // 检查长度是否为4的倍数
        if (strlen($str) % 4 !== 0) {
            return false;
        }

        // 检查是否包含有效的 base64 字符
        if (!preg_match('/^[A-Za-z0-9+\/]+={0,2}$/', $str)) {
            return false;
        }

        // 尝试解码
        $decoded = base64_decode($str, true);
        if ($decoded === false) {
            return false;
        }

        // 重新编码并比较
        return base64_encode($decoded) === $str;
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
        // 根据环境选择对应的网关地址
        $gateway = $this->config['sandbox'] ? $this->config['sandbox_gateway'] : $this->config['gateway'];
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