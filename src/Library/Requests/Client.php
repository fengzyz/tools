<?php
/**
 * Created by PhpStorm.
 * User: fengzyz
 * Date: 2019/3/13
 * Time: 17:41
 */

namespace Fengzyz\Requests;


class Client
{
    protected $client;
    public static $instance;
    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client();
    }

    public static function getInstance()
    {
        if (!self::$instance) self::$instance = new self();
        return self::$instance;
    }

    /***
     * @param $url
     * @param array $params
     * @param array $header
     * @param bool $isError
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getRequest($url, $params = [], $header = [], $isError = false)
    {
        $array = [
            'headers' => $header,
            'query' => $params,
            'http_errors' => $isError   #支持错误输出
        ];
        return $this->response('get', $url, $array);
    }

    /**
     * @param $url
     * @param array $params
     * @param array $header
     * @param bool $isError
     */
    public function postRequest($url, $params = [], $header = [], $isError = false)
    {
        $array = [
            'json' => $params,
            'query' => [],
            'headers' => $header,
            'http_errors' => $isError
        ];
        return $this->response('post', $url, $array);
    }

    /**
     * @param $url
     * @param array $params
     * @param array $header
     * @param bool $isError
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function putRequest($url, $params = [], $header = [], $isError = false)
    {
        $array = [
            'json' => $params,
            'query' => [],
            'headers' => $header,
            'http_errors' => $isError
        ];
        return $this->response('put', $url, $array);
    }

    /**
     * @param $url
     * @param $header
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function deleteRequest($url, $header)
    {
        $array = [
            'json' => [],
            'query' => [],
            'headers' => $header,
            'http_errors' => false
        ];
        return $this->response('delete', $url, $array);
    }

    /**
     * @param $method
     * @param $url
     * @param $array
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function response($method, $url, $array)
    {
        $response = $this->client->request($method, $url, $array);
        if ($response->getStatusCode() == 200) {
            return $this->jsonResult($response);
        }
    }

    /**
     *  输出结果
     * @param $result
     * @return mixed
     */
    private function jsonResult($result)
    {
        return json_decode($result->getBody()->getContents(), true);
    }
}