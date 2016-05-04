<?php
namespace Illuminate\Support\Facades;
class Kotana extends Facade{
    public static function getFacadeAccessor()
    {
        /*
         * 单列singleton()多为alias,非单例bind(xx)多为abstract
         */
        return '\Illuminate\Kotana\Ko';
    }
}