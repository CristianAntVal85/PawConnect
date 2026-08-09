<?php

namespace App\Models;

use Illuminate\Notifications\DatabaseNotification;

class Notification extends DatabaseNotification
{
    protected $table = 'notifications';

    protected $keyType = 'string';

    public $incrementing = false;
}
