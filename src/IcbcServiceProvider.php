<?php

namespace Hiworld\LaravelIcbc;

use Illuminate\Support\ServiceProvider;
use Hiworld\LaravelIcbc\Services\IcbcService;
use Illuminate\Support\Facades\Config;
use Illuminate\Foundation\Application;

class IcbcServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // 合并配置文件
        $this->mergeConfigFrom(
            __DIR__.'/../config/icbc.php', 'icbc'
        );

        // 注册服务到容器
        $this->app->singleton('icbc', function ($app) {
            return new IcbcService($app['config']['icbc']);
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // 发布配置文件
        $this->publishes([
            __DIR__.'/../config/icbc.php' => config_path('icbc.php'),
        ], 'icbc-config');
    }
} 