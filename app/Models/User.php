<?php

namespace App\Models;

use App\Enums\ActivtyType;
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
        'name',
        'email',
        'phone',
        'password',
        'remember_token',
        'image',
        'activity',
        'wallet',
        'role',
        'email_code',
        'email_verified',
        'id_token',
    ];

    protected $casts = [
        'activity' => ActivtyType::class,
    ];

    public $timestamps = true;

    protected $appends =['image_link'];

    public function getImageLinkAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

}
