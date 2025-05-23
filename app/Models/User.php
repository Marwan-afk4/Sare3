<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    use HasFactory;
    use HasApiTokens, Notifiable;

    protected $table = 'users';

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $fillable = [
        'email',
        'phone',
        'password',
        'remember_token',
        'image',
        'activity',
        'wallet',
        'role'
    ];

    public $timestamps = true;

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

}
