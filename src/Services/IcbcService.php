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
            $config['private_key'],
            $config['sign_type'],
            $config['charset'],
            $config['format'],
            $config['icbc_public_key'],
            $config['encrypt_key'],
            $config['encrypt_type'],
            $config['ca'],
            $config['password']
        );
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