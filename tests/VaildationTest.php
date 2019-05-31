<?php
/**
 * Created by PhpStorm.
 * User: fengzyz
 * Date: 2019/3/19
 * Time: 9:27
 */

use PHPUnit\Framework\TestCase;


class VaildationTest extends TestCase
{
    public function testIsMobile()
    {
//        $rs = \Fengzyz\Validation\Mobile::isMobile('1557487141');
        $rs = \Fengzyz\Validation\Email::isEmail('15574871411@qqqq.com');
        var_dump($rs);
        exit();
    }
}