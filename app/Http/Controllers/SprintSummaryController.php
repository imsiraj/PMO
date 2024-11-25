<?php

namespace App\Http\Controllers;

use SprintStatus;
use App\Models\Sprints;
use App\Models\Projects;
use Illuminate\Http\Request;
use ApiStatus, WeekDays;
use Illuminate\Support\Facades\Validator;
use App\Models\AuditLogs;

class SprintSummaryController extends Controller
{
    /**
     * Retrieves the sprint summary for a project, aggregated by team and status.
     * @param Request $request.
     */
    public function getTeamWiseStatusTotalSprintSummary(Request $request)
    {
        $params = $request->all();
        $validationRules = [
            'project_code' => 'required|exists:projects,code',
            'start_date' => 'required|date|date_format:d-m-Y',
            'end_date' => 'required|date|date_format:d-m-Y',
            'weights' => 'nullable|array',
            'weights.*' => 'numeric'
        ];
        $customMessages = [
            'project_code.required' => 'Project code field is required.',
            'project_code.exists' => 'Invalid Project code.',
            'start_date.date' => 'Start date field must be valid date.',
            'start_date.date_format' => 'Start date field must be in dd-mm-yyyy format.',
            'end_date.date' => 'End date field must be valid date.',
            'end_date.date_format' => 'End date field must be in dd-mm-yyyy format.',
            'weights.array' => 'Weights must be an array.',
            'weights.*.numeric' => 'Each weight must be a numeric value.',
        ];

        $validator = Validator::make($params, $validationRules, $customMessages);

        if ($validator->fails()) {
            $errorMessage = $validator->errors()->first();
            $data = [
                "error_message" => $errorMessage,
            ];
            return response()->json([
                'status' => ApiStatus::FAILURE,
                'data' => $data,
                'message' => ApiStatus::VALIDATION_ERROR,
                'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
            ], ApiStatus::API_STATUS_BAD_REQUEST);
        }
        $projectCode = $params['project_code'];
        $getProject = Projects::findProjectByCode($projectCode);
        $projectId = $getProject->id;

        # to get sprint-completed and week-completed-will be response
        $currentDate = date('Y-m-d');
        $projectSprintDays = $getProject->no_of_days_in_sprint;
        $sprintStartDate = $getProject->sprint_start_date;

        $daysPassed = calculateDaysPassed($sprintStartDate, $currentDate);
        $sprintStartDaysRatio = calculateSprintRatio($daysPassed, $projectSprintDays);

        $weeksPassed = calculateWeeksPassed($daysPassed);

        $searchForDate = "";
        if (isset($params['start_date']) && !empty($params['start_date']) && isset($params['end_date']) && !empty($params['end_date'])) {

            $currentDate = date('Y-m-d');
            $searchForDate = date('Y-m-d', strtotime($params['end_date']));
            $getFirstImportedOn = AuditLogs::getFirstImportedOn($projectId);
            if (empty($getFirstImportedOn)) {
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => ApiStatus::NO_IMPORT_HISTORY_FOUND,
                    'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                ], ApiStatus::API_STATUS_BAD_REQUEST);
            }
            $importedOn = date('Y-m-d', strtotime($getFirstImportedOn['created_at']));
            if ($searchForDate <= $importedOn) {
                $searchForDate = $importedOn;
                $previousDate = $importedOn;
            } else if ($searchForDate >= get_friday_date($currentDate)) {
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => ApiStatus::NEXT_FRIDAY_REQUEST,
                    'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                ], ApiStatus::API_STATUS_BAD_REQUEST);

                #searchForDate is after the current_date.
            } else if ($currentDate <= $searchForDate) {
                $timeStamp = strtotime(date('Y-m-d', strtotime($currentDate)));
                $weekDay = date('l', $timeStamp);
                if ($weekDay === WeekDays::MONDAY) {
                    $previousDate = date('Y-m-d', strtotime('-3 day', strtotime($currentDate)));
                } else {
                    $previousDate = $searchForDate;
                }

                # searchForDate is before the current_date.
            } else {
                $timeStamp = strtotime(date('Y-m-d', strtotime($searchForDate)));
                $weekDay = date('l', $timeStamp);
                if ($weekDay === WeekDays::MONDAY) {
                    $previousDate = date('Y-m-d', strtotime('-3 day', strtotime($searchForDate)));
                } else {
                    $previousDate = $searchForDate;
                }
            }
        }

        $weightsArray = $params['weights'] ?? [];

        $statusOrder = [
            SprintStatus::STATUS_COMPLETED,
            SprintStatus::STATUS_USER_ACCEPTANCE,
            SprintStatus::STATUS_TESTING_PHASES,
            SprintStatus::STATUS_IN_PROGRESS,
            SprintStatus::STATUS_PENDING,
            SprintStatus::STATUS_BLOCKED,
            SprintStatus::STATUS_REWORK,
        ];

        $defaultWeight = SprintStatus::DEFAULT_STATUS_WEIGHT;
        $weights = array_pad($weightsArray, count($statusOrder), $defaultWeight);
        $weightagesMapped = [];
        foreach ($statusOrder as $index => $status) {
            $weightagesMapped[$status] = $weights[$index];
        }
        $lastFridayDate = get_last_friday_date($previousDate);

        $sprintSize = Sprints::getTotalSprintSizesByTeamandDate($projectId, $previousDate, $lastFridayDate);
        $workingSprint = Sprints::getWorkingSprintSizeByTeamandDate($projectId, $previousDate, $lastFridayDate);
        $workingPercentage = Sprints::getWorkingPercentageByTeamandDate($projectId, $previousDate, $lastFridayDate);
        $summary = Sprints::getSummarySprintSize($projectId, $previousDate, $weightagesMapped, $lastFridayDate);
        $summaryPercentage = Sprints::getSummarySprintPercentage($projectId, $previousDate, $weightagesMapped, $lastFridayDate);

        //status wise sprints
        $completedStatusSprints = Sprints::getStatusSprintSize($projectId, $previousDate, $lastFridayDate, SprintStatus::STATUS_COMPLETED);
        $userAcceptanceStatusSprints = Sprints::getStatusSprintSize($projectId, $previousDate, $lastFridayDate, SprintStatus::STATUS_USER_ACCEPTANCE);
        $testingStatusSprints = Sprints::getStatusSprintSize($projectId, $previousDate, $lastFridayDate, SprintStatus::STATUS_TESTING_PHASES);
        $inProgressStatusSprints = Sprints::getStatusSprintSize($projectId, $previousDate, $lastFridayDate, SprintStatus::STATUS_IN_PROGRESS);
        $pendingStatusSprints = Sprints::getStatusSprintSize($projectId, $previousDate, $lastFridayDate, SprintStatus::STATUS_PENDING);
        $blockedStatusSprints = Sprints::getStatusSprintSize($projectId, $previousDate, $lastFridayDate, SprintStatus::STATUS_BLOCKED);
        $reworkStatusSprints = Sprints::getStatusSprintSize($projectId, $previousDate, $lastFridayDate, SprintStatus::STATUS_REWORK);
        $deniedStatusSprints = Sprints::getStatusSprintSize($projectId, $previousDate, $lastFridayDate, SprintStatus::STATUS_DENIED);
        $inconsistentStatusSprints = Sprints::getStatusSprintSize($projectId, $previousDate, $lastFridayDate, SprintStatus::STATUS_INCONSISTENT);
        $replacedStatusSprints = Sprints::getStatusSprintSize($projectId, $previousDate, $lastFridayDate, SprintStatus::STATUS_REPLACED);

        $workingPercentageMap = [];
        foreach ($workingPercentage as $wp) {
            $workingPercentageMap[$wp['title']] = $wp['working_percentage'];
        }

        $summaryPercentageMap = [];
        foreach ($summaryPercentage as $sp) {
            $summaryPercentageMap[$sp['title']] = $sp['summary_percentage'];
        }

        $completedSprintsMap = [];
        foreach ($completedStatusSprints as $cs) {
            $completedSprintsMap[$cs->title] = $cs->sprint_total;
        }

        $userAccSprintsMap = [];
        foreach ($userAcceptanceStatusSprints as $us) {
            $userAccSprintsMap[$us->title] = $us->sprint_total;
        }

        $testingPhaseSprintsMap = [];
        foreach ($testingStatusSprints as $ts) {
            $testingPhaseSprintsMap[$ts->title] = $ts->sprint_total;
        }

        $inProgressSprintsMap = [];
        foreach ($inProgressStatusSprints as $is) {
            $inProgressSprintsMap[$is->title] = $is->sprint_total;
        }

        $pendingSprintsMap = [];
        foreach ($pendingStatusSprints as $ps) {
            $pendingSprintsMap[$ps->title] = $ps->sprint_total;
        }

        $blockedSprintsMap = [];
        foreach ($blockedStatusSprints as $bs) {
            $blockedSprintsMap[$bs->title] = $bs->sprint_total;
        }

        $reworkSprintsMap = [];
        foreach ($reworkStatusSprints as $rs) {
            $reworkSprintsMap[$rs->title] = $rs->sprint_total;
        }

        $deniedSprintsMap = [];
        foreach ($deniedStatusSprints as $ds) {
            $deniedSprintsMap[$ds->title] = $ds->sprint_total;
        }

        $inconsistentSprintsMap = [];
        foreach ($inconsistentStatusSprints as $is) {
            $inconsistentSprintsMap[$is->title] = $is->sprint_total;
        }

        $replacedSprintsMap = [];
        foreach ($replacedStatusSprints as $res) {
            $replacedSprintsMap[$res->title] = $res->sprint_total;
        }

        $responseData = [];
        foreach ($sprintSize as $total) {
            $teamTitle = $total->title;
            $isTechStatus = $total->is_tech;
            $totalSize = $total->total_alloted;
            $workingTotal = 0;
            $summaryTotal = 0;
            $workPercentage = 0.0;
            $summaryPercentageValue = 0.0;
            $completedStatus = 0;
            $userAccStatus = 0;
            $testingphaseStatus = 0;
            $inProgressStatus = 0.0;
            $pendingStatus = 0.0;
            $blockedStatus = 0;
            $reworkStatus = 0;
            $deniedStatus = 0;
            $inconsistStatus = 0;
            $replacedStatus = 0;




            foreach ($workingSprint as $working) {
                if ($working->title == $teamTitle) {
                    $workingTotal = $working->sprint_total;
                    break;
                }
            }

            foreach ($summary as $sum) {
                if ($sum->title == $teamTitle) {
                    $summaryTotal = $sum->sprint_total;
                    break;
                }
            }

            if (isset($workingPercentageMap[$teamTitle])) {
                $workPercentage = $workingPercentageMap[$teamTitle];
            }

            if (isset($summaryPercentageMap[$teamTitle])) {
                $summaryPercentageValue = $summaryPercentageMap[$teamTitle];
            }

            if (isset($completedSprintsMap[$teamTitle])) {
                $completedStatus = $completedSprintsMap[$teamTitle];
            }

            if (isset($userAccSprintsMap[$teamTitle])) {
                $userAccStatus = $userAccSprintsMap[$teamTitle];
            }

            if (isset($testingPhaseSprintsMap[$teamTitle])) {
                $testingphaseStatus = $testingPhaseSprintsMap[$teamTitle];
            }

            if (isset($inProgressSprintsMap[$teamTitle])) {
                $inProgressStatus = $inProgressSprintsMap[$teamTitle];
            }

            if (isset($pendingSprintsMap[$teamTitle])) {
                $pendingStatus = $pendingSprintsMap[$teamTitle];
            }

            if (isset($blockedSprintsMap[$teamTitle])) {
                $blockedStatus = $blockedSprintsMap[$teamTitle];
            }

            if (isset($reworkSprintsMap[$teamTitle])) {
                $reworkStatus = $reworkSprintsMap[$teamTitle];
            }

            if (isset($deniedSprintsMap[$teamTitle])) {
                $deniedStatus = $deniedSprintsMap[$teamTitle];
            }

            if (isset($inconsistentSprintsMap[$teamTitle])) {
                $inconsistStatus = $inconsistentSprintsMap[$teamTitle];
            }

            if (isset($replacedSprintsMap[$teamTitle])) {
                $replacedStatus = $replacedSprintsMap[$teamTitle];
            }

            $responseData[] = [
                'team' => $teamTitle,
                'is_tech' => $isTechStatus,
                'total_size' => (float)format_number_two_decimals($totalSize),
                'working' => (float)format_number_two_decimals($workingTotal),
                'working_percentage' => $workPercentage,
                'summary' => (float)format_number_two_decimals($summaryTotal),
                'summary_percentage' => $summaryPercentageValue,
                'completed_status' => (float)format_number_two_decimals($completedStatus),
                'user_acceptance_status' => (float)format_number_two_decimals($userAccStatus),
                'testing_phase_status' => (float)format_number_two_decimals($testingphaseStatus),
                'in_progress_status' => (float)format_number_two_decimals($inProgressStatus),
                'pending_status' => (float)format_number_two_decimals($pendingStatus),
                'blocked_status' => (float)format_number_two_decimals($blockedStatus),
                'rework_status' => (float)format_number_two_decimals($reworkStatus),
                'denied_status' => (float)format_number_two_decimals($deniedStatus),
                'inconsistent_status' => (float)format_number_two_decimals($inconsistStatus),
                'replaced_status' => (float)format_number_two_decimals($replacedStatus)
            ];
        }
        $aggregated_data = self::aggregateData($responseData);
        return response()->json([
            'status' => ApiStatus::SUCCESS,
            'data' => [
                'sprint_completed' => $sprintStartDaysRatio,
                'weeks_completed' => $weeksPassed,
                "summary" => $responseData,
                "total" => $aggregated_data,
            ],
            'message' => ApiStatus::API_SUCCESS_MESSAGE,
            'status_code' => ApiStatus::API_STATUS_SUCCESS,
        ], ApiStatus::API_STATUS_SUCCESS);
    }

