<?php
/**
 * Created by PhpStorm.
 * User: fengzyz
 * Date: 2019/5/29
 * Time: 14:30
 */

namespace Fengzyz\Queue\RabbitMQ;

use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class MQConnection
{
    /**
     * @var
     */
    private $rabbitMQConn;

    /**
     * Connection constructor.
     * @throws \Illuminate\Container\EntryNotFoundException
     */
    public function __construct ()
    {
        $this->connect();
    }

    /**
     * @return mixed
     */
    public function getRabbitMQConn ()
    {
        return $this->rabbitMQConn;
    }

    /**
     * @param mixed $rabbitMQConn
     */
    public function setRabbitMQConn ($rabbitMQConn)
    {
        $this->rabbitMQConn = $rabbitMQConn;
    }


    /**
     *  连接 rabbitMQ
     */
    private function connect ()
    {
        try {
            $this->rabbitMQConn = new AMQPStreamConnection(
                MQConfig::config('host'),
                MQConfig::config('port'),
                MQConfig::config('login'),
                MQConfig::config('password'),
                MQConfig::config('vhost')
            );
        } catch (\Exception $e) {
            Log::error("not connection", ['code' => $e->getCode(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'mgs' => $e->getMessage()]);
            throw new \InvalidArgumentException('not connect rabbitMQ service');
        }
    }
}