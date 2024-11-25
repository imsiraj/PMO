<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Projects extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $table = 'projects';


    protected $fillable = [
        'code',
        'title',
        'description',
        'reporting_pm',
        'reporting_ba',
        'members',
        'priority_id',
        'documentation',
        'release_notes',
        'web_data',
        'ios_app_data',
        'android_app_data',
        'dev_ops_data',
        'start_date',
        'tech_stack',
        'total_sprints',
        'expected_end_date',
        'sprint_start_date',
        'revision_number',
        'created_by',
        'updated_by',
        'deleted_by',
        'sheet_link',
        'sheet_name',
        'sheet_range',
        'photo_path',
        'no_of_resource_groups',
        'no_of_phases',
        'no_of_days_in_sprint'
    ];

    public static function findProjectByCode($code)
    {
        return self::where('code', $code)->first();
    }
    public static function getAllProject()
    {
        return self::select('code')->whereNotNull('sheet_link')->orderBy('id', 'ASC')->get();
    }
    public static function getLastProjectId()
    {
        $lastProject = self::select('id')->orderBy('id', 'desc')->first();
        return $lastProject ? $lastProject->id : 0;
    }

    public static function findProjectByTitle($title)
    {
        return self::where('title', $title)->first();
    }
    public static function getAllProjectWithTitle()
    {
        return self::select('title','code','photo_path')->whereNotNull('sheet_link')->orderBy('id', 'ASC')->get()->toarray();
    }
}
