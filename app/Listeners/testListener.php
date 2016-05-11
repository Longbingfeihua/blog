<?php
/**
 * Created by PhpStorm.
 * User: zhangxian
 * Date: 16/5/11
 * Time: 上午9:55
 */
namespace App\Listeners;
use Illuminate\Support\Facades\Log;

class testListener{
    public function handle()
    {
       return Log::info('测试成功');
    }

    public function test(){
        return Log::info('哈哈哈');
    }
}