<?php
/**
 * Created by PhpStorm.
 * User: zhangxian
 * Date: 16/5/11
 * Time: 上午10:37
 */
namespace App\Listeners;
use Illuminate\Support\Facades\Log;

class testListener2{
    public function handle(){
        Log::info('luo');
    }
}