<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeamAssignments extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $table = 'team_assignments';

    protected $fillable = [
        'project_id',
        // 'phase_id',
        'sprint_id',
        'team_id',
        'alloted_sprint',
        'status_id',
        'revision_number',
        'created_by',
        'created_at',
        'updated_at',
        'deleted_at',
        'created_at'
    ];

    public static function findAssignment($projectId,$sprintId,$teamId){
        $query = self::query();

        $query->where('project_id', $projectId)
        //   ->where('phase_id', $phaseId)
          ->where('sprint_id', $sprintId)
          ->where('team_id', $teamId);

          return $query->first();
    }

    public static function getAllTeamsByProjectAndSprint($projectId,$sprintId){
        $query = self::query();

        $query->select('alloted_sprint','id')
            ->where('project_id', $projectId)
            ->where('sprint_id', $sprintId)
            ->orderBy('team_id','ASC');

          return $query->get()->toArray();
    }

    public static function findRecentAssignmentDataBySprintID($sprintId){
        return self::select([
            'project_id',
            'sprint_id',
            'team_id',
            'alloted_sprint',
            'status_id',
            'revision_number',
            'created_at'
            ])->where('sprint_id',$sprintId)->orderBy('id','DESC')->get();
    }
}