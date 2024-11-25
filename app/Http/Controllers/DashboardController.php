<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use App\Models\Sprints;
use App\Models\Teams;
use App\Models\AuditLogs;
use App\Models\Projects;
use Illuminate\Http\Request;
use ApiStatus;
use SprintStatus;
use Exception;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function getDashboardData(Request $request)
    {
        try {
            $params = $request->all();
            $validationRules = [
                'project_code' => 'required|exists:projects,code',
            ];
            $customMessages = [
                'project_code.required' => 'Project code field is required.',
                'project_code.exists' => 'Invalid Project code.',
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
            $projectSprintDays = $getProject->no_of_days_in_sprint;
            $sprintStartDate = $getProject->sprint_start_date;

            $currentDate = date('Y-m-d');
            $getFirstImportedOn = AuditLogs::getFirstImportedOn($projectId);
            if (empty($getFirstImportedOn)) {
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => ApiStatus::NO_IMPORT_HISTORY_FOUND,
                    'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                ], ApiStatus::API_STATUS_BAD_REQUEST);
            }

            $teamSummary = Teams::getOverAllTeamsDataByDate($projectId, $currentDate, $sprintStartDate, $projectSprintDays);
            $fridayDataProjectDate = [];
            $fridayDataSprintSize = [];
            $fridayDataCompletedSprintSize = [];
            $fridays = getAllFridays($sprintStartDate);
            foreach ($fridays as $friday) {
                $fridayProjection=['date'=>$friday];
                $sprintSizeData = ['date' => $friday];
                $completedSprintSizeData = ['date' => $friday];
                foreach ($teamSummary as $team) {
                    if ($team->is_tech == 1) {
                        $fridayProjection[$team->team]=strtotime($team->development_projection_date);
                        $sprintSizes = Sprints::getTotalSprintSizesByTeamandDate($projectId, $friday, get_last_friday_date($friday));
                        $completedSprintSizes=Sprints::getStatusSprintSize($projectId, $friday,get_last_friday_date($friday),SprintStatus::STATUS_COMPLETED);
                        foreach ($sprintSizes as $sprintSize) {
                            if ($sprintSize->title == $team->team) {
                                $sprintSizeData[$team->team]= round($sprintSize->total_alloted, 2);
                            }
                        }
                        foreach($completedSprintSizes as $completed){
                            if ($completed->title == $team->team) {
                                $completedSprintSizeData[$team->team]= round($completed->sprint_total, 2);
                            }
                        }
                    }
                }
                $fridayDataProjectDate[] = $fridayProjection;
                $fridayDataSprintSize[] = $sprintSizeData;
                $fridayDataCompletedSprintSize[] = $completedSprintSizeData;
            }

            return response()->json([
                'status' => ApiStatus::SUCCESS,
                'message' => ApiStatus::API_SUCCESS_MESSAGE,
                'data' => [
                    'projection_date' => $fridayDataProjectDate,
                    'sprint_size' => $fridayDataSprintSize,
                    'completed_sprint_size' => $fridayDataCompletedSprintSize,
                ],
                'status_code' => ApiStatus::API_STATUS_SUCCESS,
            ], ApiStatus::API_STATUS_SUCCESS);
        } catch (Exception $e) {
            Log::debug("Exception occured while getting get dashboard view API.");
            Log::debug($e);
            Log::debug("================END=====================");
            return response()->json([
                'status' => ApiStatus::FAILURE,
                'message' => ApiStatus::INTERNAL_ERROR,
                'status_code' => ApiStatus::API_STATUS_INTERNAL_SERVER_ERROR,
            ], ApiStatus::API_STATUS_INTERNAL_SERVER_ERROR);
        }
    }
}
