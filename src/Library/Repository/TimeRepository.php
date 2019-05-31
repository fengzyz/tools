<?php


namespace Fengzyz\Repository;


class TimeRepository
{


    /**
     * 获取时间查询
     * @param $query
     * @param $where
     * @param string $column
     * @return mixed
     */
    public static function queryCreateTime($query, $where,$column = 'created_at')
    {
        $startTime = (isset($where['startTime']) && !empty($where['startTime']) ) ? date("Y-m-d 00:00:00",strtotime($where['startTime'])) : 0;
        $endTime = isset($where['endTime']) && !empty($where['endTime'])  ?  date("Y-m-d 23:59:59", strtotime($where['endTime']))  : date("Y-m-d 23:59:59",time());
        $query->whereBetween($column,[$startTime,$endTime]);
        return $query;
    }

}