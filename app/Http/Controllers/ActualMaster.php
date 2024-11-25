<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Projects;
use App\Models\Sprints;
use App\Models\Phases;
use App\Models\Teams;
use App\Models\UserStories;
use App\Models\PhaseAssignments;
use App\Models\TeamAssignments;
use App\Models\UserStoriesAssignments;
use App\Models\ChangeLog;
use DefaultExcelColumns;
use ApiStatus;
use App\Models\AuditLogs;
use WeekDays;
use Exception;
use Illuminate\Support\Facades\Log;

class ActualMaster extends Controller
{
    public function __construct() {}

    /**
     * This will return the actual master data similar to the excelsheet.
     * 
     * @param project_id : To be able to return data based on project id
     * @return json : Returns the json with column titles with a key named columns where all the titles are present, and rows key where array of object containing the data is present.
     * 
     * @example { "data":{ "columns": ["ID","Sprint Item"], "rows": [ [1,'test1'],[2,'test2'] ] } }
     */
    public function getActualMasterData(Request $request)
    {

        try {

            $params = $request->all();
            $validationRules = [
                'project_code' => 'required|exists:projects,code',
                'start_date' => 'nullable|date|date_format:d-m-Y',
                'end_date' => 'nullable|date|date_format:d-m-Y',
            ];
            $customMessages = [
                'project_code.required' => 'Project code field is required.',
                'project_code.exists' => 'Invalid Project code.',
                'start_date.date' => 'Start date field must be valid date.',
                'start_date.date_format' => 'Start date field must be in dd-mm-yyyy format.',
                'end_date.date' => 'End date field must be valid date.',
                'end_date.date_format' => 'End date field must be in dd-mm-yyyy format.',
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
            $sprintDays=$getProject->no_of_days_in_sprint;
            //Getting all the columns titles
            $actualMasterColumns = DefaultExcelColumns::getAllKeys();
            $actualMasterData = [];
            $response['history'] = $baseData = $requestedDataFor = [];
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
                        $previousDate = date('Y-m-d', strtotime('-1 day', strtotime($currentDate)));
                    }
                    # searchForDate is before the current_date.
                } else {
                    $timeStamp = strtotime(date('Y-m-d', strtotime($searchForDate)));
                    $weekDay = date('l', $timeStamp);
                    if ($weekDay === WeekDays::MONDAY) {
                        $previousDate = date('Y-m-d', strtotime('-3 day', strtotime($searchForDate)));
                    } else {
                        $previousDate = date('Y-m-d', strtotime('-1 day', strtotime($searchForDate)));
                    }
                }
                $baseData = Sprints::getPreviousChangesByDate($projectId, $previousDate);
                if (empty($baseData)) {
                    $previousDate = date('Y-m-d', strtotime('-1 day', strtotime($previousDate)));
                    $timeStamp = strtotime(date('Y-m-d', strtotime($previousDate)));
                    $weekDay = date('l', $timeStamp);
                    if ($weekDay === WeekDays::MONDAY) {
                        $searchForDate = date('Y-m-d', strtotime('-3 days', strtotime($searchForDate)));
                    }
                    $previousDate = date('Y-m-d', strtotime('-1 day', strtotime($searchForDate)));
                    $baseData = Sprints::getPreviousChangesByDate($projectId, $previousDate);
                }
                //Check Sprints/tasks are empty.
                if (empty($baseData)) {
                    return response()->json([
                        'status' => ApiStatus::FAILURE,
                        'message' => ApiStatus::NO_SPRINT_FOUND,
                        'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                    ], ApiStatus::API_STATUS_BAD_REQUEST);
                }
                $requestedDataFor = ChangeLog::getLatestChangesByDate($projectId, $searchForDate, $previousDate);

                if ($requestedDataFor) {
                    $response['history'] = $requestedDataFor;
                }
            }

            $phaseCount = $phaseStartIndex = $teamCount = $teamsStartIndex = $userStoryCount = $userStoryStartIndex = 0;

            //getting all dynamically generated phase titles based on project
            $getPhasesList = Phases::getAllPhases($projectId);
            if (!empty($getPhasesList)) {
                $phaseCount = count($getPhasesList);
                $phaseTitles = array_column($getPhasesList, 'title');
                $phaseTitles = $this->padAssociativeArrayWithTitles($phaseTitles);

                //Adding phase titles after the specific column i.e mobile completion status
                $actualMasterColumnsKeys = array_column($actualMasterColumns, "key");
                $index = array_search(DefaultExcelColumns::MOBILE_COMPLETION_STATUS, $actualMasterColumnsKeys);
                $phaseStartIndex = $index + 1;

                //Adding phases title into existing column titles
                array_splice($actualMasterColumns, $phaseStartIndex, 0, $phaseTitles);
            }

            //getting all dynamically generated teams titles based on project.
            $getTeamsList = Teams::getAllTeams($projectId);
            if (!empty($getTeamsList)) {
                $teamCount = count($getTeamsList);
                $teamTitles = array_column($getTeamsList, 'title');
                $teamTitles = $this->padAssociativeArrayWithTitles($teamTitles, "group_");

                //Adding team titles after the specific column i.e serial number
                $actualMasterColumnsKeys = array_column($actualMasterColumns, "key");
                $index = array_search(DefaultExcelColumns::SERIAL_NUMBER, $actualMasterColumnsKeys);
                $teamsStartIndex = $index + 1;

                //Adding team title into existing column titles
                array_splice($actualMasterColumns, $teamsStartIndex, 0, $teamTitles);
            }

            //getting all dynamically generated user stories based on project
            $getUserStoryList = UserStories::getAllStories($projectId);
            if (!empty($getUserStoryList)) {
                $userStoryCount = count($getUserStoryList);
                $userStoryTitles = array_column($getUserStoryList, 'title');
                $userStoryTitles = $this->padAssociativeArrayWithTitles($userStoryTitles);

                //Adding user stories title after the specific columns i.e member size
                $actualMasterColumnsKeys = array_column($actualMasterColumns, "key");
                $index = array_search(DefaultExcelColumns::MEMBER_SIZE, $actualMasterColumnsKeys);
                $userStoryStartIndex = $index + 1;

                //Adding user story title into existing column titles
                array_splice($actualMasterColumns, $userStoryStartIndex, 0, $userStoryTitles);
            }

            //Looping through all the sprint/tasks
            foreach ($baseData as $key => $value) {
                $rowData[$key] = [
                    'updated_columns' => [],
                    DefaultExcelColumns::ID => $value->task_id,
                    DefaultExcelColumns::SPRINT_ITEM => $value->title,
                    DefaultExcelColumns::COMPLEXITY => $value->complexity,
                    DefaultExcelColumns::RESOURCES => $value->resources,
                    DefaultExcelColumns::SPRINT_ESTIMATION => $value->sprint_estimation,
                    DefaultExcelColumns::SPRINT_ADJUSTMENTS => $value->sprint_adjustment,
                    DefaultExcelColumns::SPRINT_SIZE => $value->sprint_size,
                    DefaultExcelColumns::SPRINT_SIZE_ADHOC_TASK => $value->sprint_adhoc_task,
                    DefaultExcelColumns::FINAL_SPRINT_SIZE => $value->sprint_total,
                    DefaultExcelColumns::SPRINT_COMMENT => $value->comments,
                    DefaultExcelColumns::STATUS => $value->status_id,
                    DefaultExcelColumns::MOBILE_COMPLETION_STATUS => $value->mobile_completion_status,
                    DefaultExcelColumns::ALL_PROJECT => $value->all_project,
                    DefaultExcelColumns::SERIAL_NUMBER => $value->serial_number,
                    DefaultExcelColumns::NEW_SR_NO => $value->new_sr_no,
                    DefaultExcelColumns::NEW_MODULE_SCREENS => $value->new_module_screens,
                    DefaultExcelColumns::SPRINT_NUMBER => $value->sprint_number,
                    DefaultExcelColumns::TEAM_NAME => $value->team_name,
                    DefaultExcelColumns::MODULE_SCREENS => $value->module_screens,
                    DefaultExcelColumns::REQUIREMENT => $value->requirement,
                    DefaultExcelColumns::ACCESS_REQUIRED_FOR_USER_ROLE => $value->access_required_for_role,
                    DefaultExcelColumns::PRIORITY => $value->priority_id,
                    DefaultExcelColumns::EXTRA_COLUMN_1 => $value->extra_column_one,
                    DefaultExcelColumns::EXTRA_COLUMN_2 => $value->extra_column_two,
                    DefaultExcelColumns::WEB_BACKEND => $value->web_backend,
                    DefaultExcelColumns::PRIORITY_BY_STAKEHOLDER => $value->priority_id_stakeholder,
                    DefaultExcelColumns::PRIORITY_BS => $value->priority_bs,
                    DefaultExcelColumns::RESPONSIBLE_OWNER => $value->responsible_owner,
                    DefaultExcelColumns::RESPONSIBLE_TEAM => $value->responsible_team,
                    DefaultExcelColumns::OWNER_OR_TEAM_COMMENTS => $value->owner_or_team_comments,
                    DefaultExcelColumns::COMMENTS_POST_DISCUSSION_WITH_STAKEHOLDER => $value->comments_post_discussion_with_stakeholder,
                    DefaultExcelColumns::DERIVED_PRIORITY => $value->derived_priority,
                    DefaultExcelColumns::MEMBER_SIZE => $value->member_size,
                    DefaultExcelColumns::IN_APP_COMMUNICATIONS => $value->in_app_communications,
                    DefaultExcelColumns::CHANGES_DONE_IN_ACTUAL_MASTER => $value->changes_done_in_actual_master,
                    DefaultExcelColumns::CHANGE_TYPE => $value->change_type,
                    DefaultExcelColumns::DESCRIPTION_OF_CHANGES => $value->description_of_changes,
                    DefaultExcelColumns::CHANGE_DONE_ON_DATE => $value->change_done_on_date,
                    DefaultExcelColumns::WHERE_THESE_TASKS_HAVE_BEEN_ADDED => $value->where_these_tasks_have_been_added,
                    DefaultExcelColumns::EXTRA_COLUMN_3 => $value->extra_column_three,
                    DefaultExcelColumns::EXTRA_COLUMN_4 => $value->extra_column_four,
                    DefaultExcelColumns::INCOMPLETE_BASIS_WEIGHTAGES => $value->incomplete_basis_weightages,
                    DefaultExcelColumns::COMPLETED_BASIS_ACTUAL_PROGRESS => $value->completed_basis_actual_progress,
                    DefaultExcelColumns::COMPLETE_STATUS_PERCENTAGE => $value->complete_status_percentage,
                    DefaultExcelColumns::INCOMPLETE_STATUS_PERCENTAGE => $value->incomplete_status_percentage,
                    DefaultExcelColumns::INCOMPLETE_BASIS_ACTUAL => $value->incomplete_basis_actual,
                    DefaultExcelColumns::INCOMPLETE_SPRINT_SIZE_BASIS_WEIGHTAGE => $value->incomplete_sprint_size_basis_weightage,
                    DefaultExcelColumns::FEATURE => $value->feature,
                    DefaultExcelColumns::DEMO_GIVEN_YES_OR_NO => $value->demo_given,
                    DefaultExcelColumns::DEMO_GIVEN_ON_YYYYMMDD => $value->demo_given_on,
                    DefaultExcelColumns::APPROVED_BY => $value->approved_by,
                    DefaultExcelColumns::APPROVED_ON_YYYYMMDD => $value->approved_on,
                    DefaultExcelColumns::FEEDBACK_BY_STSAKEHOLDER => $value->feedback_by_stakeholder,
                    DefaultExcelColumns::STAKEHOLDER_COMMENTS => $value->stakeholder_comments,
                    DefaultExcelColumns::SCREENSHOT_USER_MANUAL_LINK_1 => $value->screenshot_link_one,
                    DefaultExcelColumns::SCREENSHOT_USER_MANUAL_LINK_2 => $value->screenshot_link_two,
                    DefaultExcelColumns::SCREENSHOT_USER_MANUAL_LINK_3 => $value->screenshot_link_three,
                    DefaultExcelColumns::SCREENSHOT_USER_MANUAL_LINK_4 => $value->screenshot_link_four,
                    DefaultExcelColumns::SCREENSHOT_USER_MANUAL_LINK_5 => $value->screenshot_link_five,
                    DefaultExcelColumns::USER_ACCEPTANCE_DEMO_STATUS => $value->uat_demo_status,
                    DefaultExcelColumns::WEB_LOGIN_URL_CREDENTIALS => $value->web_login_details,
                ];

                //If phase count is >=1 then add their respective data.
                if ($phaseCount) {
                    //Getting phase data based on project and sprint id
                    $getphaseData = PhaseAssignments::getAllPhasesByProjectAndSprint($projectId, $value->id);
                    $phaseValues = array_column($getphaseData, 'phase_value');
                    //Checking if there phase count does not match the count of phase values, we will append the empty string to match the length
                    $phaseValues = $this->padAssociativeArrayWithPattern($phaseValues, $phaseCount, DefaultExcelColumns::SEARCH_TEXT_PAHSE);

                    // array_splice($rowData,$phaseStartIndex,0, $phaseValues);
                    $keyToInsertAfter = DefaultExcelColumns::MOBILE_COMPLETION_STATUS;

                    // Split the array into two parts and merge with the new data
                    $position = array_search($keyToInsertAfter, array_keys($rowData[$key])) + 1;
                    $firstPart = array_slice($rowData[$key], 0, $position, true);
                    $secondPart = array_slice($rowData[$key], $position, null, true);

                    $rowData[$key] = array_merge($firstPart, $phaseValues, $secondPart);
                }

                //If team count is >=1 then add their respective data.
                if ($teamCount) {
                    //Getting teams data based on project and sprint id
                    $getTeamsData = TeamAssignments::getAllTeamsByProjectAndSprint($projectId, $value->id);
                    $teamsValues = array_column($getTeamsData, 'alloted_sprint');
                    //Checking if there team count does not match the count of team values, we will append the string 0(zero) to match the length
                    $teamsValues = $this->padAssociativeArrayWithPattern($teamsValues, $teamCount, DefaultExcelColumns::SEARCH_TEXT_TEAM);

                    // array_splice($rowData,$phaseStartIndex,0, $phaseValues);
                    $keyToInsertTeamAfter = DefaultExcelColumns::SERIAL_NUMBER;

                    // Split the array into two parts and merge with the new data
                    $position = array_search($keyToInsertTeamAfter, array_keys($rowData[$key])) + 1;
                    $firstPart = array_slice($rowData[$key], 0, $position, true);
                    $secondPart = array_slice($rowData[$key], $position, null, true);

                    $rowData[$key] = array_merge($firstPart, $teamsValues, $secondPart);
                    // array_splice($rowData,$teamsStartIndex,0, $teamsValues);
                }

                //If user story count is >=1 then add their respective data.
                if ($userStoryCount) {
                    //Getting user stories data based on project and sprint id
                    $getUserStoryData = UserStoriesAssignments::getAllStoriesByProjectAndSprint($projectId, $value->id);
                    $storyValues = array_column($getUserStoryData, 'story_data');
                    //Checking if there story count does not match the count of story values, we will append the empty string to match the length
                    $storyValues = $this->padAssociativeArrayWithPattern($storyValues, $userStoryCount, DefaultExcelColumns::SEARCH_TEXT_USER_STORIES);

                    // array_splice($rowData,$userStoryStartIndex,0, $storyValues);

                    // array_splice($rowData,$phaseStartIndex,0, $phaseValues);
                    $keyToInsertStoryAfter = DefaultExcelColumns::MEMBER_SIZE;
                    // Split the array into two parts and merge with the new data
                    $position = array_search($keyToInsertStoryAfter, array_keys($rowData[$key])) + 1;
                    $firstPart = array_slice($rowData[$key], 0, $position, true);
                    $secondPart = array_slice($rowData[$key], $position, null, true);

                    $rowData[$key] = array_merge($firstPart, $storyValues, $secondPart);
                }

                $actualMasterData[] = $rowData[$key];
            }

            $replaceCount = 0;

            if (!empty($requestedDataFor)) {

                $taskIds = array_column($actualMasterData, DefaultExcelColumns::ID);
                foreach ($requestedDataFor as $item) {

                    // Find the index in the second array where task_id matches ids from the first array
                    $index = array_search($item['task_id'], $taskIds);

                    if ($index !== false) {

                        // Replace the value in the second array if the keys match
                        if (array_key_exists($item['column'], $actualMasterData[$index])) {
                            $actualMasterData[$index][$item['column']] = $item['value'];
                            $actualMasterData[$index]['updated_columns'][] = $item['column'];
                            $replaceCount++;
                        }
                    }
                }
            }

            $response['sprint_days']=$sprintDays;
            $response['master_columns'] = $actualMasterColumns;
            $response['master_data'] = $actualMasterData;

            return response()->json([
                'status' => ApiStatus::SUCCESS,
                'message' => ApiStatus::API_SUCCESS_MESSAGE,
                'data' => $response,
                'status_code' => ApiStatus::API_STATUS_SUCCESS,
            ], ApiStatus::API_STATUS_SUCCESS);
        } catch (Exception $e) {
            Log::debug("Exception occured while getting actual master API.");
            Log::debug($e);
            Log::debug("================END=====================");
            return response()->json([
                'status' => ApiStatus::FAILURE,
                'message' => ApiStatus::INTERNAL_ERROR,
                'status_code' => ApiStatus::API_STATUS_INTERNAL_SERVER_ERROR,
            ], ApiStatus::API_STATUS_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * This function will help to create the array with given string prefix and by filling in the data from array.
     * The new array can be created by the desired length.
     * 
     * @param arrayData : The array which contains the n numbers of data
     * @param desiredLength : The length of the new result array 
     * @param keyPrefix : The prefix with which we will create new array key
     * @return array 
     */
    public function padAssociativeArrayWithPattern($arrayData, $desiredLength, $keyPrefix)
    {
        $newKeys = [];

        for ($i = 0; $i < $desiredLength; $i++) {
            $key = $keyPrefix . ($i + 1);
            if (isset($arrayData[$i])) {
                $newKeys[$key] = $arrayData[$i];
            } else {

                $newKeys[$key] = "";
            }
        }

        return $newKeys;
    }

    /**
     * This function will help to create the array by adding new associtive key as key
     * 
     * @param arrayData : The array which contains the n numbers of data
     * @param keyPrefix : The prefix with which we will use it as defaul value
     * @return array 
     */
    public function padAssociativeArrayWithTitles($arrayData, $prefix = null)
    {
        $currentCount = count($arrayData);
        $newKeys = [];

        for ($i = 0; $i < $currentCount; $i++) {
            $newKeys[$i]['title'] = $arrayData[$i];
            if (isset($prefix)) {
                $newKeys[$i]['key'] = str_replace(['-', ' '], '_', trim(strtolower($prefix))) . ($i + 1);
            } else {
                $newKeys[$i]['key'] = str_replace(['-', ' '], '_', trim(strtolower($arrayData[$i])));
            }
        }

        return $newKeys;
    }

    /**
     * Retrieves a list of phases based on project code and optional phase title search.
     * 
     * This function validates the input parameters, fetches project details by code,
     * retrieves phases based on project ID and optional title search, and returns a JSON response
     * 
     * @param project_code : To get the phases for given project code
     * @param phase_title : To get the phases similar to the given title
     * @return json response containg id and title for phases along with standard response keys.
     */
    public function getPhasesList(Request $request)
    {

        try {
            $params = $request->all();
            $validationRules = [
                'project_code' => 'required|exists:projects,code',
                'phase_title' => 'nullable|regex:/^[a-zA-Z0-9\s]*$/',
            ];
            $customMessages = [
                'project_code.required' => 'Project code field is required.',
                'project_code.exists' => 'Invalid Project code.',
                'phase_title.regex' => 'Phase title field can have only characters and numbers.',
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

            $phaseTitle = $params['phase_title'] ?? "";

            $getPhases = Phases::getPhasesByTitle($projectId, $phaseTitle);
            if (empty($getPhases)) {
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => ApiStatus::NO_PHASES_FOUND,
                    'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                ], ApiStatus::API_STATUS_BAD_REQUEST);
            } else {
                return response()->json([
                    'status' => ApiStatus::SUCCESS,
                    'data' => $getPhases,
                    'message' => ApiStatus::API_SUCCESS_MESSAGE,
                    'status_code' => ApiStatus::API_STATUS_SUCCESS,
                ], ApiStatus::API_STATUS_SUCCESS);
            }
        } catch (Exception $e) {
            Log::debug("Exception occured while getting pahse list API.");
            Log::debug($e);
            Log::debug("================END=====================");
            return response()->json([
                'status' => ApiStatus::FAILURE,
                'message' => ApiStatus::INTERNAL_ERROR,
                'status_code' => ApiStatus::API_STATUS_INTERNAL_SERVER_ERROR,
            ], ApiStatus::API_STATUS_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * To get the summary of teams based on phases and overall and generate the projection date.
     * 
     * @param project_code : To get the data for given project code
     * @param phases_list : To get the data based on given phase ids
     * @param start_date : To get the phases for given start date
     * @param end_date : To get the phases for given end date
     * 
     * @return json : The json response with the each team sprint and its projection date.
     */
    public function getTeamWiseProjectionDateSummary(Request $request)
    {
        try {

            $params = $request->all();
            $validationRules = [
                'project_code' => 'required|exists:projects,code',
                'phases_list' => 'nullable|array',
                'phases_list.*' => 'required|integer|distinct',
                'start_date' => 'required|date|date_format:d-m-Y',
                'end_date' => 'required|date|date_format:d-m-Y',
            ];
            $customMessages = [
                'project_code.required' => 'Project code field is required.',
                'project_code.exists' => 'Invalid Project code.',
                'phases_list.array' => 'Phases list field must be an array of ids',
                'phases_list.*.integer' => 'Values in phases field must be numbers.',
                'phases_list.*.distinct' => 'Values in phases field must be unique.',
                'start_date.date' => 'Start date field must be valid date.',
                'start_date.date_format' => 'Start date field must be in dd-mm-yyyy format.',
                'end_date.date' => 'End date field must be valid date.',
                'end_date.date_format' => 'End date field must be in dd-mm-yyyy format.',
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
            if (!isset($projectSprintDays)) {
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => ApiStatus::NO_SPRINT_DAYS_DATA_FOUND,
                    'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                ], ApiStatus::API_STATUS_BAD_REQUEST);
            }
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
                        $previousDate = date('Y-m-d', strtotime('-1 day', strtotime($currentDate)));
                    }
                    # searchForDate is before the current_date.
                } else {
                    $timeStamp = strtotime(date('Y-m-d', strtotime($searchForDate)));
                    $weekDay = date('l', $timeStamp);
                    if ($weekDay === WeekDays::MONDAY) {
                        $previousDate = date('Y-m-d', strtotime('-3 day', strtotime($searchForDate)));
                    } else {
                        $previousDate = date('Y-m-d', strtotime('-1 day', strtotime($searchForDate)));
                    }
                }
            }
            $weekDate = date('Y-m-d', strtotime($params['end_date']));
            $lastFriday = get_last_friday_date($weekDate);
            $getOverAllData = $getOverAllDataByPhases = [];

            $teamSummary = Teams::getOverAllTeamsDataByDate($projectId, $previousDate, $weekDate, $projectSprintDays);
            $total = self::getUserAcceptionDevelopmentSumByIstech($teamSummary, $previousDate, $projectSprintDays);
            $comparisonData = Teams::getComparisonData($projectId, $previousDate, $lastFriday, $weekDate, $projectSprintDays);

            $increasedColumns = $comparisonData['increased_columns'];
            $decreasedColumns = $comparisonData['decreased_columns'];
            $getOverAllData = [
                "Overall" => $teamSummary,
                "Total" => $total,
                "increasedColumns" => $increasedColumns,
                "decreasedColumns" => $decreasedColumns,
            ];
            if (isset($params['phases_list']) && count($params['phases_list']) >= 1) {
                foreach ($params['phases_list'] as $phaseId) {
                    $overallByPhases = Teams::getOverAllTeamsDataByDateAndPhases($projectId, $phaseId, $previousDate, $weekDate, $projectSprintDays);
                    $totalPhases = self::getUserAcceptanceDevelopmentSumByPhase($overallByPhases, $previousDate, $projectSprintDays);
                    $getPhaseData = [
                        "OverallByPhases" => $overallByPhases,
                        "TotalPhases" => $totalPhases,
                    ];
                    $getOverAllDataByPhases[$phaseId] = $getPhaseData;
                }
            }
            $response['sprint_days']=$projectSprintDays;
            $response['overall_summary'] = $getOverAllData;
            $response['phases_summary'] = $getOverAllDataByPhases;
            if (!empty($getOverAllData)) {
                return response()->json([
                    'status' => ApiStatus::SUCCESS,
                    'data' => $response,
                    'message' => ApiStatus::API_SUCCESS_MESSAGE,
                    'status_code' => ApiStatus::API_STATUS_SUCCESS,
                ], ApiStatus::API_STATUS_SUCCESS);
            } else {
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => ApiStatus::INTERNAL_ERROR,
                    'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                ], ApiStatus::API_STATUS_SUCCESS);
            }
        } catch (Exception $e) {
            Log::debug("Exception occured while getting Team-Wise-Projection-Date-Summary API.");
            Log::debug($e);
            Log::debug("================END=====================");
            return response()->json([
                'status' => ApiStatus::FAILURE,
                'message' => ApiStatus::INTERNAL_ERROR,
                'status_code' => ApiStatus::API_STATUS_INTERNAL_SERVER_ERROR,
            ], ApiStatus::API_STATUS_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * This function will help to create the array by adding new associtive key as key
     * 
     * @param arrayData : The array which contains the n numbers of data
     * @param keyPrefix : The prefix with which we will use it as defaul value
     * @return array 
     */
    public function padArrayWithTitles($totalLength, $prefix = null)
    {
        $titles = [];
        for ($i = 0; $i < $totalLength; $i++) {
            $titles[] = trim(strtolower($prefix) . ($i + 1));
        }
        return $titles;
    }

    /** 
     * To get the User Acceptance and development sum by is_tech category.
     * @param $projectId, date, startDate, projectSprintDays,
     */
    public static function getUserAcceptionDevelopmentSumByIstech($teamSummary, $startDate, $projectSprintDays)
    {
        // Initialize totals
        $techTotals = (object)[
            'team' => 'tech',
            'total_user_acceptance' => 0,
            'total_development' => 0,
            'user_acceptance_projection_date' => $startDate,
            'development_projection_date' => $startDate,
        ];

        $nonTechTotals = (object)[
            'team' => 'non_tech',
            'total_user_acceptance' => 0,
            'total_development' => 0,
            'user_acceptance_projection_date' => $startDate,
            'development_projection_date' => $startDate,
        ];

        $allTotals = (object)[
            'team' => 'all',
            'total_user_acceptance' => 0,
            'total_development' => 0,
            'user_acceptance_projection_date' => $startDate,
            'development_projection_date' => $startDate,
        ];

        foreach ($teamSummary as $item) {
            if ($item->is_tech) {
                $techTotals->total_user_acceptance += $item->user_acceptance;
                $techTotals->total_development += $item->development;
                $techTotals->development_projection_date = Teams::calculateProjectionDate($startDate, $techTotals->total_development, $projectSprintDays);
                $techTotals->user_acceptance_projection_date = Teams::calculateProjectionDate($startDate, $techTotals->total_user_acceptance, $projectSprintDays);
            } else {
                $nonTechTotals->total_user_acceptance += $item->user_acceptance;
                $nonTechTotals->total_development += $item->development;
                $nonTechTotals->development_projection_date = Teams::calculateProjectionDate($startDate, $nonTechTotals->total_development, $projectSprintDays);
                $nonTechTotals->user_acceptance_projection_date = Teams::calculateProjectionDate($startDate, $nonTechTotals->total_user_acceptance, $projectSprintDays);
            }
            $allTotals->total_user_acceptance += $item->user_acceptance;
            $allTotals->total_development += $item->development;
            $allTotals->user_acceptance_projection_date = Teams::calculateProjectionDate($startDate, $allTotals->total_user_acceptance, $projectSprintDays);
            $allTotals->development_projection_date = Teams::calculateProjectionDate($startDate, $allTotals->total_development, $projectSprintDays);
        }

        return [$techTotals, $nonTechTotals, $allTotals];
    }


    /** 
     * To get the User Acceptance and development sum by phase
     * @param projectId, phaseId, date, startDate, projectSprintDays
     */
    public function getUserAcceptanceDevelopmentSumByPhase($overallphases, $startDate, $projectSprintDays)
    {
        // Initialize totals
        $techTotals = (object)[
            'team' => 'tech',
            'total_user_acceptance' => 0,
            'total_development' => 0,
            'user_acceptance_projection_date' => $startDate,
            'development_projection_date' => $startDate,
        ];
        $nonTechTotals = (object)[
            'team' => 'non_tech',
            'total_user_acceptance' => 0,
            'total_development' => 0,
            'user_acceptance_projection_date' => $startDate,
            'development_projection_date' => $startDate,
        ];

        $allTotals = (object)[
            'team' => 'all',
            'total_user_acceptance' => 0,
            'total_development' => 0,
            'user_acceptance_projection_date' => $startDate,
            'development_projection_date' => $startDate,
        ];

        foreach ($overallphases as $item) {
            if ($item->is_tech) {
                $techTotals->total_user_acceptance += $item->user_acceptance;
                $techTotals->total_development += $item->development;
                $techTotals->development_projection_date = Teams::calculateProjectionDate($startDate, $techTotals->total_development, $projectSprintDays);
                $techTotals->user_acceptance_projection_date = Teams::calculateProjectionDate($startDate, $techTotals->total_user_acceptance, $projectSprintDays);
            } else {
                $nonTechTotals->total_user_acceptance += $item->user_acceptance;
                $nonTechTotals->total_development += $item->development;
                $nonTechTotals->development_projection_date = Teams::calculateProjectionDate($startDate, $nonTechTotals->total_development, $projectSprintDays);
                $nonTechTotals->user_acceptance_projection_date = Teams::calculateProjectionDate($startDate, $nonTechTotals->total_user_acceptance, $projectSprintDays);
            }
            $allTotals->total_user_acceptance += $item->user_acceptance;
            $allTotals->total_development += $item->development;
            $allTotals->user_acceptance_projection_date = Teams::calculateProjectionDate($startDate, $allTotals->total_user_acceptance, $projectSprintDays);
            $allTotals->development_projection_date = Teams::calculateProjectionDate($startDate, $allTotals->total_development, $projectSprintDays);
        }

        $allTotals->total_development = round($allTotals->total_development, 2);
        return [$techTotals, $nonTechTotals, $allTotals];
    }
}
