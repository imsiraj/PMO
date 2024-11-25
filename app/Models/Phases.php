<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Phases extends Model
{
    use HasFactory;  
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $table = 'phases';

    protected $fillable = [
        'project_id',
        'title',
        // 'sprint_id',
        'revision_number',
        'created_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public static function findPhaseByTitle($title,$projectId){
        return self::where('title',$title)->where('project_id',$projectId)->get()->toArray();
    }
    
    public static function getAllPhases($projectId){
        return self::select('title','id')->where('project_id',$projectId)->orderBy('id','ASC')->get()->toArray();
    }

    public static function getPhasesByTitle($projectId,$title=null){
        $query = self::query();
        $query->select('id','title');
        $query->where('project_id',$projectId);
        if($title){
            $query->where('title','like','%'.$title.'%');
        }
        return $query->orderBy('title','ASC')->get()->toArray();
    }

    public static function getPhasesCountByProject($projectId){
        return self::where('project_id',$projectId)->count();
    }
}