    /**
     * Aggregates technical and non-technical data from the provided response data.
     *
     * @param array $responseData Array of response data containing various metrics.
     * @return array An array containing aggregated data for tech, non-tech, and overall metrics.
     */
    public function aggregateData($responseData)
    {
        $techData = (object)[
            'team' => 'tech',
            'total_size' => 0,
            'working' => 0,
            'working_percentage' => 0,
            'summary' => 0,
            'summary_percentage' => 0,
            'completed_status' => 0,
            'user_acceptance_status' => 0,
            'testing_phase_status' => 0,
            'in_progress_status' => 0,
            'pending_status' => 0,
            'blocked_status' => 0,
            'rework_status' => 0,
            'denied_status' => 0,
            'inconsistent_status' => 0,
            'replaced_status' => 0,
            'count' => 0,
        ];

        $nonTechData = (object)[
            'team' => 'non-tech',
            'total_size' => 0,
            'working' => 0,
            'working_percentage' => 0,
            'summary' => 0,
            'summary_percentage' => 0,
            'completed_status' => 0,
            'user_acceptance_status' => 0,
            'testing_phase_status' => 0,
            'in_progress_status' => 0,
            'pending_status' => 0,
            'blocked_status' => 0,
            'rework_status' => 0,
            'denied_status' => 0,
            'inconsistent_status' => 0,
            'replaced_status' => 0,
            'count' => 0,
        ];

        foreach ($responseData as $data) {
            if ($data['is_tech'] == 1) {
                $this->updateData($techData, $data);
            } else {
                $this->updateData($nonTechData, $data);
            }
        }

        // Calculate average percentages after looping through all data
        $techData->summary_percentage = round($techData->count ? $techData->summary_percentage / $techData->count : 0, 2);
        $nonTechData->summary_percentage = round($nonTechData->count ? $nonTechData->summary_percentage / $nonTechData->count : 0, 2);
        $techData->working_percentage = round($techData->count ? $techData->working_percentage / $techData->count : 0, 2);
        $nonTechData->working_percentage = round($nonTechData->count ? $nonTechData->working_percentage / $nonTechData->count : 0, 2);

        $overall = (object)[
            'team' => 'all',
            'total_size' => round($techData->total_size + $nonTechData->total_size, 2),
            'working' => round($techData->working + $nonTechData->working, 2),
            'working_percentage' => round(($techData->working_percentage + $nonTechData->working_percentage) / 2, 2),
            'summary' => round($techData->summary + $nonTechData->summary, 2),
            'summary_percentage' => round(($techData->summary_percentage + $nonTechData->summary_percentage) / 2, 2),
            'completed_status' => round($techData->completed_status + $nonTechData->completed_status, 2),
            'user_acceptance_status' => round($techData->user_acceptance_status + $nonTechData->user_acceptance_status, 2),
            'testing_phase_status' => round($techData->testing_phase_status + $nonTechData->testing_phase_status, 2),
            'in_progress_status' => round($techData->in_progress_status + $nonTechData->in_progress_status, 2),
            'pending_status' => round($techData->pending_status + $nonTechData->pending_status, 2),
            'blocked_status' => round($techData->blocked_status + $nonTechData->blocked_status, 2),
            'rework_status' => round($techData->rework_status + $nonTechData->rework_status, 2),
            'denied_status' => round($techData->denied_status + $nonTechData->denied_status, 2),
            'inconsistent_status' => round($techData->inconsistent_status + $nonTechData->inconsistent_status, 2),
            'replaced_status' => round($techData->replaced_status + $nonTechData->replaced_status, 2),
        ];

        return [
            $techData,
            $nonTechData,
            $overall,
        ];
    }

