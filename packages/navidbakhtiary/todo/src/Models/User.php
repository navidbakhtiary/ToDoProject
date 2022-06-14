<?php

namespace NavidBakhtiary\ToDo\Models;

use App\User as AppUser;

class User extends AppUser
{
    protected $fillable = ['name'];

    protected $table = 'users';

    public function __construct(AppUser $user)
    {
        foreach ($user as $property => $value) 
        {
            $this->$property = $value;
        }
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
