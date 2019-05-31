<?php
/**
 * Created by PhpStorm.
 * User: fengzyz
 * Date: 2019/4/28
 * Time: 10:46
 */

namespace Fengzyz\Encrypt;


use Illuminate\Support\Facades\Log;

class RsaHelper
{
    private static $private_key;    //私钥
    private static $public_key;  //公钥
    /**
     * RSA最大加密明文大小
     */
    const MAX_ENCRYPT_BLOCK = 117;

    /**
     * RSA最大解密密文大小
     */
    const MAX_DECRYPT_BLOCK = 128;

    public static $instance;


    private function __construct()
    {
        $private = realpath('../') . '/download/key/private.key';
        $public = realpath('../') . '/download/key/public.key';

        //私钥
        $fp = fopen($private, "r");
        self::$private_key = fread($fp, 8192);
        fclose($fp);

        // 公钥
        $fp = fopen($public, "r");
        self::$public_key = fread($fp, 8192);
        fclose($fp);
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * crul post请求
     * @param $data
     * @param $url
     * @param int $type 默认type=0 登录注册找回密码，type=1时头部必须带登录令牌
     * @param string $loginToken 定时任务传的token
     * @return mixed
     */
    function curlPost($url, $params, $rpc = 'guangzhou', $header = [])
    {
        $url = config('server.rpc.' . $rpc) . ltrim($url, '/');
        $ch = curl_init();
        $self_header = array(
            "content-type: application/x-www-form-urlencoded; charset=UTF-8",
        );
        $header = !empty($self_header) ? array_merge($self_header, $header) : $self_header;
        $data = urlencode($this->rsa_encrypt($params));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        if (strpos(strtolower($url), 'https') == 0) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        Log::info('Api Request Start', compact('url', 'params', 'method'));
        $return = curl_exec($ch);
        $return = $this->rsa_decrypt($return);
        Log::info('Api Request End', [$return]);

//        $res =  !empty($return) &&  !empty(\GuzzleHttp\json_decode($return,true)) ? \GuzzleHttp\json_decode($return,true) : [];
//        return (!empty($res) && $res['retCode'] == '0000')?  $res['data']:  ( (isset($res['retMsg'])) ? $res['retMsg'] : "请求网络失败");
        return !empty($return) && !empty(\GuzzleHttp\json_decode($return, true)) ? \GuzzleHttp\json_decode($return, true) : "接口请求失败:";
    }


    /*RSA加密*/
    public function rsa_encrypt($array = array(), $urlencode = 0)
    {
        $jsonStr = '';
        if (is_array($array)) {
            $jsonStr = json_encode($array);
        } else {
            $jsonStr = json_encode(array('key' => $array));
        }

        if ($urlencode == 1) {
            $jsonStr = urldecode($jsonStr);
        }

        return $this->encrypt($jsonStr);
    }

    /*
    * 分段加密
    */

    public function encrypt($originalData)
    {

        $res = openssl_get_publickey(self::$public_key);
        $crypto = '';
        foreach (str_split($originalData, self::MAX_ENCRYPT_BLOCK) as $chunk) {
            openssl_public_encrypt($chunk, $encryptData, $res);
            $crypto .= $encryptData;
        }

        return base64_encode($crypto);
    }


    /*RSA解密*/
    public function rsa_decrypt($string = '')
    {
        $jsonStr = '';
        $jsonStr = $this->decrypt(urldecode($string));
        return $jsonStr;
    }

    /*
     * 分段解密
     */

    public function decrypt($data)
    {
        $data = base64_decode($data);

        $res = openssl_get_publickey(self::$public_key);
        $crypto = "";

        foreach (str_split($data, self::MAX_DECRYPT_BLOCK) as $chunk) {
            openssl_public_decrypt($chunk, $decryptData, $res);
            $crypto .= $decryptData;
        }
        return $crypto;
    }

}