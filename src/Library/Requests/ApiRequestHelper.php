<?php
/**
 * Created by PhpStorm.
 * User: fengzyz
 * Date: 2019/3/27
 * Time: 17:32
 */

namespace Fengzyz\Requests;

use Fengzyz\Exception\ExceptionResult;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ApiRequestHelper
{
    /**
     * @var string
     */
    protected $deviceType = 'Unknown';

    protected $requestId;

    const RESP_CODE_SUCCESS = 200;
    const RESP_CODE_FAIL = 500;
    const RESP_CODE_MISS_UPLOAD_FILE = 404009;

    public function __construct()
    {
        $this->requestId = app('requestId');
    }

    /**
     * 获取rpc域名
     * @param $uri
     * @param string $rpc
     * @return string
     */
    protected function getRpcUrl($uri, $rpc = 'base')
    {
        if (!empty(config('server.rpc.' . $rpc))) {
            return config('server.rpc.' . $rpc) . ltrim($uri, '/');
        }
        throw new \RuntimeException("Rpc:{$rpc} config not exist");
    }

    /**
     * @return string
     */
    protected function getClientName()
    {
        $serverName = getenv("SERVERS_NAME");

        return isset($serverName) ? $serverName : $this->deviceType;
    }


    /**
     * @return string
     */
    protected function getDeviceType()
    {
        return isset($_SERVER['HTTP_Fengzyz_DEVICE_TYPE']) ? $_SERVER['HTTP_Fengzyz_DEVICE_TYPE'] : $this->deviceType;
    }

    /**
     * @param $deviceType
     */
    public function setDeviceType($deviceType)
    {
        $this->deviceType = $deviceType;
    }

    /**
     * @return null
     */
    protected function getClientIp()
    {
        return isset($_SERVER['HTT_CLIENT_IP']) ? $_SERVER['HTT_CLIENT_IP'] : (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null);

    }

    /**
     * @return string
     */
    protected function getForwardFor()
    {
        return isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : '';
    }

    /**
     * @param $url
     * @param array $params
     * @param string $method
     * @param string $rpc
     * @param bool $openErr 开启错误异常抛出
     * @param array $options
     * @param bool $parseResult 开启解析返回数据
     * @return mixed
     */
    public function request($url, $params = [], $method = 'GET', $rpc = 'cmm', $openErr = true, $options = [], $parseResult = true)
    {
        $url = $this->getRpcUrl($url, $rpc);
        $method = strtoupper($method);

        $httpClient = new Client();
        $option = [
            'headers' => [
                //统一requestId
                'Fengzyz-REQUEST-ID' => $this->requestId,
                'Fengzyz-REQUEST-CLIENT' => $this->getClientName(),
                'CLIENT-IP' => $this->getClientIp(),
                'X-FORWARDED-FOR' => $this->getForwardFor(),
                'Fengzyz-DEVICE-TYPE' => $this->getDeviceType(),
            ],
            'verify' => false,
        ];
        unset($params['_url']);

        if ($method === 'GET' && !empty($params)) {
            $url .= '?' . http_build_query($params);
        } else {
            /**
             * 采用json传输
             */
            $option['json'] = $params;
        }

        if (!empty($options)) {
            $option['headers'] = array_merge($option['headers'], $options);
        }

        Log::info('Api Request Start', compact('url', 'params', 'method'));
        $response = $httpClient->request($method, $url, $option);
        $resp = $response->getBody()->getContents();
        Log::info('Api Request End', [$resp]);
        $result = $resp;

        if ($parseResult) {
            $result = static::parseResponseDataFormat($resp);
            $success = static::isSuccess($result['code']);
            if (!$success) {
                Log::warning('调用远程接口错误信息', $result);
            }
            /*
             * 解析结果，抛出异常
             */
            if ($openErr) {
                $this->parseResponseDataResult($result);
            }
        }

        return $result;
    }

    /**
     * 请求是否成功
     * @param $code
     * @return bool
     */
    protected static function isSuccess($code)
    {
        return $code == self::RESP_CODE_SUCCESS ? TRUE : FALSE;
    }

    /**
     * 解析http返回数据
     * @param $response
     * @return mixed
     * @throws ExceptionResult
     */
    public static function parseResponseDataFormat($response)
    {
        $data = @json_decode($response, TRUE);
        if (json_last_error()) {
            throw new ExceptionResult('网络请求失败', self::RESP_CODE_FAIL);
        }

        return $data;
    }

    /**
     * @param $data
     * @throws ExceptionResult
     */
    public static function parseResponseDataResult(&$data)
    {
        if (!static::isSuccess($data['code'])) {
            throw new ExceptionResult($data['msg'], $data['code']);
        }
        $data = $data['data'];
    }

    /**
     * 文件上传
     * @param $fileInfo
     * $fileInfo = ['name'=>'qrcode.png','path'=>'/tmp/qrcode.png'];
     * @param int $watermark [水印类型，0：不开启，1：类型一水印]
     * @param int $posX [水印位置]
     * @param int $posY [水印位置]
     * @return mixed|string
     * @throws ExceptionResult
     */
    public function upload($fileInfo, $watermark = 0, $posX = 0, $posY = 0)
    {
        if (!isset($fileInfo['path']) && empty($fileInfo['data'])) {
            throw new ExceptionResult('缺少上传文件', self::RESP_CODE_MISS_UPLOAD_FILE);
        }

        $fileName = $fileInfo['name'];
        $data = isset($fileInfo['path']) ? fopen($fileInfo['path'], 'r') : $fileInfo['data'];
        $body = [
            [
                'name' => 'files',
                'filename' => $fileName,
                'contents' => $data,

            ],
        ];

        $url = $this->getRpcUrl('files', 'base');

        $httpClient = new Client();
        $response = $httpClient->request('post', $url, [
            'multipart' => $body, 'headers' => [
                'Image-Mark' => $watermark,
                'Image-POS-X' => $posX,
                'Image-POS-Y' => $posY,
                'Fengzyz-REQUEST-ID' => $this->requestId,
                'Fengzyz-REQUEST-CLIENT' => $this->getClientName(),
                'CLIENT-IP' => $this->getClientIp(),
                'X-FORWARDED-FOR' => $this->getForwardFor(),
                'Fengzyz-DEVICE-TYPE' => $this->getDeviceType(),
            ],
        ]);

        $result = $response->getBody()->getContents();
        Log::info('文件上传结果', [$result]);

        $result = static::parseResponseDataFormat($result);
        $success = static::isSuccess($result['code']);
        if (!$success) {
            Log::warning('调用远程接口错误信息', $result);
        }
        $this->parseResponseDataResult($result);
        $result['origUrl'] = $result['url'];
        $result['url'] = getenv('HOST_IMAGE') . $result['url'];

        return $result;
    }

    /**
     * 上次视频，对10M以上视频支持不友好
     * @param $fileInfo
     * @return mixed|string
     */
    public function uploadVideo($fileInfo)
    {
        if (!isset($fileInfo['path']) && empty($fileInfo['data'])) {
            throw new ExceptionResult('缺少上传视频', self::RESP_CODE_MISS_UPLOAD_FILE);
        }

        $fileName = $fileInfo['name'];
        $data = isset($fileInfo['path']) ? fopen($fileInfo['path'], 'r') : $fileInfo['data'];
        $body = [
            [
                'name' => 'files',
                'filename' => $fileName,
                'contents' => $data,

            ],
        ];

        $url = $this->getRpcUrl('files', 'base');

        $httpClient = new Client();
        $response = $httpClient->request('post', $url, [
            'multipart' => $body, 'headers' => [
                'Fengzyz-REQUEST-ID' => $this->requestId,
                'Fengzyz-REQUEST-CLIENT' => $this->getClientName(),
                'CLIENT-IP' => $this->getClientIp(),
                'X-FORWARDED-FOR' => $this->getForwardFor(),
                'Fengzyz-DEVICE-TYPE' => $this->getDeviceType(),
            ],
        ]);

        $result = $response->getBody()->getContents();
        Log::info('视频上传结果', [$result]);

        $result = static::parseResponseDataFormat($result);
        $success = static::isSuccess($result['code']);
        if (!$success) {
            Log::warning('调用远程接口错误信息', $result);
        }
        $this->parseResponseDataResult($result);
        $result['origUrl'] = $result['url'];
        $result['url'] = getenv('HOST_IMAGE') . $result['url'];

        return $result;
    }
}