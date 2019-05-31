<?php
/**
 * Created by PhpStorm.
 * User: fengzyz
 * Date: 2019/4/16
 * Time: 13:38
 */

namespace Fengzyz\Jwt;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;

class JWTUserProvider extends EloquentUserProvider
{
    /**
     * MyJWTUserProvider constructor.
     * @param HasherContract $hasher
     * @param $model
     */
    public function __construct(HasherContract $hasher, $model)
    {
        parent::__construct($hasher, $model);
    }

    /**
     *  获取用户信息
     * @param mixed $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|\Illuminate\Database\Eloquent\Model|null
     */
    public function retrieveById($identifier)
    {
        $model = $this->createModel();
        $user =  $model->getUser($identifier);
        if ($user){
            foreach ($user as $key => $val){
                $model->$key = $val;
            }
        }
        return $model;
//        return new GenericUser([]);
    }
}