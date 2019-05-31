<?php
/**
 * Created by PhpStorm.
 * User: fengzyz
 * Date: 2019/5/17
 * Time: 9:48
 */

use PHPUnit\Framework\TestCase;

class QrTest extends TestCase
{
    public function testQr ()
    {
        $url = 'http://costalong.com';
        $rs = Fengzyz\QrCode\Qr::make($url);
    }
}