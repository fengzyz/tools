<?php
/**
 * Created by PhpStorm.
 * User: fengzyz
 * Date: 2019/4/17
 * Time: 18:28
 * 公共上传cos
 */

namespace Fengzyz\Upload;

use Illuminate\Support\Facades\Log;
use Qcloud\Cos\Client;

class Cos
{
    private static $instance;
    private $config;

    public $appid;
    public $cosClient;

    const BUCKET_PREFIX = 'bucket-';

    private function __construct()
    {
        $this->appid = env('COS_APPID');

        $this->initClient();
    }

    public function initClient()
    {
        //初始化
        $this->config = [
            'region' => env("COS_REGION"),
            'schema' => env("COS_SCHEMA"),
            "credentials" => [
                "secretId" => env("COS_SECRETID"),
                "secretKey" => env("COS_SECRETKEY")
            ]
        ];
        $this->cosClient = new Client($this->config);
    }

    public function getBucket($appid = '')
    {
        $appid = empty($appid) ? $this->appid : $appid;
        return self::BUCKET_PREFIX . $appid;
    }

    //创建存储桶
    public function createBucket($appid = '')
    {
        $appid = empty($appid) ? env('COS_APPID') : $appid;
        $bucket = 'bucket-' . $appid;
        $result = $this->cosClient->createBucket(array('Bucket' => $bucket));
        Log::info('图片上传-createBucket', ['result' => $result]);
        return $result;
    }

    //存储桶列表
    public function getBuckets()
    {
        $result = $this->cosClient->listBuckets();
        Log::info('图片上传-getBuckets', ['result' => $result]);
        return (isset($result["Buckets"]) && !empty($result['Buckets'])) ? $result['Buckets'] : [];
    }

    //使用 putObject 接口上传文件（最大5G）。
    public function putObject($srcPath, $fileName)
    {

        $bucket = $this->getBucket();
        $result = $this->cosClient->putObject(array(
            'Bucket' => $bucket,
            'Key' => $fileName,
            'Body' => fopen($srcPath, 'rb')));


        $url = (!empty($result) && isset($result['ObjectURL'])) ? $result['ObjectURL'] : '';
        Log::info('图片上传-putObj', ["srcPath" => $srcPath, 'fileName' => $fileName, 'result' => $result]);
        return $url;

    }

    //使用 Upload 接口分块上传文件。
    public function upload($srcPath, $fileName)
    {
        $result = $this->cosClient->Upload(
            $bucket = $this->getBucket(),
            $key = $fileName,
            $body = fopen($srcPath, 'rb'));

        $url = (!empty($result) && isset($result['Location'])) ? $result['Location'] : '';
        Log::info('图片上传-upload', ["srcPath" => $srcPath, 'fileName' => $fileName, 'result' => (array)$result]);
        return $url;
    }

    /**
     * Desc: 删除对象
     * Author: fengzyz
     * @parma $key 除了域名之外的
     * https://bucket-1259053731.cos.ap-guangzhou.myqcloud.com/Fengzyz/api/images/d.png
     * /Fengzyz/api/images/d.png
     */
    public function delete($key)
    {
        // 删除 COS 对象
        $result = $this->cosClient->deleteObject(array(
            //bucket 的命名规则为{name}-{appid} ，此处填写的存储桶名称必须为此格式
            'Bucket' => $this->getBucket(),
            'Key' => $key));
        return $result;
    }

    private function __clone()
    {

    }

    //单例
    public static function getInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function download()
    {
        //todo
    }
}