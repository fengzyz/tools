<?php
/**
 * Created by PhpStorm.
 * User: fengzyz
 * Date: 2019/4/16
 * Time: 13:37
 */

namespace Fengzyz\Jwt;

use Tymon\JWTAuth\Providers\LumenServiceProvider as SystemLumenServiceProvider;

class LumenServiceProvider extends SystemLumenServiceProvider
{
    public function boot()
    {
        $auth = $this->app['auth'];
        $auth->provider('fengzyz', function ($app, array $config) {
            return new JWTUserProvider($app['hash'], $config['model']);
        });
        parent::boot();
    }
}