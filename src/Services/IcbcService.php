<?php

namespace IcbcSdk\Services;

use Exception;
use IcbcSdk\lib\DefaultIcbcClient;
use IcbcSdk\lib\IcbcConstants;
use IcbcSdk\lib\IcbcSignature;
use IcbcSdk\lib\WebUtils;
use IcbcSdk\lib\IcbcEncrypt;

class IcbcService
{
    protected $config;
    protected $client;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->initClient();
    }

    /**
     * 初始化 ICBC 客户端
     */
    protected function initClient()
    {
        $this->client = new DefaultIcbcClient(
            $this->config['app_id'],
            $this->config['private_key'],
            $this->config['sign_type'],
            $this->config['charset'],
            $this->config['format'],
            $this->config['icbc_public_key'],
            $this->config['encrypt_key'],
            $this->config['encrypt_type'],
            $this->config['ca'],
            $this->config['password']
        );
    }

    /**
     * 执行请求
     *
     * @param array $request
     * @param string $msgId
     * @param string|null $appAuthToken
     * @return mixed
     * @throws Exception
     */
    public function execute(array $request, string $msgId, string $appAuthToken = null)
    {
        // 设置 API 地址
        $baseUrl = $this->config['sandbox'] ? $this->config['api_url']['sandbox'] : $this->config['api_url']['production'];
        $request['serviceUrl'] = $baseUrl . ($request['serviceUrl'] ?? '');

        return $this->client->execute($request, $msgId, $appAuthToken);
    }

    /**
     * 支付接口
     *
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    public function pay(array $params)
    {
        $request = [
            'method' => 'POST',
            'serviceUrl' => '/api/cardbusiness/aggregatepay/b2c/online/consumepurchase/V1',
            'biz_content' => $params,
            'isNeedEncrypt' => false
        ];

        return $this->execute($request, uniqid());
    }

    /**
     * 订单查询
     *
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    public function query(array $params)
    {
        $request = [
            'method' => 'POST',
            'serviceUrl' => '/api/cardbusiness/aggregatepay/b2c/online/orderqry/V1',
            'biz_content' => $params,
            'isNeedEncrypt' => false
        ];

        return $this->execute($request, uniqid());
    }

    /**
     * 退款
     *
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    public function refund(array $params)
    {
        $request = [
            'method' => 'POST',
            'serviceUrl' => '/api/cardbusiness/aggregatepay/b2c/online/merrefund/V1',
            'biz_content' => $params,
            'isNeedEncrypt' => false
        ];

        return $this->execute($request, uniqid());
    }

    /**
     * 订单撤销
     *
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    public function cancel(array $params)
    {
        $request = [
            'method' => 'POST',
            'serviceUrl' => '/api/mybankpay/cpaycp/preservation/cancel/V2',
            'biz_content' => $params,
            'isNeedEncrypt' => false
        ];

        return $this->execute($request, uniqid());
    }
} 