<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/',['middleware'=>'test',function () {
    // class_alias('Illuminate\Support\Facades\Kotana','KOKO');
    //return KOKO::test();
//        dd(app());
//        return spl_autoload_functions();
//        App::register('App\Providers\TestServiceProvider');
    //return App::make('env');
//    $string =  'http://www.php.net/kotana/lala/index.html';
//    preg_match('#/([^/]+)#',$string,$arr,0,18);
//    print_r($arr);
//    $arr = ['hello','iam','carry'];
//    function mix($a,$b){
//        echo $a.PHP_EOL;
//        return ucfirst($a).ucfirst($b);
//    }
//    return array_reduce($arr,'mix','everyone');
//    return spl_autoload_functions();
//    dd(Event::getListeners('App\Event\TestEvent'));
}]);
//preg_match()返回 pattern 的匹配次数。 它的值将是0次（不匹配）或1次，因为preg_match()在第一次匹配后 将会停止搜索。
//preg_match_all()不同于此，它会一直搜索subject 直到到达结尾。 如果发生错误preg_match()返回 FALSE。