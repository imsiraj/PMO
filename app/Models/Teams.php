<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use SprintSummary;
use DefaultExcelColumns;

class Teams extends Model
{
    use HasFactory;

    protected $dates = ['deleted_at'];
    protected $table = 'teams';

    protected $fillable = [
        'title',
        'members',
        'project_id',
        // 'alloted_sprint',
        // 'status_id',
        'revision_number',
        'created_by',
        'created_at',
        'updated_at',
        'deleted_at',
        'is_tech',
        'header'
    ];
    protected $casts = [
        'is_tech' => 'boolean',
    ];

    public static function findTeamByTitle($title, $projectId)
    {
        return self::where('title', $title)->where('project_id', $projectId)->get()->toArray();
    }

    public static function findTeamByHeader($header, $projectId)
    {
        return self::where('header', $header)->where('project_id', $projectId)->get()->toArray();
    }

    public static function getAllTeams($projectId)
    {
        return self::select('title', 'id')->where('project_id', $projectId)->orderBy('id', 'ASC')->get()->toArray();
    }

    public static function updateIsTechById($teamId, $isTech)
    {
        return self::where('id', $teamId)->update(['is_tech' => $isTech]);
    }

    /** 
     * To get the Overall teams data
     * @param $projectId
     */
    public static function getOverAllTeamsData($projectId)
    {
        $statusUserAccepted = SprintSummary::USER_ACCEPTANCE;
        $teamSummary = DB::table('teams as t')
            ->join('team_assignments as ta', 't.id', '=', 'ta.team_id')
            ->join('sprints as s', 'ta.sprint_id', '=', 's.id')
            ->select(
                't.title as team',
                DB::raw("SUM(CASE WHEN s.status_id = '$statusUserAccepted' THEN ta.alloted_sprint ELSE 0 END) as `user_acceptance`"),
                DB::raw("SUM(CASE WHEN s.status_id != '$statusUserAccepted' THEN ta.alloted_sprint ELSE 0 END) as `development`")
            )
            ->where('t.project_id', $projectId)
            ->groupBy('t.id', 't.title')
            ->get();
        return $teamSummary;
    }

