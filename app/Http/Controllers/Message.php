<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Jobs\StoreMessage;

class Message extends Controller
{
    public function create(Request $request){
        $sort = 'queue1';
        $this->dispatch((new StoreMessage($request->all()))->onQueue($sort)->delay(1));
    }
}
