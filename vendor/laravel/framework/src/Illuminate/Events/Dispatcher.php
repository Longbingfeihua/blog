<?php

namespace Illuminate\Events;

use Exception;
use ReflectionClass;
use Illuminate\Support\Str;
use Illuminate\Container\Container;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Contracts\Container\Container as ContainerContract;

class Dispatcher implements DispatcherContract
{
    /**
     * The IoC container instance.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * The registered event listeners.
     *
     * @var array
     */
    protected $listeners = [];

    /**
     * The wildcard listeners.
     *
     * @var array
     */
    //通配符
    protected $wildcards = [];

    /**
     * The sorted event listeners.
     *
     * @var array
     */
    //有序的事件监听器。
    protected $sorted = [];

    /**
     * The event firing stack.
     *
     * @var array
     */
    //事件触发堆栈
    protected $firing = [];

    /**
     * The queue resolver instance.
     *
     * @var callable
     */
    //解析实例队列
    protected $queueResolver;

    /**
     * Create a new event dispatcher instance.
     *
     * @param  \Illuminate\Contracts\Container\Container|null  $container
     * @return void
     */
    public function __construct(ContainerContract $container = null)
    {
        $this->container = $container ?: new Container;
    }

    /**
     * Register an event listener with the dispatcher.
     *
     * @param  string|array  $events
     * @param  mixed  $listener
     * @param  int  $priority
     * @return void
     */
    //$priority优先级
    public function listen($events, $listener, $priority = 0)
    {
        foreach ((array) $events as $event) {
            if (Str::contains($event, '*')) {//包含通配符*,同一个监听器拦截多个事件,无优先级.
                $this->setupWildcardListen($event, $listener);
            } else {
                $this->listeners[$event][$priority][] = $this->makeListener($listener);

                //删除有序事件监听器堆栈中相应的事件(如果存在的话)
                unset($this->sorted[$event]);
            }
        }
    }

    /**
     * Setup a wildcard listener callback.
     *
     * @param  string  $event
     * @param  mixed  $listener
     * @return void
     */
    //设置通配符监听器数组
    protected function setupWildcardListen($event, $listener)
    {
        $this->wildcards[$event][] = $this->makeListener($listener);
    }

    /**
     * Determine if a given event has listeners.
     *
     * @param  string  $eventName
     * @return bool
     */
    //判断给定事件是否有对应监听器
    public function hasListeners($eventName)
    {
        return isset($this->listeners[$eventName]) || isset($this->wildcards[$eventName]);
    }

    /**
     * Register an event and payload to be fired later.
     *
     * @param  string  $event
     * @param  array  $payload
     * @return void
     */
    public function push($event, $payload = [])
    {
        $this->listen($event.'_pushed', function () use ($event, $payload) {
            $this->fire($event, $payload);
        });
    }

    /**
     * Register an event subscriber with the dispatcher.
     *
     * @param  object|string  $subscriber
     * @return void
     */
    //注册订阅者
    public function subscribe($subscriber)
    {
        $subscriber = $this->resolveSubscriber($subscriber);

        $subscriber->subscribe($this);
    }

    /**
     * Resolve the subscriber instance.
     *
     * @param  object|string  $subscriber
     * @return mixed
     */
    //解析订阅者实例
    protected function resolveSubscriber($subscriber)
    {
        if (is_string($subscriber)) {
            return $this->container->make($subscriber);
        }

        return $subscriber;
    }

    /**
     * Fire an event until the first non-null response is returned.
     *
     * @param  string|object  $event
     * @param  array  $payload
     * @return mixed
     */
    public function until($event, $payload = [])
    {
        return $this->fire($event, $payload, true);
    }

    /**
     * Flush a set of pushed events.
     *
     * @param  string  $event
     * @return void
     */
    public function flush($event)
    {
        $this->fire($event.'_pushed');
    }

    /**
     * Get the event that is currently firing.
     *
     * @return string
     */
    public function firing()
    {
        return last($this->firing);
    }

