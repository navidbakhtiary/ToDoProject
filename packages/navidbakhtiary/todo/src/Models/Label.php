<?php

namespace NavidBakhtiary\ToDo\Models;

use Illuminate\Database\Eloquent\Model;

class Label extends Model
{
    protected $fillable = ['name'];

    public function tasks()
    {
        return $this->belongsToMany(Task::class, TaskLabel::class);
    }
}
