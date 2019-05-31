<?php
/**
 * Created by PhpStorm.
 * User: fengzyz
 * Date: 2019/3/15
 * Time: 13:39
 */

namespace Fengzyz\Exception;

use \Exception;
use Fengzyz\Response\JsonResult;

class ExceptionResult extends Exception
{
    /**
     * @var $string
     */
    protected $message;
    /**
     * @var $string
     */
    protected $code;
    /**
     * @var array
     */
    protected static $messageTemplate = [];

    public function __construct($message, $code = 0)
    {
        parent::__construct($message, $code);
    }

    // 自定义字符串输出的样式 */
    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

    /**
     * set default template config
     * @param array $template
     * @return $this
     */
    public static function setMsgTemplate(array $template = [])
    {
        static::$messageTemplate = $template;
    }

    /**
     * get error msg template
     * @return array
     */
    public static function getMsgTemplate()
    {
        return static::$messageTemplate;
    }


    /**
     * error information to array data
     * @return array
     */
    public function toArray()
    {
        return [
            'code' => $this->getCode(),
            'message' => $this->getMessage(),
        ];
    }

    /**
     * throwException
     * @param $msgCode
     * @param array $args
     */
    public static function throwException($msgCode, array $args = [])
    {
        $class = __CLASS__;
        $message = static::getMsg($msgCode);
        if (!empty($args)) {
            foreach ($args as $key => $value) {
                $message = str_replace('%' . $key, $value, $message);
            }
        }
        throw new $class($message, $msgCode);
    }

    /**
     * get message
     * @param $msgCode
     * @return mixed
     */
    public static function getMsg($msgCode)
    {
        static::getMsgTemplate();
        $message = static::$messageTemplate;
        if (empty($message)) {
            throw new \RuntimeException("Message Template Is Not Set");
        }

        if (!isset($message[$msgCode])) {
            throw new \RuntimeException("MsgCode Not Found Message Value");
        }

        return $message[$msgCode];
    }

    /**
     * catchException
     * @param \Exception $e
     * @param JsonResult $jsonResult
     * @return $this|array
     */
    public static function catchException(\Exception $e, &$jsonResult = null)
    {
        $code = $e->getCode();
        $msg = $e->getMessage();
        if (!empty($jsonResult) && $jsonResult instanceof JsonResult) {
            return $jsonResult->setMsg($msg)->setCode($code);
        }
        return compact('code', 'msg');
    }


}