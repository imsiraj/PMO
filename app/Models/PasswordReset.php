<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class PasswordReset extends Model
{
    use HasFactory;
    protected $table = 'password_resets';
    public $timestamps = false;
    protected $fillable = [
        'email',
        'token',
        'created_at',
    ];
    public static function getResetRequestByEmail($userEmail){
        return self::where('email',$userEmail)->first();
    } 
    public static function deleteResetRequestByEmail($userEmail){
        return self::where('email', $userEmail)->delete();
    }
    public static function updateOrInsertPasswordReset($email,$token){
        return self::updateOrInsert(
        [
            'email' => $email
        ],
        [
            'token' => Hash::make($token),
            'created_at' => now(),
        ]
        );
}
}