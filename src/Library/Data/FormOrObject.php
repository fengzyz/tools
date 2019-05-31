<?php

/**
 * FormOrObject.php
 *
 */

namespace Fengzyz\Data;

/**
 * Class FormOrObject
 * @package MYH\Data
 */
class FormOrObject
{
    /**
     * Example ：fooBar to foo_bar
     * @param $origData [必须是数组]
     * @param null $targetData [可以为空和model对象]
     */
    public static function formToObject($origData, &$targetData = null)
    {
        if (!empty($origData) && is_array($origData)
            && (empty($targetData) || (!empty($targetData) && (is_object($targetData))))
        ) {
            if (!empty($targetData) && is_object($targetData)) {
                $obj = new \ReflectionObject($targetData);
                foreach ($origData as $k => $v) {
                    print_r($k);
                    $newK = self::uncamelize($k);
                    if ($obj->hasProperty($newK)) {
                        $targetData->$newK = is_numeric($v) ? $v : (empty(trim($v)) ? null : $v);
                    }
                }
            } else {
                foreach ($origData as $k => $v) {
                    print_r($k);
                    $newK = self::uncamelize($k);
                    $v2 = $v;
                    if (is_array($v)) {
                        self::formToObject($v, $v2);
                    }
                    $targetData[$newK] = $v2;
                }
            }
        }
    }

    /**
     * Example ：foo_bar to fooBar
     * @param $origData
     * @param null $targetData
     */
    public static function objectToForm($origData, &$targetData = null)
    {
        if (is_array($origData)) {
            foreach ($origData as $k => $v) {

                $v2 = $v;
                if (is_numeric($k)) {
                    $newK = $k;
                } else {
                    $newK = lcfirst(Text::camelize($k));
                }

                if (is_array($v)) {
                    $v2 = [];
                    self::objectToForm($v, $v2);
                } else {
                    $newK = lcfirst(Text::camelize($k));
                }
                $targetData[$newK] = $v2;
            }
        } else if (is_object($origData) && ($origData instanceof \Phalcon\Mvc\Model)) {
            $obj = new \ReflectionObject($origData);
            $className = $obj->getName();
            if (!empty($obj->getProperties())) {
                foreach ($obj->getProperties() as $property) {
                    //add by functions for 因model中存在静态变量，通过箭头方式会报notice错误，这些静态变量可以不用转换 on 2017-11-20 11:07:25
                    if ($property->isStatic()) {
                        continue;
                    }
                    $k = $property->name;
                    $propertyClassName = $property->class;
                    $newK = lcfirst(Text::camelize($k));
                    if ($propertyClassName == $className) {
                        $targetData[$newK] = $origData->$k;
                    }
                }
            }
        } else if (is_object($origData) && ($origData instanceof \Phalcon\Mvc\Model\Resultset\Simple)) {
            foreach ($origData as $item) {
                $temp = null;
                static::objectToForm($item, $temp);
                $targetData[] = $temp;
            }
        }
    }


    /**
     * 转为驼峰格式
     */
    public static function camelize($str)
    {
        return preg_replace_callback('/_+([a-z])/', function ($matches) {
            return strtoupper($matches[1]);
        }, $str);
    }

    /**
     * @param $str
     * @return string
     */
    public static function unCamelize($str)
    {
        return strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $str));
    }

    /**
     * 把数组的key驼峰命名法转下划线
     * @param $arr
     * @return array
     */
    public static function parseKeysUnderline($arr)
    {
        foreach (array_keys($arr) as $v) {
            $array = array();
            for ($i = 0; $i < strlen($v); $i++) {
                if ($v[$i] == strtolower($v[$i])) {
                    $array[] = $v[$i];
                } else {
                    if ($i > 0) {
                        $array[] = '_';
                    }
                    $array[] = strtolower($v[$i]);
                }
            }
            $keys[] = implode('', $array);
        }

        return array_combine($keys, array_values($arr));
    }
}