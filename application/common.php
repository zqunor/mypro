<?php

function curl_get($url, &$httpCode = 0)
{
    //1、初始化curl
    $curl = curl_init();

    //2、告诉curl,请求的地址
    curl_setopt($curl, CURLOPT_URL, $url);
    //3、将请求的数据返回，而不是直接输出
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);

    $fileContents = curl_exec($curl); // 执行操作
    curl_close($curl); // 关键CURL会话

    return $fileContents; // 返回数据
}

function getRandChar($length)
{
    $str    = null;
    $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
    $max    = strlen($strPol) - 1;

    for ($i = 0; $i < $length; $i++) {
        $str .= $strPol[rand(0, $max)];
    }

    return $str;
}