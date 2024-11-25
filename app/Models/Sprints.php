<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use SprintStatus;

class Sprints extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $table = 'sprints';

    protected $fillable = [
        'task_id',
        'project_id',
        'title',
        'complexity',
        'resources',
        'sprint_estimation',
        'sprint_adjustment',
        'sprint_size',
        'sprint_adhoc_task',
        'sprint_total',
        // 'sprint_start_date',
        // 'sprint_end_date',
        'status_id',
        'priority_id',
        'priority_id_stakeholder',
        'comments',
        'mobile_completion_status',
        'sprint_number',
        'requirement',
        'revision_number',
        'created_by',
        'created_at',
        'updated_at',
        'deleted_at',
        'created_at'
    ];

    public static function findSprintByTitle($title)
    {
        return self::where('title', $title)->first();
    }
    public static function findSprintByTask($taskId)
    {
        return self::where('task_id', $taskId)->first();
    }

    public static function getAllSprintData($projectId)
    {
        return self::select('sprints.*', 'sprint_data.*')
            ->join('sprint_data', 'sprints.id', '=', 'sprint_data.sprint_id')
            ->where('sprints.project_id', $projectId)->get();
    }

    public static function getAllSprintByStatus($projectId, $status)
    {
        return DB::table('sprints as s')
            ->select('s.sprint_total', DB::raw('GROUP_CONCAT(t.title SEPARATOR ", ") as team_names'))
            ->join('team_assignments as ta', 's.id', '=', 'ta.sprint_id')
            ->join('teams as t', 'ta.team_id', '=', 't.id')
            ->where('s.status_id', '=', $status)
            ->where('s.project_id', '=', $projectId)
            ->where('ta.alloted_sprint', '>', 0)
            ->groupBy('s.id')
            ->get();
    }

    public static function findRecentSprintByTaskID($taskId, $projectId)
    {
        return self::select(['id', 'task_id', 'project_id', 'title', 'complexity', 'resources', 'sprint_estimation', 'sprint_adjustment', 'sprint_size', 'sprint_adhoc_task', 'sprint_total', 'status_id', 'priority_id', 'priority_id_stakeholder', 'comments', 'mobile_completion_status', 'sprint_number', 'requirement', 'created_at'])->where('task_id', $taskId)->where('project_id', $projectId)->orderBy('id', 'DESC')->first();
    }

    public static function getPreviousChangesByDate($projectId, $date)
    {

        return DB::table('sprints as sp')
            ->join(DB::raw("(SELECT MAX(id) as max_id FROM sprints WHERE DATE(created_at) <= '$date' AND project_id='$projectId' GROUP BY task_id) as spc"), 'sp.id', '=', 'spc.max_id')
            ->join('sprint_data', 'sp.id', '=', 'sprint_data.sprint_id')
            ->select('sp.*', 'sprint_data.*')
            ->whereDate('sp.created_at', '<=', $date)
            ->where('sp.project_id', $projectId)
            ->orderBy('sp.task_id', 'asc')
            ->get()->toArray();
    }

    public static function getLatestChangesByDate($projectId, $date)
    {

        return DB::table('sprints as sp')
            ->join(DB::raw("(SELECT MAX(id) as max_id FROM sprints WHERE DATE(created_at) = '$date' AND project_id='$projectId' GROUP BY task_id) as spc"), 'sp.id', '=', 'spc.max_id')
            ->join('sprint_data', 'sp.id', '=', 'sprint_data.sprint_id')
            ->select('sp.*', 'sprint_data.*')
            ->whereDate('sp.created_at', '=', $date)
            ->where('sp.project_id', $projectId)
            ->orderBy('sp.task_id', 'asc')
            ->get();
    }

    public static function getTotalSprintSizesByTeamandDate($projectId, $date, $lastFridayDate)
    {
        $teams = DB::table('teams')
            ->select('title')
            ->get()
            ->keyBy('title')
            ->toArray();
        $totalSprints = DB::table('sprints as sp')
            ->select('t.title', 't.is_tech', DB::raw('SUM(ta.alloted_sprint) as total_alloted'))
            ->join('team_assignments as ta', 'sp.id', '=', 'ta.sprint_id')
            ->join('teams as t', 'ta.team_id', '=', 't.id')
            ->whereDate('sp.created_at', '<=', $date)
            ->whereDate('sp.created_at', '>', $lastFridayDate)
            ->where('sp.project_id', $projectId)
            ->groupBy('t.title', 't.is_tech')
            ->get()->toArray();
        $result = [];
        foreach ($teams as $title => $team) {
            $totalAlloted = 0.0;
            $is_tech = 0;
            foreach ($totalSprints as $sprint) {
                if ($sprint->title === $title) {
                    $totalAlloted = (float)$sprint->total_alloted;
                    $is_tech = $sprint->is_tech;
                    break;
                }
            }
            $result[] = (object)[
                'title' => $title,
                'is_tech' => $is_tech,
                'total_alloted' => $totalAlloted
            ];
        }
        return $result;
    }

    public static function getWorkingSprintSizeByTeamandDate($projectId, $date, $lastFridayDate)
    {
        $teams = DB::table('teams')
            ->select('title')
            ->get()
            ->keyBy('title')
            ->toArray();
        $workingSprints = DB::table('sprints as s')
            ->select('t.title', DB::raw('SUM(ta.alloted_sprint) as sprint_total'))
            ->join('team_assignments as ta', 's.id', '=', 'ta.sprint_id')
            ->join('teams as t', 'ta.team_id', '=', 't.id')
            ->whereIn('s.status_id', [
                SprintStatus::STATUS_IN_PROGRESS,
                SprintStatus::STATUS_USER_ACCEPTANCE,
                SprintStatus::STATUS_TESTING_PHASES,
                SprintStatus::STATUS_COMPLETED,
                SprintStatus::STATUS_REWORK
            ])
            ->whereDate('s.created_at', '<=', $date)
            ->whereDate('s.created_at', '>', $lastFridayDate)
            ->where('s.project_id', '=', $projectId)
            ->groupBy('t.title')
            ->get()->toArray();

        $result = [];
        foreach ($teams as $title => $team) {
            $result[] = (object)[
                'title' => $title,
                'sprint_total' => isset($workingSprints[$title]) ? (float)$workingSprints[$title]->total_alloted : 0.0
            ];
        }
        return $result;
    }

    public static function getWorkingPercentageByTeamandDate($projectId, $date, $lastFridayDate)
    {
        $totalSprints = self::getTotalSprintSizesByTeamandDate($projectId, $date, $lastFridayDate);
        $workingSprints = self::getWorkingSprintSizeByTeamandDate($projectId, $date, $lastFridayDate);

        $totalSprintMap = [];
        foreach ($totalSprints as $total) {
            $totalSprintMap[$total->title] = $total->total_alloted;
        }

        $result = [];
        foreach ($workingSprints as $working) {
            $teamTitle = $working->title;
            $workingTotal = $working->sprint_total;
            $totalAlloted = $totalSprintMap[$teamTitle] ?? 0;

            if ($totalAlloted > 0) {
                $workingPercentage = ($workingTotal / $totalAlloted) * 100;
            } else {
                $workingPercentage = 0;
            }

            $result[] = [
                'title' => $teamTitle,
                'working_percentage' => round($workingPercentage, 1) // Formatted string
            ];
        }
        return $result;
    }

    public static function getSummarySprintSize($projectId, $date, $weightages, $lastFridayDate)
    {
        $teams = DB::table('teams')
            ->select('title')
            ->get()
            ->keyBy('title')
            ->toArray();
        $query = DB::table('sprints as s')
            ->select('t.title', 's.status_id', DB::raw('SUM(ta.alloted_sprint) as sprint_total'))
            ->join('team_assignments as ta', 's.id', '=', 'ta.sprint_id')
            ->join('teams as t', 'ta.team_id', '=', 't.id')
            ->whereIn('s.status_id', array_keys($weightages))
            ->whereDate('s.created_at', '<=', $date)
            ->whereDate('s.created_at', '>', $lastFridayDate)
            ->where('s.project_id', '=', $projectId)
            ->groupBy('t.title', 's.status_id')
            ->get()->toArray();
        $weightedSprintSizes = [];
        $teamStatusTotals = [];

        foreach ($query as $row) {
            $teamTitle = $row->title;
            $statusId = $row->status_id;
            $sprintTotal = $row->sprint_total;


            if (!isset($teamStatusTotals[$teamTitle])) {
                $teamStatusTotals[$teamTitle] = [];
            }
            if (!isset($teamStatusTotals[$teamTitle][$statusId])) {
                $teamStatusTotals[$teamTitle][$statusId] = 0;
            }
            $teamStatusTotals[$teamTitle][$statusId] += $sprintTotal;
        }
        foreach ($teamStatusTotals as $teamTitle => $statuses) {
            $totalWeighted = 0;
            foreach ($statuses as $statusId => $sprintTotal) {
                $weight = $weightages[$statusId] ?? SprintStatus::DEFAULT_STATUS_WEIGHT;
                $weightedTotal = $sprintTotal * $weight;
                $totalWeighted += $weightedTotal;
            }
            $weightedSprintSizes[$teamTitle] = $totalWeighted;
        }

        $result = [];
        foreach ($teams as $title => $team) {
            $result[] = (object)[
                'title' => $title,
                'sprint_total' => isset($weightedSprintSizes[$title]) ? (float)number_format($weightedSprintSizes[$title], 2) : 0.0,
            ];
        }
        return $result;
    }

    public static function getSummarySprintPercentage($projectId, $date, $weightages, $lastFridayDate)
    {
        $summarySprint = self::getSummarySprintSize($projectId, $date, $weightages, $lastFridayDate);
        $totalSprintSize = self::getTotalSprintSizesByTeamandDate($projectId, $date, $lastFridayDate);

        $totalSprintMap = [];
        foreach ($totalSprintSize as $total) {
            $totalSprintMap[$total->title] = $total->total_alloted;
        }

        $summarySprintMap = [];
        foreach ($summarySprint as $summary) {
            $teamTitle = $summary->title;
            $sprintTotal = $summary->sprint_total;
            $summarySprintMap[$teamTitle] = $sprintTotal;
        }
        $result = [];
        foreach ($summarySprintMap as $teamTitle => $summaryTotal) {
            $totalAlloted = $totalSprintMap[$teamTitle] ?? 0;

            if ($totalAlloted > 0) {
                $summaryPercentage = ($summaryTotal / $totalAlloted) * 100;
            } else {
                $summaryPercentage = 0;
            }

            $result[] = [
                'title' => $teamTitle,
                'summary_percentage' => round($summaryPercentage, 1)
            ];
        }
        return $result;
    }

    public static function getStatusSprintSize($projectId, $date, $lastFridayDate, $status)
    {
        $teams = DB::table('teams')
            ->select('title')
            ->get()
            ->keyBy('title')
            ->toArray();

        // Fetch sprint totals for the given status
        $sprints = DB::table('sprints as s')
            ->select('t.title', DB::raw('SUM(ta.alloted_sprint) as sprint_total'))
            ->join('team_assignments as ta', 's.id', '=', 'ta.sprint_id')
            ->join('teams as t', 'ta.team_id', '=', 't.id')
            ->where('s.status_id', $status)
            ->whereDate('s.created_at', '<=', $date)
            ->whereDate('s.created_at', '>', $lastFridayDate)
            ->where('s.project_id', '=', $projectId)
            ->groupBy('t.title')
            ->get()
            ->keyBy('title')
            ->toArray();

        // Merge sprints with teams, defaulting to 0 if no sprint total is found
        $result = [];
        foreach ($teams as $title => $team) {
            $result[] = (object)[
                'title' => $title,
                'sprint_total' => isset($sprints[$title]) ? (float)$sprints[$title]->sprint_total : 0.0
            ];
        }
        return $result;
    }
}
