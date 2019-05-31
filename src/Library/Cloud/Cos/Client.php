<?php
/**
 * Created by PhpStorm.
 * User: fengzyz
 * Date: 2019/4/17
 * Time: 10:58
 */

namespace Fengzyz\Cloud\Cos;


use Fengzyz\Cloud\CloudConfig;

class Client
{

    /**
     * 云 API 密钥 SecretId;
     */
    protected $secretId;
    /**
     * 云 API 密钥 SecretKey;
     */
    protected $secretKey;

    /**
     * 设置一个默认的存储桶地域
     */
    protected $region;

    /**
     *  存储桶名称 格式：BucketName-APPID
     */
    protected $bucket;

    /**
     *  连接 Cos 服务
     * Client constructor.
     */
    public function __construct()
    {
        $this->getConfig();
    }

    /**
     *  获取配置文件
     */
    public function getConfig()
    {
        if (function_exists('config')) {

        } else {

        }
    }

    public function conn()
    {
        $cosClient = new \Qcloud\Cos\Client();
    }
}