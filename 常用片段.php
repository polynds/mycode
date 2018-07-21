<?php
/**
 * Created by polynds.
 * Date: 2018/7/21
 * Time: 10:06
 */

//验签解密
$sign = base64_decode($data['sign']);
$pkeyid = openssl_get_publickey(file_get_contents('pem证书路径'));
$signResult = false;
if ($pkeyid) {
    $signResult = (bool)openssl_verify($encryptCode, $sign, $pkeyid); // 响应的验签
    openssl_free_key($pkeyid);
}
if(!empty($signResult)){}//验签结果判断

