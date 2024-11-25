<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use DefaultExcelColumns;

class ChangeLog extends Model
{
    use HasFactory;

    protected $dates = ['deleted_at'];
    protected $table = 'change_log';

    protected $fillable = [
        'project_id',
        'revision_number',
        'sprint_id',
        'column_name',
        'before_data',
        'after_data',
        'date_time',
        'row_number',
        'is_updated',
        'created_at',
        'updated_at',
        'action'
    ];

    /**
     * To get the last row number for given project and date in order to read the next set of data in change logs
     * @param projectId
     * @param date
     */
    public static function getLastRowNumber($projectId, $date)
    {
        return self::select('row_number')->where('project_id', $projectId)
            ->whereRaw('DATE(date_time) = ?', [$date])
            ->orderBy('id', 'DESC')
            ->first();
    }

    /**
     * To get the changes history done on given date for specific sprint item
     * @param projectId
     * @param date
     */
    public static function getLatestChangesByDate($projectId, $date, $currentDate)
    {
        return self::select('column_name as column', 'after_data as value', 'sprint_id as task_id')
            ->whereIn(DB::raw('(column_name, date_time,sprint_id)'), function ($query) use ($date, $currentDate, $projectId) {
                $query->select(DB::raw('column_name,MAX(date_time),sprint_id'))
                    ->from('change_log')
                    ->where('project_id', $projectId)
                    ->whereDate('date_time', '>', $currentDate)
                    ->whereDate('date_time', '<=', $date)
                    ->whereNotIn('column_name', [DefaultExcelColumns::ID])
                    ->groupBy('column_name', 'sprint_id');
            })
            ->get()->toArray();
    }
    /**
     * To get the changes history done on given date for specific sprint item
     * @param projectId
     * @param date
     */
    public static function getPreviousChangesByDate($projectId, $date)
    {
        return self::select('column_name as column', 'after_data as value', 'sprint_id as task_id')
            ->whereIn(DB::raw('(column_name, date_time,sprint_id)'), function ($query) use ($date, $projectId) {
                $query->select(DB::raw('column_name,MAX(date_time),sprint_id'))
                    ->from('change_log')
                    ->where('project_id', $projectId)
                    ->whereDate('date_time', '<=', $date)
                    ->whereNotIn('column_name', [DefaultExcelColumns::ID])
                    ->groupBy('column_name');
            })
            ->get()->toArray();
    }

    /**
     * To get the changes history done on given date for specific sprint item
     * @param projectId
     * @param date
     */
    public static function getLatestChangesByDateAndColumns($projectId, $date, $isUpdated, $columns)
    {
        return self::select('column_name as column', 'after_data as value', 'sprint_id as task_id')
            ->whereIn(DB::raw('(column_name, date_time)'), function ($query) use ($date, $projectId, $isUpdated, $columns) {
                $query->select(DB::raw('column_name, MAX(date_time)'))
                    ->from('change_log')
                    ->where('project_id', $projectId)
                    ->whereDate('date_time', $date)
                    ->whereNotIn('column_name', [DefaultExcelColumns::ID])
                    ->whereIn('column_name', $columns)
                    ->groupBy('column_name');
            })
            ->get()->toArray();
    }

    /**
     * To get the changes history done on given date for specific sprint item
     * @param projectId
     * @param date
     */
    public static function getPreviousChangesByDateAndColumns($projectId, $date, $columns)
    {
        return self::select('column_name as column', 'after_data as value', 'sprint_id as task_id')
            ->whereIn(DB::raw('(column_name, date_time)'), function ($query) use ($date, $projectId, $columns) {
                $query->select(DB::raw('column_name, MAX(date_time)'))
                    ->from('change_log')
                    ->where('project_id', $projectId)
                    ->whereDate('date_time', '<=', $date)
                    ->whereNotIn('column_name', [DefaultExcelColumns::ID])
                    ->whereIn('column_name', $columns)
                    ->groupBy('column_name');
            })
            ->get()->toArray();
    }

    public static function getLatestTeamHeaders($projectId, $header)
    {
        return self::select('column_name', 'before_data', 'after_data', 'date_time','action') // Select the required fields
            ->where('project_id', $projectId) // Filter by project ID
            ->where('column_name', $header) // Filter for the specific header
            ->orderBy('date_time', 'DESC') // Order by date_time in descending order to get the latest first
            ->first(); // Use first() to get only the latest change
    }
}