    /**
     * Fire an event and call the listeners.
     *
     * @param  string|object  $event
     * @param  mixed  $payload
     * @param  bool  $halt
     * @return array|null
     */
    //$halt  此事件对应的监听器集合中第一个返回值不为空时,是否停止执行后续监听器
    public function fire($event, $payload = [], $halt = false)
    {
        //若给定事件参数是一个对象,programme假设此参数是事件对象,将对象类名作为事件名,对象本身作为handle参数,这将使基于对象的事件处理变得非常简单.
        if (is_object($event)) {
            list($payload, $event) = [[$event], get_class($event)];
        }

        $responses = [];

        //$payload设置为array可方便call_user_func_array($func,$arr)逐个调用
        if (! is_array($payload)) {
            $payload = [$payload];//(array)$payload
        }
        //放入即将触发的事件堆栈
        $this->firing[] = $event;

        //是否需要广播
        if (isset($payload[0]) && $payload[0] instanceof ShouldBroadcast) {
            $this->broadcastEvent($payload[0]);
        }
        //根据事件获取监听器集合
        foreach ($this->getListeners($event) as $listener) {
            //带参数$payload调用监听器闭包,此处是逐个调用$payload数组的值:若想获取详细值$arr,则$payload应为[$arr]
            //$listener默认触发handle方法.
            $response = call_user_func_array($listener, $payload);

            //如果监听器返回了response,若$halt == true,programme将返回此response,不会触发剩余的监听器,否则此response将会被放入到response数组.
            //停止一个事件传播到其它的侦听器。你可以通过在侦听器的监听方法(如果没有设置的话为handle())中返回 false 来实现
            if (! is_null($response) && $halt) {
                //移除此event
                array_pop($this->firing);

                return $response;
            }

            //如果一个监听器返回false,programme将停止遍历监听链上后续监听器,否则programme将循环遍历监听器集合,并依次触发.
            if ($response === false) {
                break;
            }

            $responses[] = $response;
        }

        array_pop($this->firing);

        return $halt ? null : $responses;
    }

    /**
     * Broadcast the given event class.
     *
     * @param  \Illuminate\Contracts\Broadcasting\ShouldBroadcast  $event
     * @return void
     */
    protected function broadcastEvent($event)
    {
        if ($this->queueResolver) {
            $connection = $event instanceof ShouldBroadcastNow ? 'sync' : null;

            $queue = method_exists($event, 'onQueue') ? $event->onQueue() : null;

            $this->resolveQueue()->connection($connection)->pushOn($queue, 'Illuminate\Broadcasting\BroadcastEvent', [
                'event' => serialize(clone $event),
            ]);
        }
    }

    /**
     * Get all of the listeners for a given event name.
     *
     * @param  string  $eventName
     * @return array
     */
    public function getListeners($eventName)
    {
        $wildcards = $this->getWildcardListeners($eventName);

        if (! isset($this->sorted[$eventName])) {
            $this->sortListeners($eventName);
        }

        return array_merge($this->sorted[$eventName], $wildcards);
    }

    /**
     * Get the wildcard listeners for the event.
     *
     * @param  string  $eventName
     * @return array
     */
    protected function getWildcardListeners($eventName)
    {
        $wildcards = [];

        foreach ($this->wildcards as $key => $listeners) {
            if (Str::is($key, $eventName)) {
                $wildcards = array_merge($wildcards, $listeners);
            }
        }

        return $wildcards;
    }

    /**
     * Sort the listeners for a given event by priority.
     *
     * @param  string  $eventName
     * @return array
     */
    //$this->listeners[$event][$priority][] = $this->makeListener($listener);
    protected function sortListeners($eventName)
    {
        $this->sorted[$eventName] = [];

        // If listeners exist for the given event, we will sort them by the priority
        // so that we can call them in the correct order. We will cache off these
        // sorted event listeners so we do not have to re-sort on every events.
        if (isset($this->listeners[$eventName])) {
            krsort($this->listeners[$eventName]); //按键名逆序排序

            //按顺序将监听器归于一个一维索引数组,由于参数是索引数组,故此处按数组先后顺序,后面贴前面,但若是关联数组则同键名后覆盖前
            $this->sorted[$eventName] = call_user_func_array(
                'array_merge', $this->listeners[$eventName]
            );
        }
    }

    /**
     * Register an event listener with the dispatcher.
     *
     * @param  mixed  $listener
     * @return mixed
     */
    //判断:监听器是字符串则构建监听器闭包函数
    public function makeListener($listener)
    {
        return is_string($listener) ? $this->createClassListener($listener) : $listener;
    }

