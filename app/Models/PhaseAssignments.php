<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PhaseAssignments extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $table = 'phase_assignments';

    protected $fillable = [
        'project_id',
        'phase_id',
        'sprint_id',
        'phase_value',
        'status_id',
        'revision_number',
        'created_by',
        'created_at',
        'updated_at',
        'deleted_at',
        'created_at'
    ];

    public static function findAssignment($projectId,$phaseId,$sprintId){
        $query = self::query();

        $query->where('project_id', $projectId)
          ->where('phase_id', $phaseId)
          ->where('sprint_id', $sprintId);

          return $query->first();
    }

    public static function getAllPhasesByProjectAndSprint($projectId,$sprintId){
        $query = self::query();

        $query->select('phase_value','status_id','phase_id','sprint_id')
            ->where('project_id', $projectId)
            ->where('sprint_id', $sprintId)
            ->orderBy('phase_id','ASC');

          return $query->get()->toArray();
    }

    public static function findRecentAssignmentDataBySprintID($sprintId){
        return self::select([
            'project_id',
            'phase_id',
            'sprint_id',
            'phase_value',
            'status_id',
            'revision_number',
            'created_at'
            ])->where('sprint_id',$sprintId)->orderBy('id','DESC')->get();
    }
}
