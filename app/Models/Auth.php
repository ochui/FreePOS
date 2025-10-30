<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auth extends Model
{
    protected $table = 'auth';
    public $timestamps = false;
    
    protected $fillable = [
        'username', 'name', 'password', 'token', 'uuid', 'admin', 'disabled', 'permissions'
    ];
    
    protected $hidden = [
        'password', 'token'
    ];
}
