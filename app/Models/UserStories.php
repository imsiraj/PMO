<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserStories extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $table = 'user_stories';

    protected $fillable = [
        'title',
        'project_id',
        'revision_number',
        'created_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public static function findStoryByTitle($title,$projectId){
        return self::where('title',$title)->where('project_id',$projectId)->get()->toArray();
    }

    public static function getAllStories($projectId){
        return self::select('title','id')->where('project_id',$projectId)->orderBy('id','ASC')->get()->toArray();
    }
    
}