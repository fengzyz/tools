<?php
/**
 * Created by PhpStorm.
 * User: fengzyz
 * Date: 2019/3/28
 * Time: 9:34
 */

namespace Fengzyz\Repository;

use Fengzyz\Exception\ExceptionResult;
use Illuminate\Support\Facades\Log;

abstract class BaseRepository
{
    /**
     * 根据主键查找
     *
     * @param $id
     * @param $trashed
     * @return mixed
     */
    public function find($id, $trashed = false)
    {
        if ($trashed) {
            return $this->query()->withTrashed()->findOrFail($id);
        }
        return $this->query()->findOrFail($id);
    }

    /**
     * 查询资源集合
     *
     * @param bool $query_string
     * @param bool $keys
     * @param int $paginate
     * @param bool $trashed
     * @return mixed
     */
    public function getAll($where = [], $colunms = [], $orderBy = '', $paginate = 15, $trashed = false)
    {
        try {
            $query = $this->getQuery($where, $colunms, $orderBy, $trashed);
            return $query->paginate($paginate);
        } catch (\Exception $exception) {
            Log::error("未知错误，导致查询失败");
            ExceptionResult::throwException($exception->getCode());
        }
    }


    /**
     *  查询列表信息
     * @param array $where
     *   $where['create_at'] = array('bet',"2019-03-28");
     *   $where['create_at'] = array('between' , array($start , $end));
     *   $where['client_id'] = $client_id;
     *
     * @param array $colunms
     * @param bool $trashed
     *
     */
    public function queryList($where = [], $colunms = [], $orderBy = '', $trashed = false)
    {
        try {
            $model = $this->getQuery($where, $colunms, $orderBy, $trashed);
            return $model->get();
        } catch (\Exception $exception) {
            Log::error("未知错误，导致查询失败", ['msg' => $exception->getMessage()]);
            ExceptionResult::throwException($exception->getCode());
        }
    }

    /**
     * 查询详情信息
     * @param array $where
     * @param array $colunms
     * @param string $orderBy
     * @param bool $trashed
     * @return mixed
     */
    public function queryInfo($where = [], $colunms = [], $orderBy = '', $trashed = false)
    {
        try {
            $model = $this->getQuery($where, $colunms, $orderBy, $trashed);
            return $model->first();
        } catch (\Exception $exception) {
            Log::error("未知错误，导致查询失败", ['msg' => $exception->getMessage()]);
            ExceptionResult::throwException($exception->getCode());
        }
    }

    /**
     * @param array $where
     * @param array $colunms
     * @param string $orderBy
     * @param int $paginate
     * @param bool $trashed
     * @return mixed
     */
    public function getQuery($where = [], $colunms = [], $orderBy = '', $trashed = false)
    {
        $query = $this->query();
        $query = $colunms ? $query->select($colunms) : $query;
        if ($where && is_array($where)) {
            $query->where($where);
        }
        if ($trashed) {
            $query->withTrashed();
        }
        if ($orderBy) {
            $orderByArr = explode(' ', $orderBy);
            count($orderByArr) == 2 ? list($column, $sort) = $orderByArr : list($column) = $orderByArr;
            isset($sort) ? $query->orderBy($column, $sort) : $query->orderBy($column);
        }

        return $query;
    }

    /**
     * @param $query
     * @param bool $trashed
     * @return mixed
     */
    protected function isTrashed($query, $trashed = false)
    {
        return $trashed ? $query : $query->withTrashed();
    }

    /**
     * 创建查询构造器
     *
     * @return mixed
     */
    public function query()
    {
        return call_user_func(static::MODEL . '::query');
    }

    /**
     * 序列化模型实例
     *
     * @param array $attributes
     * @return mixed
     */
    abstract protected function serialization(array $attributes);
}