<?php

namespace App\Http\Controllers\Web;

use Illuminate\View\View;

class NotificationController
{
    /**
     * Display all notifications
     */
    public function index(): View
    {
        return view('notifications.index');
    }
}
