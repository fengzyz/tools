<?php
/**
 * Created by PhpStorm.
 * User: fengzyz
 * Date: 2019/3/14
 * Time: 9:18
 */

namespace Fengzyz\Micro;

use Illuminate\Support\Facades\Session;

class BaseMicro
{
    /**
     * @var
     */
    public $rpc;

    /**
     * @var
     */
    public $apiRequestHelper;

    /**
     * @var string
     */
    protected $getMethod = "GET";

    /**
     * @var string
     */
    protected $postMethod = "POST";

    /**
     * @var string
     */
    protected $putMethod = "PUT";


    /**
     * @var string
     */
    protected $deleteMethod = 'DELETE';

    /**
     * 公共参数
     * @var array
     */
    public $publicParams = [];

    public function __construct()
    {
        $this->apiRequestHelper = app("apiRequestHelper");
    }

    public function buildParams($params)
    {
        if (empty($params)) $params = [];

        return array_merge($this->publicParams, $params);
    }

    /**
     * 实例
     * @return static
     */
    protected static $instance;

    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     *  get方法请求
     * @param $uri
     * @param $params
     * @param bool $openErr
     * @param array $options
     * @param bool $parseResult
     * @return mixed
     */
    public function getRequest($uri, $params = [], $openErr = true, $options = [], $parseResult = true)
    {
        return app("apiRequestHelper")->request($uri, $this->buildParams($params), $this->getMethod, $this->rpc, $openErr, $options, $parseResult);
    }


    /**
     *  post 方法请求
     * @param $uri
     * @param $params
     * @param bool $openErr
     * @param array $options
     * @param bool $parseResult
     * @return mixed
     */
    public function postRequest($uri, $params = [], $openErr = true, $options = [], $parseResult = true)
    {
        return app("apiRequestHelper")->request($uri, $this->buildParams($params), $this->postMethod, $this->rpc, $openErr, $options, $parseResult);
    }

    /**
     *  PUT 方法请求
     * @param $uri
     * @param $params
     * @param bool $openErr
     * @param array $options
     * @param bool $parseResult
     * @return mixed
     */
    public function putRequest($uri, $params = [], $openErr = true, $options = [], $parseResult = true)
    {
        return app("apiRequestHelper")->request($uri, $this->buildParams($params), $this->putMethod, $this->rpc, $openErr, $options, $parseResult);
    }

    /**
     *  PUT 方法请求
     * @param $uri
     * @param $params
     * @param bool $openErr
     * @param array $options
     * @param bool $parseResult
     * @return mixed
     */
    public function deleteRequest($uri, $params = [], $openErr = true, $options = [], $parseResult = true)
    {
        return app("apiRequestHelper")->request($uri, $this->buildParams($params), $this->deleteMethod, $this->rpc, $openErr, $options, $parseResult);
    }

}