    private function updateData($targetData, $data)
    {
        $targetData->total_size = round($targetData->total_size + ($data['total_size'] ?? 0), 2);
        $targetData->working = round($targetData->working + ($data['working'] ?? 0), 2);
        $targetData->working_percentage = round($targetData->working_percentage + ($data['working_percentage'] ?? 0), 2);
        $targetData->summary = round($targetData->summary + ($data['summary'] ?? 0), 2);
        $targetData->summary_percentage = round($targetData->summary_percentage + ($data['summary_percentage'] ?? 0), 2);
        $targetData->completed_status = round($targetData->completed_status + ($data['completed_status'] ?? 0), 2);
        $targetData->user_acceptance_status = round($targetData->user_acceptance_status + ($data['user_acceptance_status'] ?? 0), 2);
        $targetData->testing_phase_status = round($targetData->testing_phase_status + ($data['testing_phase_status'] ?? 0), 2);
        $targetData->in_progress_status = round($targetData->in_progress_status + ($data['in_progress_status'] ?? 0), 2);
        $targetData->pending_status = round($targetData->pending_status + ($data['pending_status'] ?? 0), 2);
        $targetData->blocked_status = round($targetData->blocked_status + ($data['blocked_status'] ?? 0), 2);
        $targetData->rework_status = round($targetData->rework_status + ($data['rework_status'] ?? 0), 2);
        $targetData->denied_status = round($targetData->denied_status + ($data['denied_status'] ?? 0), 2);
        $targetData->inconsistent_status = round($targetData->inconsistent_status + ($data['inconsistent_status'] ?? 0), 2);
        $targetData->replaced_status = round($targetData->replaced_status + ($data['replaced_status'] ?? 0), 2);
        $targetData->count++;
    }
}
