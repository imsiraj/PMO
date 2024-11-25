<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'country_phonecode',
        'mobile_number',
        'remember_token',
        'status',
        'u_roles',
        'created_by'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public static function getUserById($userId){
        return self::where('id',$userId)->first();
    }
    public static function updateEmailVerifiedByUser($userId){
        return self::where('id',$userId)->update(['email_verified_at'=>Carbon::now()]);
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strtoupper($value);
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtoupper($value);
    }
    public static function getUserByEmail($userEmail){
        return self::where('email',$userEmail)->first();
    } 
    public static function updatePasswordUpdatedAtByUser($userEmail){
        return self::where('email',$userEmail)->update(['password_updated_at'=>Carbon::now()]);
    }
}
