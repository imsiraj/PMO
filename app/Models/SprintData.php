<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SprintData extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $table = 'sprint_data';

    protected $fillable = [
        'sprint_id',
        'new_sr_no',
        'new_module_screens',
        'sprint_number',
        'team_name',
        'module_screens',
        'access_required_for_role',
        'extra_column_one',
        'extra_column_two',
        'web_backend',
        'priority_bs',
        'responsible_owner',
        'responsible_team',
        'owner_or_team_comments',
        'comments_post_discussion_with_stakeholder',
        'derived_priority',
        'member_size',
        'in_app_communications',
        'changes_done_in_actual_master',
        'change_type',
        'description_of_changes',
        'change_done_on_date',
        'where_these_tasks_have_been_added',
        'extra_column_three',
        'extra_column_four',
        'incomplete_basis_weightages',
        'completed_basis_actual_progress',
        'complete_status_percentage',
        'incomplete_status_percentage',
        'incomplete_basis_actual',
        'incomplete_sprint_size_basis_weightage',
        'feature',
        'demo_given',
        'demo_given_on',
        'approved_by',
        'approved_on',
        'feedback_by_stakeholder',
        'stakeholder_comments',
        'screenshot_link_one',
        'screenshot_link_two',
        'screenshot_link_three',
        'screenshot_link_four',
        'screenshot_link_five',
        'uat_demo_status',
        'web_login_details',
        'all_project',
        'created_by',
        'updated_by',
        'deleted_by',
        'created_at'
    ];

    public static function findDataBySprint($sprint){
        return self::where('sprint_id',$sprint)->first();
    }

    public static function findRecentSprintDataBySprintID($sprintId){
        return self::select([
            'id',
            'new_sr_no',
            'new_module_screens',
            'sprint_number',
            'team_name',
            'module_screens',
            'access_required_for_role',
            'extra_column_one',
            'extra_column_two',
            'web_backend',
            'priority_bs',
            'responsible_owner',
            'responsible_team',
            'owner_or_team_comments',
            'comments_post_discussion_with_stakeholder',
            'derived_priority',
            'member_size',
            'in_app_communications',
            'changes_done_in_actual_master',
            'change_type',
            'description_of_changes',
            'change_done_on_date',
            'where_these_tasks_have_been_added',
            'extra_column_three',
            'extra_column_four',
            'incomplete_basis_weightages',
            'completed_basis_actual_progress',
            'complete_status_percentage',
            'incomplete_status_percentage',
            'incomplete_basis_actual',
            'incomplete_sprint_size_basis_weightage',
            'feature',
            'demo_given',
            'demo_given_on',
            'approved_by',
            'approved_on',
            'feedback_by_stakeholder',
            'stakeholder_comments',
            'screenshot_link_one',
            'screenshot_link_two',
            'screenshot_link_three',
            'screenshot_link_four',
            'screenshot_link_five',
            'uat_demo_status',
            'web_login_details',
            'all_project',
            'created_at'
            ])->where('sprint_id',$sprintId)->orderBy('id','DESC')->first();
    }
}
