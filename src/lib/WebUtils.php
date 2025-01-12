<?php

namespace Hiworld\LaravelIcbc\lib;

class WebUtils
{
    public static function doGet($url, $params, $charset)
    {
        $queryString = self::buildQueryString($params);
        $fullUrl = $queryString ? $url . "?" . $queryString : $url;
        return self::curlExec($fullUrl, "GET", null, $charset);
    }

    public static function doPost($url, $params, $charset)
    {
        $queryString = self::buildQueryString($params);
        return self::curlExec($url, "POST", $queryString, $charset);
    }

    public static function buildQueryString($params)
    {
        if (empty($params)) {
            return "";
        }
        $query = "";
        foreach ($params as $key => $value) {
            if (!empty($query)) {
                $query .= "&";
            }
            $query .= "$key=" . urlencode($value);
        }
        return $query;
    }

    public static function buildOrderedSignStr($path, $params)
    {
        if (empty($params)) {
            return $path;
        }
        ksort($params);
        $query = "";
        foreach ($params as $key => $value) {
            if (!empty($query)) {
                $query .= "&";
            }
            $query .= "$key=" . $value;
        }
        return $path . "?" . $query;
    }

    private static function curlExec($url, $method, $postFields, $charset)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        if ($method == "POST") {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        }

        if ($charset) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: application/x-www-form-urlencoded;charset=" . $charset));
        }

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            throw new \Exception("CURL Error: " . $err);
        }
        return $response;
    }
} 