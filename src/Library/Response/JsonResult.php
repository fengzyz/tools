<?php
/**
 * Created by PhpStorm.
 * User: fengzyz
 * Date: 2019/3/13
 * Time: 9:10
 */
namespace Fengzyz\Response;

class JsonResult
{
    /**
     * @var int $code
     */
    private $code;
    /**
     * @var mixed $data
     */
    private $data;
    /**
     * @var string $msg
     */
    private $msg;

    /**
     * set code value
     * @param $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code == null ? 200 : $code;
        return $this;
    }

    /**
     * get code value
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * set message value
     * @param $msg
     * @return $this
     */
    public function setMsg($msg)
    {
        $this->msg = $msg;
        return $this;
    }

    /**
     * get message value
     * @return mixed
     */
    public function getMsg()
    {
        return $this->msg;
    }

    /**
     * set data value
     * @param $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * get data value
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * set json result data
     * @param int $code
     * @param null $msg
     * @param null $data
     * @return $this
     */
    public function setResult($code = null, $msg = null, $data = null)
    {
        if($code instanceof JsonResult) {
            $code = $code->getCode();
            $msg = $code->getMsg();
            $data = $code->getData();
        }

        $this->setCode($code);
        $this->setMsg($msg);
        $this->setData($data);

        return $this;
    }

    /**
     * 设置成功
     * @param null $data
     * @return $this
     */
    public function setSuccess($msg=null,$data = null)
    {
        $this->setCode(200);
        $msg = empty($msg) ? 'success' : $msg;
        $this->setMsg($msg);
        $this->setData($data);
        return $this;
    }

    /**
     * 设置系统错误
     * @param null $data
     * @return $this
     */
    public function setError($msg=null,$data = null)
    {
        $this->setCode(500);
        $msg = empty($msg) ? 'system err' : $msg;
        $this->setMsg($msg);
        $this->setData($data);
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->toArray());
    }

    /**
     * to array
     * @return array
     */
    public function toArray()
    {
        return array(
            'code' => $this->code,
            'msg' => $this->getMsg(),
            'data' => $this->getData(),
        );
    }

    /**
     * 这里会调用__toString这个魔术方法
     * output result
     * @return string
     */
    public function sendResult()
    {
        header("Content-Type: application/json; charset=UTF-8");
        return (string)$this;
    }

    /**
     * reset value
     * @return $this
     */
    public function reset()
    {
        $this->setCode(null);
        $this->setMsg(null);
        $this->setData(null);
        return $this;
    }
}