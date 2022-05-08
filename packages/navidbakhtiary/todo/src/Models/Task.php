<?php

namespace NavidBakhtiary\ToDo\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = ['title', 'description', 'status'];

    public static $statuses = ['Close', 'Open'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
