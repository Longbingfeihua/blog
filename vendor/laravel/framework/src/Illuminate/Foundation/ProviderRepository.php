<?php

namespace Illuminate\Foundation;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;

class ProviderRepository
{
    /**
     * The application implementation.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The path to the manifest file.
     *
     * @var string
     */
    protected $manifestPath;

    /**
     * Create a new service repository instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @param  string  $manifestPath
     * @return void
     */
    public function __construct(ApplicationContract $app, Filesystem $files, $manifestPath)
    {
        //(new ProviderRepository($this, new Filesystem, $manifestPath))->load($this->config['app.providers']);
        $this->app = $app;
        $this->files = $files;
        $this->manifestPath = $manifestPath;
    }

    /**
     * Register the application service providers.
     *
     * @param  array  $providers
     * @return void
     */
    public function load(array $providers)
    {
        //获取所有provider数组.
        $manifest = $this->loadManifest();

        //每次请求都需要读取$manifest,通过和$providers进行对比,确定是否需要重新编译,最终确定哪些providers需要延迟加载
        if ($this->shouldRecompile($manifest, $providers)) {
            $manifest = $this->compileManifest($providers);
        }

        //为每个需要事件触发的服务提供者注册所需事件,当所需事件触发时,服务提供者自动加载
        foreach ($manifest['when'] as $provider => $events) {
            $this->registerLoadEvents($provider, $events);
        }

       //注册manifest['eager']中的所有服务提供者
        foreach ($manifest['eager'] as $provider) {
            $this->app->register($this->createProvider($provider));
        }
        //将manifest['deferred']与Application的deferredServices数组合并.
        $this->app->addDeferredServices($manifest['deferred']);
    }

    /**
     * Register the load events for the given provider.
     *
     * @param  string  $provider
     * @param  array  $events
     * @return void
     */
    //注册监听事件,当事件触发时,注册对应的服务提供者.
    protected function registerLoadEvents($provider, array $events)
    {
        if (count($events) < 1) {
            return;
        }

        $app = $this->app;

        $app->make('events')->listen($events, function () use ($app, $provider) {
            $app->register($provider);
        });
    }

    /**
     * Compile the application manifest file.
     *
     * @param  array  $providers
     * @return array
     */
    //将app.prociders编译为services.json文件.
    protected function compileManifest($providers)
    {
        //构建manifest数组.
        $manifest = $this->freshManifest($providers);

        foreach ($providers as $provider) {
            $instance = $this->createProvider($provider);

            //重新编译manifest时,laravel会解析每个provider,确定是否是延迟服务提供者.
            if ($instance->isDeferred()) {//检测provider是否将父类的$defer属性重写为了true;
                foreach ($instance->provides() as $service) {//将provider->provides()方法返回的需要延迟服务的数组遍历,循环加入到deferred中.
                    $manifest['deferred'][$service] = $provider;
                }
                //将需要条件出发的服务数组以provider为键名,插入到$manifest['when']下
                $manifest['when'][$provider] = $instance->when();
            }
            //如果provider的defer属性为false,那么它将会被插入到$manifest['eager']数组中,以后每次请求都会注册它.
            else {
                $manifest['eager'][] = $provider;
            }
        }
        //转换为json写到services.json
        return $this->writeManifest($manifest);
    }

    /**
     * Create a new provider instance.
     *
     * @param  string  $provider
     * @return \Illuminate\Support\ServiceProvider
     */
    //实例化provider
    public function createProvider($provider)
    {
        return new $provider($this->app);
    }

    /**
     * Determine if the manifest should be compiled.
     *
     * @param  array  $manifest
     * @param  array  $providers
     * @return bool
     */
    //当$manifest和app.providers数组不相等时,$manifest将会重新编译.
    public function shouldRecompile($manifest, $providers)
    {
        return is_null($manifest) || $manifest['providers'] != $providers;
    }

    /**
     * Load the service provider manifest JSON file.
     *
     * @return array|null
     */
    public function loadManifest()
    {
        //获取app()->basePath().'/bootstrap/services.json中的json数据,包含所有serviceProvider'
        if ($this->files->exists($this->manifestPath)) {
            $manifest = json_decode($this->files->get($this->manifestPath), true);

            return array_merge(['when' => []], $manifest);
        }
    }

    /**
     * Write the service manifest file to disk.
     *
     * @param  array  $manifest
     * @return array
     */
    public function writeManifest($manifest)
    {
        $this->files->put(
            $this->manifestPath, json_encode($manifest, JSON_PRETTY_PRINT)
        );

        return array_merge(['when' => []], $manifest);
    }

    /**
     * Create a fresh service manifest data structure.
     *
     * @param  array  $providers
     * @return array
     */
    //构建新的manifest数组.
    protected function freshManifest(array $providers)
    {
        return ['providers' => $providers, 'eager' => [], 'deferred' => []];
    }
}
