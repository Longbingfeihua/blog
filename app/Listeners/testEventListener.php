<?php

namespace App\Listeners;

use App\Events\testEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class testEventListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  testEvent  $event
     * @return void
     */
    public function handle(testEvent $event)
    {
    }
}