    /**
     * Create a class based listener using the IoC container.
     *
     * @param  mixed  $listener
     * @return \Closure
     */
    //构建监听器闭包函数
    public function createClassListener($listener)
    {
        $container = $this->container;

        return function () use ($listener, $container) {
            return call_user_func_array(
                $this->createClassCallable($listener, $container), func_get_args()
            );
        };
    }

    /**
     * Create the class based event callable.
     *
     * @param  string  $listener
     * @param  \Illuminate\Container\Container  $container
     * @return callable
     */
    //listener是一个控制器带方法的url还是一个单纯的Class url(默认handle方法)
    protected function createClassCallable($listener, $container)
    {
        list($class, $method) = $this->parseClassCallable($listener);

        if ($this->handlerShouldBeQueued($class)) {
            return $this->createQueuedHandlerCallable($class, $method);
        } else {
            return [$container->make($class), $method];
        }
    }

    /**
     * Parse the class listener into class and method.
     *
     * @param  string  $listener
     * @return array
     */
    //方法@隔开,默认artisan生成的handle方法
    protected function parseClassCallable($listener)
    {
        $segments = explode('@', $listener);

        return [$segments[0], count($segments) == 2 ? $segments[1] : 'handle'];
    }

    /**
     * Determine if the event handler class should be queued.
     *
     * @param  string  $class
     * @return bool
     */
    //检测listener类是否继承了'Illuminate\Contracts\Queue\ShouldQueue'接口
    protected function handlerShouldBeQueued($class)
    {
        try {
            return (new ReflectionClass($class))->implementsInterface(
                'Illuminate\Contracts\Queue\ShouldQueue'
            );
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Create a callable for putting an event handler on the queue.
     *
     * @param  string  $class
     * @param  string  $method
     * @return \Closure
     */
    //若类中存在queue方法,则在不调用构造函数的情况下调用queue方法
    protected function createQueuedHandlerCallable($class, $method)
    {
        return function () use ($class, $method) {
            $arguments = $this->cloneArgumentsForQueueing(func_get_args());

            if (method_exists($class, 'queue')) {
                $this->callQueueMethodOnHandler($class, $method, $arguments);
            } else {
                $this->resolveQueue()->push('Illuminate\Events\CallQueuedHandler@call', [
                    'class' => $class, 'method' => $method, 'data' => serialize($arguments),
                ]);
            }
        };
    }

    /**
     * Clone the given arguments for queueing.
     *
     * @param  array  $arguments
     * @return array
     */
    //过滤参数数组,是对象则克隆,不是则不处理
    protected function cloneArgumentsForQueueing(array $arguments)
    {
        return array_map(function ($a) {
            return is_object($a) ? clone $a : $a;
        }, $arguments);
    }

    /**
     * Call the queue method on the handler class.
     *
     * @param  string  $class
     * @param  string  $method
     * @param  array  $arguments
     * @return void
     */
    //不调用监听器类的构造函数去创建新的类实例
    protected function callQueueMethodOnHandler($class, $method, $arguments)
    {
        $handler = (new ReflectionClass($class))->newInstanceWithoutConstructor();

        $handler->queue($this->resolveQueue(), 'Illuminate\Events\CallQueuedHandler@call', [
            'class' => $class, 'method' => $method, 'data' => serialize($arguments),
        ]);
    }

    /**
     * Remove a set of listeners from the dispatcher.
     *
     * @param  string  $event
     * @return void
     */
    //删除对应已缓存的事件监听器集合:通配的,刚输入的,已排序的>>>[wildcards,listeners,sorted]
    public function forget($event)
    {
        if (Str::contains($event, '*')) {
            unset($this->wildcards[$event]);
        } else {
            unset($this->listeners[$event], $this->sorted[$event]);
        }
    }

    /**
     * Forget all of the pushed listeners.
     *
     * @return void
     */
    public function forgetPushed()
    {
        foreach ($this->listeners as $key => $value) {
            if (Str::endsWith($key, '_pushed')) {
                $this->forget($key);
            }
        }
    }

    /**
     * Get the queue implementation from the resolver.
     *
     * @return \Illuminate\Contracts\Queue\Queue
     */
    protected function resolveQueue()
    {
        return call_user_func($this->queueResolver);
    }

    /**
     * Set the queue resolver implementation.
     *
     * @param  callable  $resolver
     * @return $this
     */
    public function setQueueResolver(callable $resolver)
    {
        $this->queueResolver = $resolver;

        return $this;
    }
}
