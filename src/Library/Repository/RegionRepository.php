<?php
/**
 * Created by PhpStorm.
 * User: fengzyz
 * Date: 2019/4/19
 * Time: 18:20
 */

namespace Fengzyz\Repository;


class RegionRepository
{

    /**
     *  区域查询查询条件
     * @param $query
     * @param $where
     * @param $indexRegionId
     * @param array $accessRegionId
     * @return mixed
     */
    public static function queryRegionCondition($query, $where, $indexRegionId, array $accessRegionId = [])
    {
        $where[$indexRegionId] = !empty($where[$indexRegionId]) ? $where[$indexRegionId] : '';
        return self::queryRegionId($query, $indexRegionId, $where[$indexRegionId], $accessRegionId);
    }

    /**
     *  获取区域查询
     * @param $query
     * @param $regionColumn
     * @param $regionId
     * @param array $accessRegionId
     * @return mixed
     */
    public static function queryRegionId($query, $regionColumn, $regionId, array $accessRegionId = [])
    {
        if ($query && $regionColumn) {
            if ($regionId && $accessRegionId) {
                $accessRegionId[] = $regionId;
                $query->whereIn($regionColumn, $accessRegionId);
            } elseif ($regionId && !$accessRegionId) {
                $query->where($regionColumn, $regionId);
            } elseif ($accessRegionId && !$regionId) {
                $query->whereIn($regionColumn, $accessRegionId);
            }
        }
        return $query;
    }

}