<?php
/**
 * Created by PhpStorm.
 * User: fengzyz
 * Date: 2019/3/15
 * Time: 13:41
 */

use PHPUnit\Framework\TestCase;

use Fengzyz\Exception\ExceptionResult;

class ExceptionTest extends TestCase
{
    /**
     *
     */
    public function testThrowException()
    {
        ExceptionResult::throwException(200);
    }
}