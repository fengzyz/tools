<?php
/**
 * Created by PhpStorm.
 * User: fengzyz
 * Date: 2019/3/15
 * Time: 13:07
 */

use PHPUnit\Framework\TestCase;


class RequertsTest extends TestCase
{
    public function testGetRequest()
    {
        $url = "http://test.server.com/test";
        $header = [
            'SERVER-NAME'=> 'base-server',
        ];
        $result = \Fengzyz\Requests\Client::getInstance()->getRequest($url,[],$header);
        print_r($result);
    }
}