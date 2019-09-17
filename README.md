# tools
    开发中常用的类与函数封装
###  cline 请求
    Client::getInstance()->getRequest($url,[],$header);
    
###  添加验证
```
 // 邮箱验证
 $rs = \Fengzyz\Validation\Email::isEmail('479235966@qq.com'); /
 // 手机号码验证
 $rs = \Fengzyz\Validation\Mobile::isMobile('15865545545');

```

### Redis缓存使用

```
    添加缓存
    你可以使用Cache 门面上的put方法在缓存中存储缓存项。当你在缓存中存储缓存项的时候，你需要指定数据被缓存的时间（分钟数）：
    CacheRedis::pull($key,$name,$time)
    #add方法只会在缓存项不存在的情况下添加缓存项到缓存，如果缓存项被添加到缓存返回true，否则，返回false：
    CacheRedis::add($key,$name,$time)
    获取缓存
    $data = CacheRedis::get($key);
    
    // 获取并且添加
    $data = CacheRedis::cacheResult($key,$func,$time)
```      
在项目中 .env 中添加 CACHE_OPEN 配置   值为 true（打开缓存） 与 false （关闭缓存）

SQL 日志监听
    在 .evn 添加日志输出文件路径
```
  LOG_FILE_SQL_PATH = '' # 默认位置为 storyage/logs
```
   修改App\Providers\EventServiceProvider.php
   
```
 'Illuminate\Database\Events\QueryExecuted' => [
               'Fengzyz\Listeners\QueryListener'
           ] ,
```   
   
   
###  jwt 使用

**安装**
```
composer require  "tymon/jwt-auth": "1.*@rc"
```
使用教程可以参考：https://learnku.com/articles/10885/full-use-of-jwt
  
**注意**
  项目中有些服务没有直操作数据库，是以接口的模式请求的的数据，所以在使用jwt 需要重构类型 重构的的文件在 Library/Jwt 文件下
  
  在bootstrap/app 文件中的
 ```
   $app->register(Dingo\Api\Provider\LumenServiceProvider::class);
   修改成  $app->register(\Fengzyz\Jwt\LumenServiceProvider::class);
```
 
  需要修改的文件的配置auth文件
```
 'guards' => [
          'api' => [
              'driver' => 'jwt', // 默认的是token 改成 jwt 
              'provider' => 'users'
          ],
      ],

      'providers' => [
          'users' => [
              'driver' => 'fengzyz',   //  默认eloquent，需要改成 fengzyz
              'model' => \App\User::class
          ]
```

在配置文件中的 proivders['users']['model'] 是数据来源的model 但是在启动项目中
没有连接数据库，所以需要在 \App\User.php 修改，修改代码如下
 ```
<?php

namespace App;

use App\Services\UserService;
use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Tymon\JWTAuth\Contracts\JWTSubject;    

class User extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email',
    ];
    protected $primaryKey = 'user_id';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * 获取唯一标识的，可以用来认证的字段名，比如 id，guid
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return $this->primaryKey;
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     *  获取用户信息
     * @param int $userId
     */
    public function getUser(int $userId)
    {
        return (new UserService())->getUser($userId);
    }
}

```

需要 implements Tymon\JWTAuth\Contracts\JWTSubject 接口,需要设置主键  protected $primaryKey = 'user_id'; 属性，但user_id 是查询数据员的主键，在添加 getUser()方法，来获取数据员

注意：参考Auth jwt 的源代码地址：https://segmentfault.com/a/1190000015095554


# 生成二维码
```
 public function test()
    {
        $fileName = time().'.png';
        $logoPath = storage_path('app/logo.png');
        $config = array(
            'ecc' => 'H',    // L-smallest, M, Q, H-best
            'size' => 12,    // 1-50
            'dest_file' => $fileName,
            'quality' => 90,
            'logo' => $logoPath,
            'logo_size' => 100,
            'logo_outline_size' => 20,
//            'logo_outline_color' => '#F0FFF0',
            'logo_radius' => 15,
            'logo_opacity' => 100,
        );
// 二维码内容
        $data = 'http://costalong.com';
// 创建二维码类
        $oPHPQRCode = new PHPQRCode();
// 设定配置
        $oPHPQRCode->setConfig($config);
// 创建二维码
        $qrcode = $oPHPQRCode->generate($data);
// 显示二维码
        echo '<img src="'.$qrcode.'?t='.time().'">';
    }
```

## 合成图片
```
     /**
     * [合併兩張圖片]
     * @param $source1 [图片一，大图]
     * @param $source2 [图片二，小图]
     * @param null $saveName [图片合成后输出路劲]
     * @param int $alpha [透明度]
     * @param int $position [偏移九宫格位置1,2,3,4,5,6,7,8,9]
     * @param null $posX [小图偏移位置]
     * @param null $posY [小图偏移位置]
     * @return bool
     */
 ImageTools::combine($fileName,'/var/www/img.png','/var/www/orgTest.jpg',0,5,40);
```

 
 