    /** 
     * To get the Overall teams data by Date
     * @param $projectId, date, startDate, sprintDurationDays
     */
    public static function getOverAllTeamsDataByDate($projectId, $date, $startDate, $sprintDurationDays)
    {
        $statusUserAccepted = SprintSummary::USER_ACCEPTANCE;
        $teamSummary = DB::table('teams as t')
            ->join('team_assignments as ta', 't.id', '=', 'ta.team_id')
            ->join('sprints as sp', 'ta.sprint_id', '=', 'sp.id')
            ->join(
                DB::raw("(SELECT MAX(id) as max_id, task_id FROM sprints WHERE DATE(created_at) <= '$date' AND project_id='$projectId' GROUP BY task_id) as spc"),
                'sp.id',
                '=',
                'spc.max_id'
            )
            ->select(
                't.title as team',
                't.is_tech',
                DB::raw("SUM(CASE WHEN sp.status_id = '$statusUserAccepted' THEN ta.alloted_sprint ELSE 0 END) as `user_acceptance`"),
                DB::raw("SUM(CASE WHEN sp.status_id != '$statusUserAccepted' THEN ta.alloted_sprint ELSE 0 END) as `development`"),
                DB::raw("
                CASE 
                    WHEN SUM(CASE WHEN sp.status_id = '$statusUserAccepted' THEN ta.alloted_sprint ELSE 0 END) > 0 
                    THEN DATE_ADD('$startDate', INTERVAL SUM(CASE WHEN sp.status_id = '$statusUserAccepted' THEN ta.alloted_sprint ELSE 0 END) * $sprintDurationDays DAY)
                    ELSE '$startDate'
                END as `user_acceptance_projection_date`
            "),
                DB::raw("
                CASE 
                    WHEN SUM(CASE WHEN sp.status_id != '$statusUserAccepted' THEN ta.alloted_sprint ELSE 0 END) > 0 
                    THEN DATE_ADD('$startDate', INTERVAL SUM(CASE WHEN sp.status_id != '$statusUserAccepted' THEN ta.alloted_sprint ELSE 0 END) * $sprintDurationDays DAY)
                    ELSE '$startDate'
                END as `development_projection_date`
            ")
            )
            ->where('t.project_id', $projectId)
            ->whereDate('sp.created_at', '<=', $date)
            ->orderBy('t.id', 'ASC')
            ->groupBy('t.id', 't.title', 't.is_tech')
            ->get()->toArray();
        return $teamSummary;
    }

    /** 
     * This function will help to calcualte increased and decreased column sprint based on date.
     * @param $projectId, $currentDate, $lastFriday, $startDate, $sprintDurationDays
     */
    public static function getComparisonData($projectId, $currentDate, $lastFriday, $startDate, $sprintDurationDays)
    {
        // Fetch current data
        $currentData = self::getOverAllTeamsDataByDate($projectId, $currentDate, $startDate, $sprintDurationDays);

        // Fetch last Friday's data
        $lastFridayData = self::getOverAllTeamsDataByDate($projectId, $lastFriday, $startDate, $sprintDurationDays);

        // Convert last Friday's data to an associative array for easy lookup
        $lastFridayDataAssoc = [];
        foreach ($lastFridayData as $data) {
            $lastFridayDataAssoc[$data->team] = $data;
        }

        // Compare the values
        $increasedColumns = [];
        $decreasedColumns = [];

        foreach ($currentData as $current) {
            $team = $current->team;
            if (isset($lastFridayDataAssoc[$team])) {
                $last = $lastFridayDataAssoc[$team];

                $userAcceptanceIncreased = $current->user_acceptance > $last->user_acceptance;
                $developmentIncreased = $current->development > $last->development;

                $userAcceptanceDecreased = $current->user_acceptance < $last->user_acceptance;
                $developmentDecreased = $current->development < $last->development;

                if ($userAcceptanceIncreased || $developmentIncreased) {
                    $increasedColumns[] = (object)[
                        'team' => $team,
                        'user_acceptance' => $current->user_acceptance,
                        'development' => $current->development,
                        'change' => 'increased'
                    ];
                }
                if ($userAcceptanceDecreased || $developmentDecreased) {
                    $decreasedColumns[] = (object)[
                        'team' => $team,
                        'user_acceptance' => $current->user_acceptance,
                        'development' => $current->development,
                        'change' => 'decreased'
                    ];
                }
            }
        }
        return [
            'increased_columns' => $increasedColumns,
            'decreased_columns' => $decreasedColumns,
        ];
    }


    /** 
     * This function will help to calcualte projectionDate.
     * @param $startDate, date, sprintDurationDays,
     */
    public static function calculateProjectionDate($startDate, $totalSprints, $sprintDurationDays)
    {
        $totalDays = ceil($totalSprints + ($sprintDurationDays * 2));
        return date('Y-m-d', strtotime($startDate . ' + ' . $totalDays . ' days'));
    }

    /** 
     * To get the Overall teams data by phases
     * @param $projectId, phaseId
     */
    public static function getAllTeamsDataByPhases($projectId, $phaseId)
    {
        $statusUserAccepted = SprintSummary::USER_ACCEPTANCE;
        $yes = DefaultExcelColumns::YES;
        $teamSummary = DB::table('teams as t')
            ->join('team_assignments as ta', 't.id', '=', 'ta.team_id')
            ->join('sprints as s', 'ta.sprint_id', '=', 's.id')
            ->join('phase_assignments as pa', 'pa.sprint_id', '=', 's.id')
            ->join('phases as p', 'pa.phase_id', '=', 'p.id')
            ->select(
                't.title as team',
                DB::raw("SUM(CASE WHEN s.status_id = '$statusUserAccepted' THEN ta.alloted_sprint ELSE 0 END) as `user_acceptance`"),
                DB::raw("SUM(CASE WHEN s.status_id != '$statusUserAccepted' THEN ta.alloted_sprint ELSE 0 END) as `development`")
            )
            ->where('t.project_id', $projectId) // Filter by project if needed
            ->where('pa.phase_id', $phaseId) // Filter by specific phase
            ->where('pa.phase_value', $yes) // Filter by phase value
            ->groupBy('t.id', 't.title')
            ->get()->toArray();
        return $teamSummary;
    }

    /** 
     * To get the teams count by project.
     * @param projectId, phaseId, date, startDate, sprintDurationDays,
     */
    public static function getTeamsCountByProject($projectId)
    {
        return self::where('project_id', $projectId)->count();
    }

    /** 
     * To get the Overall teams data by phases and date.
     * @param projectId, phaseId, date, startDate, sprintDurationDays,
     */
    public static function getOverAllTeamsDataByDateAndPhases($projectId, $phaseId, $date, $startDate, $sprintDurationDays)
    {
        $statusUserAccepted = SprintSummary::USER_ACCEPTANCE;
        $yes = DefaultExcelColumns::YES;
        $teamSummary = DB::table('teams as t')
            ->join('team_assignments as ta', 't.id', '=', 'ta.team_id')
            ->join('sprints as sp', 'ta.sprint_id', '=', 'sp.id')
            ->join(
                DB::raw("(SELECT MAX(id) as max_id, task_id FROM sprints WHERE DATE(created_at) <= '$date' AND project_id='$projectId' GROUP BY task_id) as spc"),
                'sp.id',
                '=',
                'spc.max_id'
            )
            ->join('phase_assignments as pa', 'pa.sprint_id', '=', 'sp.id')
            ->join('phases as p', 'pa.phase_id', '=', 'p.id')
            ->select(
                't.title as team',
                't.is_tech',
                DB::raw("SUM(CASE WHEN sp.status_id = '$statusUserAccepted' THEN ta.alloted_sprint ELSE 0 END) as `user_acceptance`"),
                DB::raw("SUM(CASE WHEN sp.status_id != '$statusUserAccepted' THEN ta.alloted_sprint ELSE 0 END) as `development`"),
                DB::raw("
                    CASE 
                        WHEN SUM(CASE WHEN sp.status_id = '$statusUserAccepted' THEN ta.alloted_sprint ELSE 0 END) > 0 
                        THEN DATE_ADD('$startDate', INTERVAL SUM(CASE WHEN sp.status_id = '$statusUserAccepted' THEN ta.alloted_sprint ELSE 0 END) * $sprintDurationDays DAY)
                        ELSE '$startDate'
                    END as `user_acceptance_projection_date`
                "),
                DB::raw("
                    CASE 
                        WHEN SUM(CASE WHEN sp.status_id != '$statusUserAccepted' THEN ta.alloted_sprint ELSE 0 END) > 0 
                        THEN DATE_ADD('$startDate', INTERVAL SUM(CASE WHEN sp.status_id != '$statusUserAccepted' THEN ta.alloted_sprint ELSE 0 END) * $sprintDurationDays DAY)
                        ELSE '$startDate'
                    END as `development_projection_date`
                ")
            )
            ->where('t.project_id', $projectId)  // Filter by project
            ->where('pa.phase_id', $phaseId)  // Filter by specific phase
            ->where('pa.phase_value', $yes)  // Filter by phase value
            ->whereDate('sp.created_at', '<=', $date)  // Date condition for sprint data
            ->orderBy('t.id', 'ASC')
            ->groupBy('t.id', 't.title', 't.is_tech')
            ->get();

        return $teamSummary;
    }
}
