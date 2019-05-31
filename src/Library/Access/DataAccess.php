<?php
/**
 * Created by PhpStorm.
 * User: fengzyz
 * Date: 2019/4/19
 * Time: 18:17
 */

namespace Fengzyz\Access;


class DataAccess
{
    const REGION_ALL = 99;

    public function __construct()
    {
    }



    /**
     * 获取数据权限
     * @param array $access
     * @return array
     */
    public static function getAccess(array $access)
    {
        $regionId = $provinceId = $cityId = $districtId = [];
        if (!empty($access)) {
            $accessArr = [];
            foreach ($access as $row) {
                $accessArr[$row['access_level']][] = $row['area_code'];
            }
            if (!array_key_exists(99,$accessArr)){
                $parentCode = array_unique(array_column($access, 'access_level'));
                if (in_array(1, $parentCode)) {
                    $regionId = array_unique($accessArr[1]);
                }
                if (in_array(2, $parentCode)) {
                    $provinceId = array_unique($accessArr[2]);
                }
                if (in_array(3, $parentCode)) {
                    $cityId = array_unique($accessArr[3]);
                }
                if (in_array(4, $parentCode)) {
                    $districtId = array_unique($accessArr[4]);
                }
            } else {
                $regionId = self::REGION_ALL;
            }
        }
        return [$regionId, $provinceId, $cityId, $districtId];
    }
}