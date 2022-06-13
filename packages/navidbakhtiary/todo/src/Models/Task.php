<?php

namespace NavidBakhtiary\ToDo\Models;

use App\Models\User as AppUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Task extends Model
{
    use Notifiable;

    protected $fillable = ['title', 'description', 'status'];

    public static $status_close = 'Close';
    public static $status_open = 'Open';

    public function labels()
    {
        return $this->belongsToMany(Label::class, TaskLabel::class);
    }

    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

    public function routeNotificationForMail($notification)
    {
        return $this->user->email;
    }

    public function user()
    {
        return $this->belongsTo(AppUser::class);
    }
}
