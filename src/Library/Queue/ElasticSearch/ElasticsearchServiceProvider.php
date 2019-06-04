<?php
/**
 * Created by PhpStorm.
 * User: shuyu
 * Date: 2019/6/4
 * Time: 16:00
 */

namespace Fengzyz\Queue\ElasticSearch;
use Laravel\Scout\EngineManager;
use Illuminate\Support\ServiceProvider;

class ElasticsearchServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        // 注册命令
        if ($this->app->runningInConsole()) {
            $this->commands([
                ImportCommand::class,
                FlushCommand::class,
            ]);
        }
        app(EngineManager::class)->extend('elasticsearch', function($app) {
            return new ElasticsearchEngine();
        });
    }
    /**
     * 在容器中注册绑定。
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/Config/laravel-scout-elasticsearch.php', 'scout'
        );
    }
}