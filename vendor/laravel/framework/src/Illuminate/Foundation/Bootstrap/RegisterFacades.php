<?php

namespace Illuminate\Foundation\Bootstrap;

use Illuminate\Support\Facades\Facade;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Contracts\Foundation\Application;

class RegisterFacades
{
    /**
     * Bootstrap the given application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        Facade::clearResolvedInstances(); //初次启动时,清除内存中所有已经实例化的类

        Facade::setFacadeApplication($app);
        //$app->make('config')返回Repository对象
        AliasLoader::getInstance($app->make('config')->get('app.aliases'))->register();
    }
}
