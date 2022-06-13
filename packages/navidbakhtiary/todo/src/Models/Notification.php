<?php

namespace NavidBakhtiary\ToDo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;

class Notification extends DatabaseNotification
{
    public function task()
    {
        return $this->morphOne(Task::class, 'notifiable');
    }
}
