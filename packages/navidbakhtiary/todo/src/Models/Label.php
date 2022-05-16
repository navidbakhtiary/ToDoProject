<?php

namespace NavidBakhtiary\ToDo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Label extends Model
{
    protected $fillable = ['name'];

    public function tasks()
    {
        return $this->belongsToMany(Task::class, TaskLabel::class);
    }

    public function userTasks()
    {
        return $this->tasks()->where('user_id', Auth::user()->id);
    }
}
