<?php
/**
 * Created by PhpStorm.
 * User: fengzyz
 * Date: 2019/5/29
 * Time: 15:13
 */

namespace Fengzyz\Queue\RabbitMQ;

class MQConfig
{
    /**
     * 获取配置文件
     * @throws \Illuminate\Container\EntryNotFoundException
     */
    public static function config ($key = '')
    {
        $queueConfig = app('config')->get('queue');
        if (empty($queueConfig['connections']['rabbitmq'])) {
            throw new \InvalidArgumentException('get rabbitMQ config err !');
        }
        $rabbitmq = $queueConfig['connections']['rabbitmq'];
        return $key ? $rabbitmq[$key] : $rabbitmq;
    }
}