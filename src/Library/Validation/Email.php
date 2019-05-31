<?php
/**
 * Created by PhpStorm.
 * User: fengzyz
 * Date: 2019/3/19
 * Time: 9:24
 */

namespace Fengzyz\Validation;


class Email
{
    protected static $regRex = "/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/";

    /**
     *
     * @param $mobile
     * @return mixed
     */
    public static function isEmail($email)
    {
        $reg = array(
            "options" => array("regexp" => static::$regRex)
        );
        return filter_var($email, FILTER_VALIDATE_REGEXP, $reg);
    }
}