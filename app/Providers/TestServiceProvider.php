<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class TestServiceProvider extends ServiceProvider
{
    /**
     * 是否延迟加载服务提供者的服务
     */
    public $defer = true;

    /**
     * 该服务提供者所提供的服务名称
     */
    public function provides()
    {
        return ['mox'];
    }
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('mox',function($app){
            return $app->make('config')->get('app.timezone');
        });
    }
}
