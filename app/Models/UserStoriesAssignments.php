<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserStoriesAssignments extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $table = 'user_stories_assignments';

    protected $fillable = [
        'project_id',
        'sprint_id',
        'user_story_id',
        'story_data',
        'revision_number',
        'created_by',
        'created_at',
        'updated_at',
        'deleted_at',
        'created_at'
    ];

    public static function findAssignment($projectId,$sprintId,$storyId){
        $query = self::query();

        $query->where('project_id', $projectId)
          ->where('sprint_id', $sprintId)
          ->where('user_story_id', $storyId);

          return $query->first();
    }

    public static function getAllStoriesByProjectAndSprint($projectId,$sprintId){
        $query = self::query();

        $query->select('story_data','id')
            ->where('project_id', $projectId)
            ->where('sprint_id', $sprintId)
            ->orderBy('user_story_id','ASC');

          return $query->get()->toArray();
    }


    public static function findRecentAssignmentDataBySprintID($sprintId){
        return self::select([
            'project_id',
            'user_story_id',
            'sprint_id',
            'story_data',
            'revision_number',
            'created_at'
            ])->where('sprint_id',$sprintId)->orderBy('id','DESC')->get();
    }
}