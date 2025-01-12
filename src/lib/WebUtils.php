<?php

namespace IcbcSdk\lib;

use Exception;

class WebUtils
{
    /**
     * 执行HTTP GET请求
     *
     * @param string $url
     * @param array $params
     * @param string $charset
     * @return string
     * @throws Exception
     */
    public static function doGet(string $url, array $params, string $charset): string
    {
        $queryString = self::buildQueryString($params);
        $fullUrl = $url . (strpos($url, '?') === false ? '?' : '&') . $queryString;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fullUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception("HTTP GET Request failed: " . $error);
        }
        
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("HTTP GET Request failed with code: " . $httpCode);
        }

        return $response;
    }

    /**
     * 执行HTTP POST请求
     *
     * @param string $url
     * @param array $params
     * @param string $charset
     * @return string
     * @throws Exception
     */
    public static function doPost(string $url, array $params, string $charset): string
    {
        $queryString = self::buildQueryString($params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $queryString);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception("HTTP POST Request failed: " . $error);
        }
        
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("HTTP POST Request failed with code: " . $httpCode);
        }

        return $response;
    }

    /**
     * 构建有序的签名字符串
     *
     * @param string $path
     * @param array $params
     * @return string
     */
    public static function buildOrderedSignStr(string $path, array $params): string
    {
        ksort($params);
        $stringToBeSigned = '';
        
        foreach ($params as $k => $v) {
            if (!empty($v) && !is_array($v)) {
                $stringToBeSigned .= "&{$k}={$v}";
            }
        }
        
        $stringToBeSigned = substr($stringToBeSigned, 1);
        return $path . '?' . $stringToBeSigned;
    }

    /**
     * 构建查询字符串
     *
     * @param array $params
     * @return string
     */
    protected static function buildQueryString(array $params): string
    {
        ksort($params);
        $pairs = [];
        
        foreach ($params as $key => $value) {
            if (!empty($value) && !is_array($value)) {
                $pairs[] = $key . '=' . urlencode($value);
            }
        }
        
        return implode('&', $pairs);
    }

    public static function buildGetUrl($url, $params)
    {
        if (empty($params)) {
            return $url;
        }
        $queryString = self::buildQueryString($params);
        return $url . (strpos($url, '?') === false ? '?' : '&') . $queryString;
    }

    public static function buildForm($url, $params)
    {
        $fields = self::buildHiddenFields($params);
        return sprintf(
            '<form name="auto_submit_form" method="post" action="%s">%s<input type="submit" value="立刻提交" style="display:none"></form><script>document.forms[0].submit();</script>',
            $url,
            $fields
        );
    }

    protected static function buildHiddenFields($params)
    {
        if (empty($params)) {
            return '';
        }

        $fields = [];
        foreach ($params as $key => $value) {
            if ($key === null || $value === null) {
                continue;
            }
            $fields[] = sprintf(
                '<input type="hidden" name="%s" value="%s">',
                $key,
                htmlspecialchars($value, ENT_QUOTES)
            );
        }
        return implode("\n", $fields);
    }
}
?>