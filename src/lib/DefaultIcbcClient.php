<?php

namespace IcbcSdk\lib;

use Exception;
use IcbcSdk\lib\IcbcEncrypt;
use IcbcSdk\lib\IcbcSignature;
use IcbcSdk\lib\WebUtils;

class DefaultIcbcClient
{
    public $appId;
    public $privateKey;
    public $signType;
    public $charset;
    public $format;
    public $icbcPulicKey;
    public $encryptKey;
    public $encryptType;
    public $ca;
    public $password;

    public function __construct(
        string $appId,
        string $privateKey,
        ?string $signType = null,
        ?string $charset = null,
        ?string $format = null,
        string $icbcPulicKey,
        string $encryptKey,
        string $encryptType,
        string $ca,
        string $password
    ) {
        $this->appId = $appId;
        $this->privateKey = $privateKey;
        $this->signType = $signType ?: IcbcConstants::$SIGN_TYPE_RSA;
        $this->charset = $charset ?: IcbcConstants::$CHARSET_UTF8;
        $this->format = $format ?: IcbcConstants::$FORMAT_JSON;
        $this->icbcPulicKey = $icbcPulicKey;
        $this->encryptKey = $encryptKey;
        $this->encryptType = $encryptType;
        $this->password = $password;
        
        // 去除签名数据及证书数据中的空格
        if (!empty($ca)) {
            $ca = preg_replace("/\s*|\t/", "", $ca);
        }
        $this->ca = $ca;
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
    public function execute(array $request, string $msgId, ?string $appAuthToken = null)
    {
        $params = $this->prepareParams($request, $msgId, $appAuthToken);

        if ($request['method'] === 'GET') {
            $respStr = WebUtils::doGet($request['serviceUrl'], $params, $this->charset);
        } elseif ($request['method'] === 'POST') {
            $respStr = WebUtils::doPost($request['serviceUrl'], $params, $this->charset);
        } else {
            throw new Exception('Only support GET or POST http method!');
        }

        $response = json_decode($respStr, true);
        $respBizContent = $response[IcbcConstants::$RESPONSE_BIZ_CONTENT] ?? null;
        $sign = $response[IcbcConstants::$SIGN] ?? null;

        if (!$respBizContent || !$sign) {
            throw new Exception('Invalid response format!');
        }

        // 验证签名
        $passed = IcbcSignature::verify(
            json_encode($respBizContent, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            IcbcConstants::$SIGN_TYPE_RSA,
            $this->icbcPulicKey,
            $this->charset,
            $sign
        );

        if (!$passed) {
            throw new Exception('ICBC sign verify failed!');
        }

        // 如果需要解密
        if ($request['isNeedEncrypt'] ?? false) {
            $respBizContent = IcbcEncrypt::decryptContent(
                substr(json_encode($respBizContent), 1, -1),
                $this->encryptType,
                $this->encryptKey,
                $this->charset
            );
        }

        return $respBizContent;
    }

    /**
     * 准备请求参数
     *
     * @param array $request
     * @param string $msgId
     * @param string|null $appAuthToken
     * @return array
     */
    protected function prepareParams(array $request, string $msgId, ?string $appAuthToken = null): array
    {
        $path = parse_url($request['serviceUrl'], PHP_URL_PATH);
        $params = $request['extraParams'] ?? [];

        $params[IcbcConstants::$APP_ID] = $this->appId;
        $params[IcbcConstants::$SIGN_TYPE] = $this->signType;
        $params[IcbcConstants::$CHARSET] = $this->charset;
        $params[IcbcConstants::$FORMAT] = $this->format;
        $params[IcbcConstants::$CA] = $this->ca;
        $params[IcbcConstants::$APP_AUTH_TOKEN] = $appAuthToken;
        $params[IcbcConstants::$MSG_ID] = $msgId;

        date_default_timezone_set(IcbcConstants::$DATE_TIMEZONE);
        $params[IcbcConstants::$TIMESTAMP] = date(IcbcConstants::$DATE_TIME_FORMAT);

        $bizContent = json_encode($request['biz_content'] ?? null);
        if ($request['isNeedEncrypt'] ?? false) {
            $params[IcbcConstants::$ENCRYPT_TYPE] = $this->encryptType;
            $params[IcbcConstants::$BIZ_CONTENT_KEY] = IcbcEncrypt::encryptContent(
                $bizContent,
                $this->encryptType,
                $this->encryptKey,
                $this->charset
            );
        } else {
            $params[IcbcConstants::$BIZ_CONTENT_KEY] = $bizContent;
        }

        $strToSign = WebUtils::buildOrderedSignStr($path, $params);
        $params[IcbcConstants::$SIGN] = IcbcSignature::sign(
            $strToSign,
            $this->signType,
            $this->privateKey,
            $this->charset,
            $this->password
        );

        return $params;
    }
} 