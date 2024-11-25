<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GlobalStatus extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $table = 'global_status';

    protected $fillable = [
        'title',
        'revision_number',
        'created_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public static function findStatusByTitle($title){
        return self::where('title',$title)->get()->toArray();
    }
}
