<?php
/**
 * Created by PhpStorm.
 * User: fengzyz
 * Date: 2019/5/29
 * Time: 16:55
 */

namespace Fengzyz\Queue\RabbitMQ;

use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMq
{
    static private $_instance;
    static private $queue;
    static private $_conn;
    static private $channel;
    /**
     * RabbitMq constructor.
     */
    public function __construct ()
    {
        self::$_conn = new MQConnection();
    }

    /**
     * @return RabbitMq
     */
    public static function getInstance ()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
            return self::$_instance;
        }
        return self::$_instance;
    }

    /**
     * @param $exchangeName
     * @param $queuename
     */
    public function listen ($exchangeName, $queuename)
    {
        self::$queue = $queuename;
        $this->setChannel();
        return $this->setExchange($exchangeName, $queuename);

    }

    /**
     * 处理数据
     * @param $className
     * @param string $func
     */
    public function run ($className, $func = '')
    {
        if ($className) {

            $callback = function ($message) use ($className, $func) {
                Log::info('queueInfo:' . $message->body);
                if ($func) {
                    (new $className())->$func($message->body);
                } else {
                    new $className($message->body);
                }
                $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
            };

            $this->getChannel()->basic_consume(self::$queue, "", false, false, false, false, $callback);
            while (count($this->getChannel()->callbacks)) {
                $this->getChannel()->wait();
            }
            // 关闭
            $this->cloneChannel();
            $this->cloneConn();
        } else {
            throw new \InvalidArgumentException('not find class name or function name');
        }
    }

    /**
     * @return mixed
     */
    private function setChannel ()
    {
        self::$channel = self::$_conn->getRabbitMQConn()->channel();
    }

    /**
     * @return mixed
     */
    public function getChannel ()
    {
        return self::$channel;
    }

    /**
     * 连接交换机
     * @param $exchangeName
     * @param $queueName
     */
    public function setExchange ($exchangeName, $queueName)
    {
        $this->getChannel()->exchange_declare($exchangeName, 'direct', false, true, false);    // 初始化交换机
        $this->getChannel()->queue_declare($queueName, false, true, false, false);             // 初始化队列

        return $this;
    }

    /**
     *  推送消息
     * @param $msg
     */
    public function pushlish ($msg, $replyTo = '')
    {
        $msg = new AMQPMessage($msg, ['reply_to' => $replyTo]);
        $this->getChannel()->basic_publish($msg, "", self::$queue);//发送消息到队列
        $this->cloneChannel();//关闭通道
        $this->cloneConn();//关闭连接
    }

    /**
     *  关闭渠道
     */
    public function cloneChannel ()
    {
        $this->getChannel()->close();
    }

    /***
     *  关闭连接
     */
    public function cloneConn ()
    {
        self::$_conn->getRabbitMQConn()->close();
    }

    /**
     * __clone方法防止对象被复制克隆
     */
    public function __clone ()
    {
        trigger_error('Clone is not allow!', E_USER_ERROR);
    }
}