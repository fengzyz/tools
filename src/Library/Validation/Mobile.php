<?php
/**
 * Created by PhpStorm.
 * User: fengzyz
 * Date: 2019/3/19
 * Time: 9:21
 */


namespace Fengzyz\Validation;

class Mobile
{
    protected static $regRex = "/^((\+86)|(86))?((1\d{10})|(00\d{11})|(852\d{8}))$/";

    /**
     *
     * @param $mobile
     * @return mixed
     */
    public static function isMobile($mobile)
    {
        $reg = array(
            "options" => array("regexp" => static::$regRex)
        );
        return filter_var($mobile, FILTER_VALIDATE_REGEXP, $reg);
    }
}