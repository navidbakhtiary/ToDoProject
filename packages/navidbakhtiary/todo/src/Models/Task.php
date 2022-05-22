<?php

namespace NavidBakhtiary\ToDo\Models;

use App\Models\User as AppUser;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = ['title', 'description', 'status'];

    public static $status_close = 'Close';
    public static $status_open = 'Open';

    public function labels()
    {
        return $this->belongsToMany(Label::class, TaskLabel::class);
    }

    public function user()
    {
        return $this->belongsTo(AppUser::class);
    }
}
