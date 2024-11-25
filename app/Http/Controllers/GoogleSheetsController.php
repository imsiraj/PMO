<?php

namespace App\Http\Controllers;

use App\Services\GoogleSheetsService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Phases;
use App\Models\Projects;
use App\Models\Sprints;
use App\Models\Teams;
use App\Models\PhaseAssignments;
use App\Models\TeamAssignments;
use App\Models\UserStories;
use App\Models\UserStoriesAssignments;
use App\Models\SprintData;
use App\Models\ChangeLog;
use App\Services\LoggingService;
use Exception;
use DefaultExcelColumns;
use ApiStatus;
use App\Models\AuditLogs;
use DateTime;
use Users;

class GoogleSheetsController extends Controller
{

    protected $sheetsService;
    protected $loggingService;

    public function __construct(GoogleSheetsService $sheetsService, LoggingService $loggingService)
    {
        $this->sheetsService = $sheetsService;
        $this->loggingService = $loggingService;
    }

    /**
     * This will fetch the actual master data from the excelsheet added to project.
     * Get the project data from DB, extract the sheet ID from the link.
     * Sets the sheet name and range to config and then starts reading data.
     * 
     * @param project_id : To be able to get sheet details based on project id and read data from that
     * 
     */
    public function readSheet(Request $request)
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
            $userId = Users::SUPER_ADMIN;
            $this->readActualMasterProjectSheet($projectCode, $userId);
        } catch (Exception $e) {
            Log::error("Excpetion occured while reading sheet data");
            Log::error($e);
            Log::debug("================END=====================");
            return response()->json([
                'status' => ApiStatus::FAILURE,
                'message' => ApiStatus::INTERNAL_ERROR,
                'status_code' => ApiStatus::API_STATUS_INTERNAL_SERVER_ERROR,
            ], ApiStatus::API_STATUS_INTERNAL_SERVER_ERROR);
        }
    }

    private function processRow($keys, $data, $revisionId, $userId, $readableTitles, $pahsesIndexes, $teamsIndexes, $userStoryIndexes, $teamDetail)
    {

        try {
            DB::beginTransaction();
            $sprintTaskId = validate_row_data($data, $keys,  DefaultExcelColumns::ID);
            $sprintTitle = validate_row_data($data, $keys, DefaultExcelColumns::SPRINT_ITEM);
            $complexity = validate_row_data($data, $keys, DefaultExcelColumns::COMPLEXITY);
            $resources = validate_row_data($data, $keys, DefaultExcelColumns::RESOURCES);

            $sprintEstimation = validate_row_data($data, $keys, DefaultExcelColumns::SPRINT_ESTIMATION);
            $sprintTimeAdjustment = validate_row_data($data, $keys, DefaultExcelColumns::SPRINT_ADJUSTMENTS);
            $sprintSizeAdhoc = validate_row_data($data, $keys, DefaultExcelColumns::SPRINT_SIZE_ADHOC_TASK);
            $sprintSize = validate_row_data($data, $keys, DefaultExcelColumns::SPRINT_SIZE);
            $sprintTotalTime = validate_row_data($data, $keys, DefaultExcelColumns::FINAL_SPRINT_SIZE);
            $sprintComment = validate_row_data($data, $keys, DefaultExcelColumns::SPRINT_COMMENT);
            $priorityTitle = validate_row_data($data, $keys,  DefaultExcelColumns::PRIORITY);
            $statusTitle = validate_row_data($data, $keys,  DefaultExcelColumns::STATUS);
            $mobileCompletionStatus = validate_row_data($data, $keys,  DefaultExcelColumns::MOBILE_COMPLETION_STATUS);
            $sprintNumber = validate_row_data($data, $keys,  DefaultExcelColumns::SPRINT_NUMBER);
            $sprintRequirement = validate_row_data($data, $keys,  DefaultExcelColumns::REQUIREMENT);
            $priorityTitleForStakeHolder = validate_row_data($data, $keys,  DefaultExcelColumns::PRIORITY_BY_STAKEHOLDER);


            $newSrNo = validate_row_data($data, $keys,  DefaultExcelColumns::NEW_SR_NO);
            $newModuleScreens = validate_row_data($data, $keys,  DefaultExcelColumns::NEW_MODULE_SCREENS);
            $teamName = validate_row_data($data, $keys,  DefaultExcelColumns::TEAM_NAME);
            $moduleScreens = validate_row_data($data, $keys,  DefaultExcelColumns::MODULE_SCREENS);
            $accessRequiredForUserRole = validate_row_data($data, $keys,  DefaultExcelColumns::ACCESS_REQUIRED_FOR_USER_ROLE);
            $extraColumn1 = validate_row_data($data, $keys,  DefaultExcelColumns::EXTRA_COLUMN_1);
            $extraColumn2 = validate_row_data($data, $keys,  DefaultExcelColumns::EXTRA_COLUMN_2);
            $webBackend = validate_row_data($data, $keys,  DefaultExcelColumns::WEB_BACKEND);
            $priorityBs = validate_row_data($data, $keys,  DefaultExcelColumns::PRIORITY_BS);
            $responsibleOwner = validate_row_data($data, $keys,  DefaultExcelColumns::RESPONSIBLE_OWNER);
            $responsibleTeam = validate_row_data($data, $keys,  DefaultExcelColumns::RESPONSIBLE_TEAM);
            $ownerOrTeamComments = validate_row_data($data, $keys,  DefaultExcelColumns::OWNER_OR_TEAM_COMMENTS);
            $commentsPostDiscussionWithStakeholder = validate_row_data($data, $keys,  DefaultExcelColumns::COMMENTS_POST_DISCUSSION_WITH_STAKEHOLDER);
            $derivedPriority = validate_row_data($data, $keys,  DefaultExcelColumns::DERIVED_PRIORITY);
            $memberSize = validate_row_data($data, $keys,  DefaultExcelColumns::MEMBER_SIZE);
            $inAppCommunications = validate_row_data($data, $keys,  DefaultExcelColumns::IN_APP_COMMUNICATIONS);
            $changesDoneInActualMaster = validate_row_data($data, $keys,  DefaultExcelColumns::CHANGES_DONE_IN_ACTUAL_MASTER);
            $changeType = validate_row_data($data, $keys,  DefaultExcelColumns::CHANGE_TYPE);
            $descriptionOfChanges = validate_row_data($data, $keys,  DefaultExcelColumns::DESCRIPTION_OF_CHANGES);
            $changeDoneOnDate = validate_row_data($data, $keys,  DefaultExcelColumns::CHANGE_DONE_ON_DATE);
            $whereTheseTasksHaveBeenAdded = validate_row_data($data, $keys,  DefaultExcelColumns::WHERE_THESE_TASKS_HAVE_BEEN_ADDED);
            $extraColumn3 = validate_row_data($data, $keys,  DefaultExcelColumns::EXTRA_COLUMN_3);
            $extraColumn4 = validate_row_data($data, $keys,  DefaultExcelColumns::EXTRA_COLUMN_4);
            $incompleteBasisWeightages = validate_row_data($data, $keys,  DefaultExcelColumns::INCOMPLETE_BASIS_WEIGHTAGES);
            $completedBasisActualProgress = validate_row_data($data, $keys,  DefaultExcelColumns::COMPLETED_BASIS_ACTUAL_PROGRESS);
            $completeStatusPercentage = validate_row_data($data, $keys,  DefaultExcelColumns::COMPLETE_STATUS_PERCENTAGE);
            $incompleteStatusPercentage = validate_row_data($data, $keys,  DefaultExcelColumns::INCOMPLETE_STATUS_PERCENTAGE);
            $incompleteBasisActual = validate_row_data($data, $keys,  DefaultExcelColumns::INCOMPLETE_BASIS_ACTUAL);
            $incompleteSprintSizeBasisWeightage = validate_row_data($data, $keys,  DefaultExcelColumns::INCOMPLETE_SPRINT_SIZE_BASIS_WEIGHTAGE);
            $feature = validate_row_data($data, $keys,  DefaultExcelColumns::FEATURE);
            $demoGivenYesOrNo = validate_row_data($data, $keys,  DefaultExcelColumns::DEMO_GIVEN_YES_OR_NO);
            $demoGivenOn = validate_row_data($data, $keys,  DefaultExcelColumns::DEMO_GIVEN_ON_YYYYMMDD);
            $approvedBy = validate_row_data($data, $keys,  DefaultExcelColumns::APPROVED_BY);
            $approvedOn = validate_row_data($data, $keys,  DefaultExcelColumns::APPROVED_ON_YYYYMMDD);
            $feedbackByStsakeholder = validate_row_data($data, $keys,  DefaultExcelColumns::FEEDBACK_BY_STSAKEHOLDER);
            $stakeholderComments = validate_row_data($data, $keys,  DefaultExcelColumns::STAKEHOLDER_COMMENTS);
            $screenshotUserManualLink1 = validate_row_data($data, $keys,  DefaultExcelColumns::SCREENSHOT_USER_MANUAL_LINK_1);
            $screenshotUserManualLink2 = validate_row_data($data, $keys,  DefaultExcelColumns::SCREENSHOT_USER_MANUAL_LINK_2);
            $screenshotUserManualLink3 = validate_row_data($data, $keys,  DefaultExcelColumns::SCREENSHOT_USER_MANUAL_LINK_3);
            $screenshotUserManualLink4 = validate_row_data($data, $keys,  DefaultExcelColumns::SCREENSHOT_USER_MANUAL_LINK_4);
            $screenshotUserManualLink5 = validate_row_data($data, $keys,  DefaultExcelColumns::SCREENSHOT_USER_MANUAL_LINK_5);
            $userAcceptanceDemoStatus = validate_row_data($data, $keys,  DefaultExcelColumns::USER_ACCEPTANCE_DEMO_STATUS);
            $webLoginUrlCredential = validate_row_data($data, $keys,  DefaultExcelColumns::WEB_LOGIN_URL_CREDENTIALS);
            $allProject = validate_row_data($data, $keys,  DefaultExcelColumns::ALL_PROJECT);
            $serialNumber = validate_row_data($data, $keys,  DefaultExcelColumns::SERIAL_NUMBER);



            $projectCode = explode('T', $sprintTaskId)[0];
            $getProject = Projects::findProjectByCode($projectCode);
            if (empty($getProject)) {
                $project = Projects::create(['code' => $projectCode, 'revision_number' => $revisionId]);
                $projectId = $project->id;
                $this->loggingService->logChange($project, 'created', $revisionId, $projectId, $userId);
            } else {
                $projectId = $getProject->id;
            }


            // $statusTitle = $statusTitle ? $statusTitle :"N/A";
            // $getStatus = GlobalStatus::findStatusByTitle($statusTitle);
            // if( empty($getStatus) ){
            //     $status = GlobalStatus::create(['title'=>$statusTitle,'revision_number'=>$revisionId]);
            //     $statusId = $status->id;
            //     $this->loggingService->logChange($status, 'created', $revisionId, $projectId, $userId);
            // }else{
            //     $statusId = $getStatus[0]['id'];
            // }

            // $priorityTitleForStakeHolder = $priorityTitleForStakeHolder ? $priorityTitleForStakeHolder :"N/A";
            // $getStakeHolderPriority = Priority::findPriorityByTitle($priorityTitleForStakeHolder);
            // if( empty($getStakeHolderPriority) ){
            //     $priorityStakeHolder = Priority::create(['title'=>$priorityTitleForStakeHolder,'revision_number'=>$revisionId]);
            //     $statusIdStakeHolders = $priorityStakeHolder->id;
            //     $this->loggingService->logChange($priorityStakeHolder, 'created', $revisionId, $projectId, $userId);
            // }else{
            //     $statusIdStakeHolders = $getStakeHolderPriority[0]['id'];
            // }


            // $priorityTitle = $priorityTitle ? $priorityTitle :"N/A";
            // $getPriority = Priority::findPriorityByTitle($priorityTitle);
            // if( empty($getPriority) ){
            //     $priority = Priority::create(['title'=>$priorityTitle,'revision_number'=>$revisionId]);
            //     $priorityId = $priority->id;
            //     $this->loggingService->logChange($priority, 'created', $revisionId, $projectId, $userId);
            // }else{
            //     $priorityId = $getPriority[0]['id'];
            // }

            $getSprint = Sprints::findSprintByTask($sprintTaskId);
            $sprintRowData = [
                'task_id' => $sprintTaskId,
                'project_id' => $projectId,
                'title' => $sprintTitle,
                'complexity' => ($complexity) ? $complexity : DefaultExcelColumns::DEFAULT_COMPLEXITY,
                'resources' => $resources,
                'sprint_estimation' => $sprintEstimation,
                'sprint_adjustment' => $sprintTimeAdjustment,
                'sprint_size' => $sprintSize,
                'sprint_adhoc_task' => $sprintSizeAdhoc,
                'sprint_total' => $sprintTotalTime,
                'status_id' => $statusTitle,
                'priority_id' => $priorityTitle,
                'priority_id_stakeholder' => $priorityTitleForStakeHolder,
                'comments' => $sprintComment,
                'mobile_completion_status' => $mobileCompletionStatus,
                'sprint_number' => $sprintNumber,
                'requirement' => $sprintRequirement,
            ];

            if (empty($getSprint)) {
                $sprintRowData['revision_number'] = $revisionId;
                $sprint = Sprints::create($sprintRowData);
                $sprintId = $sprint->id;
                $this->loggingService->logChange($sprint, 'created', $revisionId, $projectId, $userId);
            } else {
                $sprintId = $getSprint->id;
                // $changedColumns = [];
                // foreach ($sprintRowData as $key => $value) {
                //     $column = Str::snake($key);

                //     if ($getSprint->$column !== $value) {
                //         $changedColumns[$column] = $value;
                //     }
                // }
                // if (!empty($changedColumns)) {
                //     $changedColumns['revision_number'] = $revisionId;
                //     $originalAttributes = $getSprint->getOriginal();
                //     $getSprint->fill($changedColumns);
                //     $this->loggingService->logChange($getSprint, 'updated', $revisionId, $projectId, $userId,$originalAttributes);
                //     $getSprint->save();
                // }
            }

            foreach ($pahsesIndexes as $index) {
                $pahseValue = replace_newline($data[$index] ?? "");
                $pahseTitle = replace_newline($readableTitles[$index]);
                if (!empty($pahseTitle)) {
                    $getPhase = Phases::findPhaseByTitle($pahseTitle, $projectId);
                    if (empty($getPhase)) {
                        $phase = Phases::create(['title' => $pahseTitle, 'revision_number' => $revisionId, 'project_id' => $projectId]); //,'sprint_id'=>$sprintId
                        $phaseId = $phase->id;
                        $this->loggingService->logChange($phase, 'created', $revisionId, $projectId, $userId);
                    } else {
                        $phaseId = $getPhase[0]['id'];
                    }

                    $pahseAssignmentRow = [
                        'project_id' => $projectId,
                        'phase_id' => $phaseId,
                        'sprint_id' => $sprintId,
                        'phase_value' => $pahseValue
                    ];
                    $getPhaseAssignment = PhaseAssignments::findAssignment($projectId, $phaseId, $sprintId);
                    if (empty($getPhaseAssignment)) {
                        $pahseAssignmentRow['revision_number'] = $revisionId;
                        $pahseAssignment = PhaseAssignments::create($pahseAssignmentRow);
                        $this->loggingService->logChange($pahseAssignment, 'created', $revisionId, $projectId, $userId);
                    } else {
                        // $changedColumns = [];
                        // foreach ($pahseAssignmentRow as $key => $value) {
                        //     $column = Str::snake($key);

                        //     if ($getPhaseAssignment->$column !== $value) {
                        //         $changedColumns[$column] = $value;
                        //     }
                        // }
                        // if (!empty($changedColumns)) {
                        //     $changedColumns['revision_number'] = $revisionId;
                        //     $originalAttributes = $getPhaseAssignment->getOriginal();
                        //     $getPhaseAssignment->fill($changedColumns);
                        //     $this->loggingService->logChange($getPhaseAssignment, 'updated', $revisionId, $projectId, $userId,$originalAttributes);
                        //     $getPhaseAssignment->save();
                        // }
                    }
                }
            }
            foreach ($teamsIndexes as $index) {
                $teamValue = replace_newline($data[$index] ?? "");
                $newIsTech = (int) $teamDetail[$index];
                $teamTitle = replace_newline($readableTitles[$index]);
                $header = $keys[$index];
                if ($teamTitle === 'NA' || empty($teamTitle)) {
                    continue;
                }
                if (!empty($teamTitle)) {
                    $getTeam = Teams::findTeamByHeader($header, $projectId);
                    if (empty($getTeam)) {
                        $team = Teams::create(['title' => $teamTitle, 'header' => $header, 'project_id' => $projectId, 'revision_number' => $revisionId, 'is_tech' => $newIsTech]);
                        $this->loggingService->logChange($team, 'created', $revisionId, $projectId, $userId);
                        $teamId = $team->id;
                        $processedTeamTitles[] = $teamTitle;
                    } else {
                        $teamId = $getTeam[0]['id'];

                        $existingTeam = Teams::find($teamId);
                        if ($existingTeam) {
                            $currentIsTech = (int) ($existingTeam->is_tech ?? 0);
                            if ($currentIsTech !== $newIsTech) {
                                $existingTeam->is_tech = $newIsTech;
                                $existingTeam->save();
                                $this->loggingService->logChange($existingTeam, 'updated', $revisionId, $projectId, $userId);
                            }
                        }
                        if ($header == $existingTeam->header && $existingTeam->title !== $teamTitle) {
                            $existingTeam->title = $teamTitle; // Update the title
                            $existingTeam->save(); // Save changes to the database
                            $this->loggingService->logChange($existingTeam, 'updated', $revisionId, $projectId, $userId);
                        }
                    }
                    $teamAssignmentRow = [
                        'project_id' => $projectId,
                        'sprint_id' => $sprintId,
                        'team_id' => $teamId,
                        'alloted_sprint' => $teamValue
                    ];
                    $getTeamAssignment = TeamAssignments::findAssignment($projectId, $sprintId, $teamId);
                    if (empty($getTeamAssignment)) {
                        $teamAssignmentRow['revision_number'] = $revisionId;

                        $teamAssignment = TeamAssignments::create($teamAssignmentRow);
                        $this->loggingService->logChange($teamAssignment, 'created', $revisionId, $projectId, $userId);
                    } else {
                        // $changedColumns = [];
                        // foreach ($teamAssignmentRow as $key => $value) {
                        //     $column = Str::snake($key);

                        //     if ($getTeamAssignment->$column !== $value) {
                        //         $changedColumns[$column] = $value;
                        //     }
                        // }
                        // if (!empty($changedColumns)) {
                        //     $changedColumns['revision_number'] = $revisionId;
                        //     $originalAttributes = $getTeamAssignment->getOriginal();
                        //     $getTeamAssignment->fill($changedColumns);
                        //     $this->loggingService->logChange($getTeamAssignment, 'updated', $revisionId, $projectId, $userId,$originalAttributes);
                        //     $getTeamAssignment->save();
                        // }
                    }
                }
            }

            foreach ($userStoryIndexes as $index) {
                $storyData = replace_newline($data[$index] ?? "");
                $storyTitle = replace_newline($readableTitles[$index]);
                if (!empty($storyTitle)) {
                    $getStory = UserStories::findStoryByTitle($storyTitle, $projectId);
                    if (empty($getStory)) {
                        $story = UserStories::create(['title' => $storyTitle, 'project_id' => $projectId, 'revision_number' => $revisionId]);
                        $this->loggingService->logChange($story, 'created', $revisionId, $projectId, $userId);
                        $storyId = $story->id;
                    } else {
                        $storyId = $getStory[0]['id'];
                    }
                    $storyAssignmentRow = [
                        'project_id' => $projectId,
                        'sprint_id' => $sprintId,
                        'user_story_id' => $storyId,
                        'story_data' => $storyData
                    ];
                    $getStoryAssignment = UserStoriesAssignments::findAssignment($projectId, $sprintId, $storyId);
                    if (empty($getStoryAssignment)) {
                        $storyAssignmentRow['revision_number'] = $revisionId;
                        $storyAssignment = UserStoriesAssignments::create($storyAssignmentRow);
                        $this->loggingService->logChange($storyAssignment, 'created', $revisionId, $projectId, $userId);
                    } else {

                        // $changedColumns = [];
                        // foreach ($storyAssignmentRow as $key => $value) {
                        //     $column = Str::snake($key);

                        //     if ($getStoryAssignment->$column !== $value) {
                        //         $changedColumns[$column] = $value;
                        //     }
                        // }
                        // if (!empty($changedColumns)) {
                        //     $changedColumns['revision_number'] = $revisionId;
                        //     $originalAttributes = $getStoryAssignment->getOriginal();
                        //     $getStoryAssignment->fill($changedColumns);
                        //     $this->loggingService->logChange($getStoryAssignment, 'updated', $revisionId, $projectId, $userId,$originalAttributes);
                        //     $getStoryAssignment->save();
                        // }
                    }
                }
            }

            $sprintDataRow = [
                'sprint_id' => $sprintId,
                'new_sr_no' => $newSrNo,
                'new_module_screens' => $newModuleScreens,
                'team_name' => $teamName,
                'module_screens' => $moduleScreens,
                'access_required_for_role' => $accessRequiredForUserRole,
                'extra_column_one' => $extraColumn1,
                'extra_column_two' => $extraColumn2,
                'web_backend' => $webBackend,
                'priority_bs' => $priorityBs,
                'responsible_owner' => $responsibleOwner,
                'responsible_team' => $responsibleTeam,
                'owner_or_team_comments' => $ownerOrTeamComments,
                'comments_post_discussion_with_stakeholder' => $commentsPostDiscussionWithStakeholder,
                'derived_priority' => $derivedPriority,
                'member_size' => $memberSize,
                'in_app_communications' => $inAppCommunications,
                'changes_done_in_actual_master' => $changesDoneInActualMaster,
                'change_type' => $changeType,
                'description_of_changes' => $descriptionOfChanges,
                'change_done_on_date' => $changeDoneOnDate,
                'where_these_tasks_have_been_added' => $whereTheseTasksHaveBeenAdded,
                'extra_column_three' => $extraColumn3,
                'extra_column_four' => $extraColumn4,
                'incomplete_basis_weightages' => $incompleteBasisWeightages,
                'completed_basis_actual_progress' => $completedBasisActualProgress,
                'complete_status_percentage' => $completeStatusPercentage,
                'incomplete_status_percentage' => $incompleteStatusPercentage,
                'incomplete_basis_actual' => $incompleteBasisActual,
                'incomplete_sprint_size_basis_weightage' => $incompleteSprintSizeBasisWeightage,
                'feature' => $feature,
                'demo_given' => $demoGivenYesOrNo,
                'demo_given_on' => ($demoGivenOn) ? $demoGivenOn : NULL,
                'approved_by' => $approvedBy,
                'approved_on' => $approvedOn,
                'feedback_by_stakeholder' => $feedbackByStsakeholder,
                'stakeholder_comments' => $stakeholderComments,
                'screenshot_link_one' => $screenshotUserManualLink1,
                'screenshot_link_two' => $screenshotUserManualLink2,
                'screenshot_link_three' => $screenshotUserManualLink3,
                'screenshot_link_four' => $screenshotUserManualLink4,
                'screenshot_link_five' => $screenshotUserManualLink5,
                'uat_demo_status' => $userAcceptanceDemoStatus,
                'web_login_details' => $webLoginUrlCredential,
                'all_project' => $allProject,
                'serial_number' => $serialNumber,
            ];

            $getSprintData = SprintData::findDataBySprint($sprintId);
            if (empty($getSprintData)) {
                $sprintDataRow['revision_number'] = $revisionId;
                $sprintData = SprintData::create($sprintDataRow);
                $this->loggingService->logChange($sprintData, 'created', $revisionId, $projectId, $userId);
            } else {
                // $changedColumns = [];
                // foreach ($sprintDataRow as $key => $value) {
                //     $column = Str::snake($key);

                //     if ($getSprintData->$column !== $value) {
                //         $changedColumns[$column] = $value;
                //     }
                // }
                // if (!empty($changedColumns)) {
                //     $changedColumns['revision_number'] = $revisionId;
                //     $originalAttributes = $getSprintData->getOriginal();
                //     $getSprintData->fill($changedColumns);
                //     $this->loggingService->logChange($getSprintData, 'updated', $revisionId, $projectId, $userId,$originalAttributes);
                //     $getSprintData->save();
                // }
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Excpetion occured while processing data row");
            Log::error($e);
            Log::debug("================END=====================");
        }
    }

    function findCustomIndexes(array $list, string $customString): array
    {
        $phaseIndexes = [];
        foreach ($list as $index => $string) {
            if (str_starts_with($string, $customString)) {
                $phaseIndexes[] = $index;
            }
        }
        return $phaseIndexes;
    }

    /**
     * To get the updated data from google sheet, into the DB based on only new sheet as Changed logs.
     * 
     * @param project_code : to read from specific project sheets.
     */
    public function readChangeLogsData(Request $request)
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
            $revisionId = time();
            $userId = Users::SUPER_ADMIN;

            $projectCode = $params['project_code'];
            $getProjectDetails = Projects::findProjectByCode($projectCode);
            if (empty($getProjectDetails->sheet_link) || empty($getProjectDetails->sheet_name) || empty($getProjectDetails->sheet_range)) {
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => ApiStatus::GOOGLE_SHEET_DATA_NOT_CONFIGURED,
                    'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                ], ApiStatus::API_STATUS_BAD_REQUEST);
            }
            $projectId = $getProjectDetails->id;
            $spreadsheetId = "";
            $pattern = '/\/d\/([a-zA-Z0-9-_]+)\//';

            if (preg_match($pattern, $getProjectDetails->sheet_link, $matches)) {
                $spreadsheetId = $matches[1];
            }

            if (empty($spreadsheetId)) {
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => ApiStatus::GOOGLE_SHEET_DATA_NOT_CONFIGURED,
                    'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                ], ApiStatus::API_STATUS_BAD_REQUEST);
            }

            $currentDate = date('Y-m-d');
            $getLastRow = ChangeLog::getLastRowNumber($projectId, $currentDate);
            if (empty($getLastRow)) {
                $startRowNumber = DefaultExcelColumns::DEFAULT_ROW_NUMBER;
            } else {
                $startRowNumber = $getLastRow->row_number + 1;
            }
            $dbDetails = DefaultExcelColumns::getDatabaseDetail();
            $actualMasterColumnsKeys = array_column($dbDetails, "key");
            $range = "Change log" . "!A{$startRowNumber}:H";
            $data = $this->sheetsService->getSheetData($spreadsheetId, $range);
            if (empty($data)) {
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => ApiStatus::GOOGLE_SHEET_DATA_READ_NO_CHANGE_LOG_DATA,
                    'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                ], ApiStatus::API_STATUS_BAD_REQUEST);
            }
            DB::beginTransaction();
            foreach ($data as $key => $row) {
                $isUpdated = false;

                $date = replace_newline($row[0]);
                $dateTimeObject = DateTime::createFromFormat('d/m/Y H:i:s', $date);
                if (!$dateTimeObject) {
                    return response()->json([
                        'status' => ApiStatus::FAILURE,
                        'message' => ApiStatus::GOOGLE_SHEET_DATA_READ_CHANGE_LOG_ERROR_DATE,
                        'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                    ], ApiStatus::API_STATUS_BAD_REQUEST);
                }
                $rowModifiedAt = $dateTimeObject->format('Y-m-d H:i:s');

                if (isset($row[4]) && !empty($row[5])) {

                    $rowNewData = replace_newline($row[3]);
                    $rowSprintCode = replace_newline($row[4]);
                    $rowColumnName = replace_newline($row[5]);
                    $rowTitle = isset($row[6]) ? replace_newline($row[6]) : "";

                    $getSprint = Sprints::findSprintByTask($rowSprintCode);
                    if (!empty($getSprint)) {
                        $sprintId = $getSprint->id;

                        if (str_contains($rowColumnName, DefaultExcelColumns::SEARCH_TEXT_PAHSE) && !empty($rowTitle)) {
                            $getPhase = Phases::findPhaseByTitle($rowTitle, $projectId);
                            if (!empty($getPhase)) {
                                $phaseId = $getPhase[0]['id'];
                                $getPhaseAssignment = PhaseAssignments::findAssignment($projectId, $phaseId, $sprintId);
                                if ($getPhaseAssignment->getOriginal()) {

                                    $changedColumnsPhase['phase_value'] = $rowNewData;
                                    $changedColumnsPhase['revision_number'] = $revisionId;

                                    $originalAttributes = $getPhaseAssignment->getOriginal();
                                    $getPhaseAssignment->fill($changedColumnsPhase);
                                    $this->loggingService->logChange($getPhaseAssignment, 'updated', $revisionId, $projectId, $userId, $originalAttributes);
                                    $getPhaseAssignment->save();
                                    $isUpdated = true;
                                }
                            }
                        } else if (str_contains($rowColumnName, DefaultExcelColumns::SEARCH_TEXT_TEAM) && !empty($rowTitle)) {
                            $getTeam = Teams::findTeamByTitle($rowTitle, $projectId);
                            if (!empty($getTeam)) {
                                $teamId = $getTeam[0]['id'];
                                $getTeamAssignment = TeamAssignments::findAssignment($projectId, $sprintId, $teamId);
                                if ($getTeamAssignment->getOriginal()) {

                                    $changedColumnsTeam['alloted_sprint'] = $rowNewData;
                                    $changedColumnsTeam['revision_number'] = $revisionId;

                                    $originalAttributes = $getTeamAssignment->getOriginal();
                                    $getTeamAssignment->fill($changedColumnsTeam);
                                    $this->loggingService->logChange($getTeamAssignment, 'updated', $revisionId, $projectId, $userId, $originalAttributes);
                                    $getTeamAssignment->save();
                                    $isUpdated = true;
                                }
                            }
                        } else if (str_contains($rowColumnName, DefaultExcelColumns::SEARCH_TEXT_USER_STORIES) && !empty($rowTitle)) {
                            $getStory = UserStories::findStoryByTitle($rowTitle, $projectId);
                            if (!empty($getStory)) {
                                $storyId = $getStory[0]['id'];
                                $getStoryAssignment = UserStoriesAssignments::findAssignment($projectId, $sprintId, $storyId);
                                if ($getStoryAssignment->getOriginal) {


                                    $changedColumnsTeamStories['story_data'] = $rowNewData;
                                    $changedColumnsTeamStories['revision_number'] = $revisionId;

                                    $originalAttributes = $getStoryAssignment->getOriginal();
                                    $getStoryAssignment->fill($changedColumnsTeamStories);
                                    $this->loggingService->logChange($getStoryAssignment, 'updated', $revisionId, $projectId, $userId, $originalAttributes);
                                    $getStoryAssignment->save();
                                    $isUpdated = true;
                                }
                            }
                        } else {
                            $index = array_search($rowColumnName, $actualMasterColumnsKeys);
                            if ($index !== false) {
                                $table = $dbDetails[$index]['table'];
                                $dbColumn = Str::snake($dbDetails[$index]['column']);
                                if ($table == DefaultExcelColumns::TABLE_SPRINTS) {
                                    if ($getSprint->getOriginal()) {

                                        $changedColumnSprint[$dbColumn] = $rowNewData;
                                        $changedColumnSprint['revision_number'] = $revisionId;

                                        $originalAttributes = $getSprint->getOriginal();
                                        $getSprint->fill($changedColumnSprint);
                                        $this->loggingService->logChange($getSprint, 'updated', $revisionId, $projectId, $userId, $originalAttributes);
                                        $getSprint->save();
                                        $isUpdated = true;
                                    }
                                } else if ($table == DefaultExcelColumns::TABLE_SPRINT_DATA) {
                                    $getSprintData = SprintData::findDataBySprint($sprintId);
                                    if ($getSprintData->getOriginal()) {

                                        $changedColumnSprintData[$dbColumn] = $rowNewData;
                                        $changedColumnSprintData['revision_number'] = $revisionId;

                                        $originalAttributes = $getSprintData->getOriginal();
                                        $getSprintData->fill($changedColumnSprintData);
                                        $this->loggingService->logChange($getSprintData, 'updated', $revisionId, $projectId, $userId, $originalAttributes);
                                        $getSprintData->save();
                                        $isUpdated = true;
                                    }
                                }
                            }
                        }
                    }
                }

                $createChangeLog = ChangeLog::create([
                    'project_id' => $projectId,
                    'revision_number' => $revisionId,
                    'sprint_id' => replace_newline($row[4]),
                    'column_name' => replace_newline($row[5]),
                    'before_data' => replace_newline($row[2]),
                    'after_data' => replace_newline($row[3]),
                    'date_time' => $rowModifiedAt,
                    'row_number' => ($startRowNumber),
                    'is_updated' => $isUpdated
                ]);

                $startRowNumber++;
            }
            DB::commit();
            return response()->json([
                'status' => ApiStatus::SUCCESS,
                'message' => ApiStatus::GOOGLE_SHEET_DATA_READ_CHANGE_LOG_DATA,
                'status_code' => ApiStatus::API_STATUS_SUCCESS,
            ], ApiStatus::API_STATUS_SUCCESS);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Excpetion occured while reading change log data");
            Log::error($e);
            Log::debug("================END=====================");
            return response()->json([
                'status' => ApiStatus::FAILURE,
                'message' => ApiStatus::INTERNAL_ERROR,
                'status_code' => ApiStatus::API_STATUS_INTERNAL_SERVER_ERROR,
            ], ApiStatus::API_STATUS_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * To get the updated data from google sheet, into the DB based on only new sheet as Changed logs.
     * 
     * @param project_code : to read from specific project sheets.
     */
    public function readChangeLogsSheet(Request $request)
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
            $revisionId = time();

            $projectCode = $params['project_code'];
            $getProjectDetails = Projects::findProjectByCode($projectCode);
            if (empty($getProjectDetails->sheet_link) || empty($getProjectDetails->sheet_name) || empty($getProjectDetails->sheet_range)) {
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => ApiStatus::GOOGLE_SHEET_DATA_NOT_CONFIGURED,
                    'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                ], ApiStatus::API_STATUS_BAD_REQUEST);
            }
            $projectId = $getProjectDetails->id;
            $spreadsheetId = "";
            $pattern = '/\/d\/([a-zA-Z0-9-_]+)\//';

            if (preg_match($pattern, $getProjectDetails->sheet_link, $matches)) {
                $spreadsheetId = $matches[1];
            }

            if (empty($spreadsheetId)) {
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => ApiStatus::GOOGLE_SHEET_DATA_NOT_CONFIGURED,
                    'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                ], ApiStatus::API_STATUS_BAD_REQUEST);
            }

            $currentDate = date('Y-m-d');
            $getLastRow = ChangeLog::getLastRowNumber($projectId, $currentDate);
            if (empty($getLastRow)) {
                $startRowNumber = DefaultExcelColumns::DEFAULT_ROW_NUMBER;
            } else {
                $startRowNumber = $getLastRow->row_number + 1;
            }

            $dbDetails = DefaultExcelColumns::getDatabaseDetail();
            $actualMasterColumnsKeys = array_column($dbDetails, "key");
            $range = "Change Log" . "!A{$startRowNumber}:H";
            $data = $this->sheetsService->getSheetData($spreadsheetId, $range);
            if (empty($data)) {
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => ApiStatus::GOOGLE_SHEET_DATA_READ_NO_CHANGE_LOG_DATA,
                    'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                ], ApiStatus::API_STATUS_BAD_REQUEST);
            }

            DB::beginTransaction();
            foreach ($data as $key => $row) {
                $isUpdated = false;
                if (isset($row[0])) {
                    $date = replace_newline($row[0]);
                    $dateTimeObject = DateTime::createFromFormat('d/m/Y H:i:s', $date);
                    if (!$dateTimeObject) {
                        return response()->json([
                            'status' => ApiStatus::FAILURE,
                            'message' => ApiStatus::GOOGLE_SHEET_DATA_READ_CHANGE_LOG_ERROR_DATE,
                            'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                        ], ApiStatus::API_STATUS_BAD_REQUEST);
                    }
                    $rowModifiedAt = $dateTimeObject->format('Y-m-d H:i:s');
                    ChangeLog::create([
                        'project_id' => $projectId,
                        'revision_number' => $revisionId,
                        'sprint_id' => replace_newline($row[4]),
                        'column_name' => replace_newline($row[5]),
                        'before_data' => replace_newline($row[2]),
                        'after_data' => replace_newline($row[3]),
                        'date_time' => $rowModifiedAt,
                        'row_number' => ($startRowNumber),
                        'is_updated' => $isUpdated,
                        'action' => replace_newline($row[7])
                    ]);
                }
                $startRowNumber++;
            }

            foreach ($data as $key => $row) {
                $isUpdated = false;
                if (isset($row[0])) {
                    $date = replace_newline($row[0]);
                    $dateTimeObject = DateTime::createFromFormat('d/m/Y H:i:s', $date);
                    if (!$dateTimeObject) {
                        return response()->json([
                            'status' => ApiStatus::FAILURE,
                            'message' => ApiStatus::GOOGLE_SHEET_DATA_READ_CHANGE_LOG_ERROR_DATE,
                            'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                        ], ApiStatus::API_STATUS_BAD_REQUEST);
                    }
                    $rowModifiedAt = $dateTimeObject->format('Y-m-d H:i:s');

                    if (isset($row[4]) && !empty($row[5]) && $row[5] != DefaultExcelColumns::ID) {
                        $rowNewData = replace_newline($row[3]);
                        $rowSprintCode = replace_newline($row[4]);
                        $rowColumnName = replace_newline($row[5]);
                        $rowTitle = isset($row[6]) ? replace_newline($row[6]) : ""; 
                        $getSprint = Sprints::findRecentSprintByTaskID($rowSprintCode, $projectId);
                        if (!empty($getSprint)) {
                            $oldSprintId = $getSprint->id;
                            $index = array_search($rowColumnName, $actualMasterColumnsKeys);
                            if ($index !== false) {
                                $table = $dbDetails[$index]['table'];
                                $dbColumn = Str::snake($dbDetails[$index]['column']);
                                if ($table == DefaultExcelColumns::TABLE_SPRINTS) {
                                    $sprintRowData = $getSprint->toArray();
                                    unset($sprintRowData['id']);

                                    //insert new sprint row
                                    $sprintRowData[$dbColumn] = $rowNewData;
                                    $sprintRowData['revision_number'] = $revisionId;
                                    $sprintRowData['created_at'] = $rowModifiedAt;
                                    $sprint = Sprints::create($sprintRowData);
                                    $sprintId = $sprint->id;

                                    //create a new sprint data row
                                    $this->createSprintData($oldSprintId, $sprintId, $revisionId);

                                    //create a new phase assignment
                                    $this->createPhaseAssignment($oldSprintId, $sprintId, $revisionId);

                                    //create a new team assignment
                                    $this->createTeamAssignment($oldSprintId, $sprintId, $revisionId);

                                    //create a new user story assignment
                                    $this->createUserStoryAssignment($oldSprintId, $sprintId, $revisionId);
                                    $isUpdated = true;
                                } else if ($table == DefaultExcelColumns::TABLE_SPRINT_DATA) {
                                    $getSprintData = SprintData::findRecentSprintDataBySprintID($oldSprintId);
                                    $newSprint = $getSprint->toArray();
                                    unset($newSprint['id']);

                                    //create new sprint row
                                    $newSprint['revision_number'] = $revisionId;
                                    $newSprint['created_at'] = $newSprint['created_at'];
                                    $newSprint = Sprints::create($newSprint);

                                    //insert a new sprint data row
                                    $sprintDataRow = $getSprintData->toArray();
                                    unset($sprintDataRow['id']);
                                    $sprintDataRow[$dbColumn] = $rowNewData;
                                    $sprintDataRow['sprint_id'] = $newSprint->id;
                                    $sprintDataRow['revision_number'] = $revisionId;
                                    $sprintDataRow['created_at'] = $rowModifiedAt;
                                    $sprintData = SprintData::create($sprintDataRow);

                                    //create a new phase assignment
                                    $this->createPhaseAssignment($oldSprintId, $newSprint->id, $revisionId);

                                    //create a new team assignment
                                    $this->createTeamAssignment($oldSprintId, $newSprint->id, $revisionId);

                                    //create a new user story assignment
                                    $this->createUserStoryAssignment($oldSprintId, $newSprint->id, $revisionId);
                                    $isUpdated = true;
                                }
                                if (str_contains($rowColumnName, DefaultExcelColumns::SEARCH_TEXT_PAHSE) && !empty($rowTitle)) {
                                    $getPhase = Phases::findPhaseByTitle($rowTitle, $projectId);
                                    if (!empty($getPhase)) {
                                        $phaseId = $getPhase[0]['id'];
                                        $getPhaseAssignment = PhaseAssignments::findAssignment($projectId, $phaseId, $oldSprintId);
                                        if (!empty($getPhaseAssignment)) {
                                            $newSprint = $getSprint->toArray();
                                            unset($newSprint['id']);

                                            //create new sprint row
                                            $newSprint['revision_number'] = $revisionId;
                                            $newSprint['created_at'] = date('Y-m-d H:i:s', strtotime($newSprint['created_at']));
                                            $newSprint = Sprints::create($newSprint);

                                            //create a new sprint data row
                                            $this->createSprintData($oldSprintId, $newSprint->id, $revisionId);

                                            //create a new team assignment
                                            $this->createTeamAssignment($oldSprintId, $newSprint->id, $revisionId);

                                            //create a new user story assignment
                                            $this->createUserStoryAssignment($oldSprintId, $newSprint->id, $revisionId);

                                            //insert new phase assignment
                                            $phaseAssignmentData = $getPhaseAssignment->toArray();
                                            unset($phaseAssignmentData['id']);
                                            $phaseAssignmentData['phase_value'] = $rowNewData;
                                            $phaseAssignmentData['sprint_id'] = $newSprint->id;
                                            $phaseAssignmentData['revision_number'] = $revisionId;
                                            $phaseAssignmentData['created_at'] = $rowModifiedAt;
                                            $phase = PhaseAssignments::create($phaseAssignmentData);
                                            $isUpdated = true;
                                        }
                                    }
                                } else if (str_contains($rowColumnName, DefaultExcelColumns::SEARCH_TEXT_TEAM) && !empty($rowTitle)) {
                                    $getTeam = Teams::findTeamByTitle($rowTitle, $projectId);
                                    if (!empty($getTeam)) {
                                        $teamId = $getTeam[0]['id'];

                                        $getTeamAssignment = TeamAssignments::findAssignment($projectId, $oldSprintId, $teamId);
                                        if (!empty($getTeamAssignment)) {

                                            $newSprint = $getSprint->toArray();
                                            unset($newSprint['id']);

                                            //create new sprint row
                                            $newSprint['revision_number'] = $revisionId;
                                            $newSprint['created_at'] = date('Y-m-d H:i:s', strtotime($newSprint['created_at']));
                                            $newSprint = Sprints::create($newSprint);

                                            //create a new sprint data row
                                            $this->createSprintData($oldSprintId, $newSprint->id, $revisionId);

                                            //create a phase team assignment
                                            $this->createPhaseAssignment($oldSprintId, $newSprint->id, $revisionId);

                                            //create a new user story assignment
                                            $this->createUserStoryAssignment($oldSprintId, $newSprint->id, $revisionId);

                                            //insert new team assignment
                                            $teamAssignmentData = $getTeamAssignment->toArray();
                                            unset($teamAssignmentData['id']);
                                            $teamAssignmentData['alloted_sprint'] = $rowNewData;
                                            $teamAssignmentData['sprint_id'] = $newSprint->id;
                                            $teamAssignmentData['revision_number'] = $revisionId;
                                            $teamAssignmentData['created_at'] = $rowModifiedAt;
                                            $team = TeamAssignments::create($teamAssignmentData);
                                            $isUpdated = true;
                                        }
                                    }
                                } else if (str_contains($rowColumnName, DefaultExcelColumns::SEARCH_TEXT_USER_STORIES) && !empty($rowTitle)) {
                                    $getStory = UserStories::findStoryByTitle($rowTitle, $projectId);
                                    if (!empty($getStory)) {
                                        $storyId = $getStory[0]['id'];
                                        $getStoryAssignment = UserStoriesAssignments::findAssignment($projectId, $sprintId, $storyId);
                                        if (!empty($getStoryAssignment)) {

                                            $newSprint = $getSprint->toArray();
                                            unset($newSprint['id']);

                                            //create new sprint row
                                            $newSprint['revision_number'] = $revisionId;
                                            $newSprint['created_at'] = date('Y-m-d H:i:s', strtotime($newSprint['created_at']));
                                            $newSprint = Sprints::create($newSprint);

                                            //create a new sprint data row
                                            $this->createSprintData($oldSprintId, $newSprint->id, $revisionId);

                                            //create a new team assignment
                                            $this->createTeamAssignment($oldSprintId, $newSprint->id, $revisionId);

                                            //create a phase team assignment
                                            $this->createPhaseAssignment($oldSprintId, $newSprint->id, $revisionId);

                                            //insert new story assignment
                                            $storyAssignmentData = $getStoryAssignment->toArray();
                                            unset($storyAssignmentData['id']);
                                            $storyAssignmentData['alloted_sprint'] = $rowNewData;
                                            $storyAssignmentData['sprint_id'] = $newSprint->id;
                                            $storyAssignmentData['revision_number'] = $revisionId;
                                            $storyAssignmentData['created_at'] = $rowModifiedAt;
                                            $team = TeamAssignments::create($storyAssignmentData);
                                            $isUpdated = true;
                                        }
                                    }
                                }
                            }
                        } else {
                            if (str_contains($rowColumnName, DefaultExcelColumns::SEARCH_TEXT_TEAM) && !empty($rowTitle)) {
                                $getTeamByHeader = Teams::findTeamByHeader($rowColumnName, $projectId);
                                if (!empty($getTeamByHeader)) {
                                    $teamId = $getTeamByHeader[0]['id'];
                                    $changeLogTeamData = ChangeLog::getLatestTeamHeaders($projectId, $getTeamByHeader[0]['header']);
                                    $action = $changeLogTeamData["action"];
                                    $newTitleFromChangeLog = $changeLogTeamData['after_data'];
                                    if ($action == 'Add' || $action == 'Edit') {
                                        if (!empty($newTitleFromChangeLog)) {
                                            unset($getTeamByHeader[0]['title']);
                                            $newTitle = Teams::where('id', $teamId)->update(['title' => $newTitleFromChangeLog]);
                                        }
                                    } else if ($action == 'Delete') {
                                        if ($newTitleFromChangeLog === null || $newTitleFromChangeLog === '') {
                                            $newTitle = Teams::where('id', $teamId)->update(['title' => null]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                    DB::commit();
                    return response()->json([
                        'status' => ApiStatus::SUCCESS,
                        'message' => ApiStatus::GOOGLE_SHEET_DATA_READ_CHANGE_LOG_DATA,
                        'status_code' => ApiStatus::API_STATUS_SUCCESS,
                    ], ApiStatus::API_STATUS_SUCCESS);
                }
            }
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Excpetion occured while reading change log data");
            Log::error($e);
            Log::debug("================END=====================");
            return response()->json([
                'status' => ApiStatus::FAILURE,
                'message' => ApiStatus::INTERNAL_ERROR,
                'status_code' => ApiStatus::API_STATUS_INTERNAL_SERVER_ERROR,
            ], ApiStatus::API_STATUS_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * To get the updated data from google sheet, into the DB based on only Changed columns/cell from changed log sheet.
     * 
     * @param project_code : to read from specific project sheets.
     */
    public function readChangeLogs(Request $request)
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
            $revisionId = time();

            $projectCode = $params['project_code'];
            $this->readChangeLogProjectSheet($projectCode, $revisionId);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Excpetion occured while reading change log");
            Log::error($e);
            Log::debug("================END=====================");
            return response()->json([
                'status' => ApiStatus::FAILURE,
                'message' => ApiStatus::INTERNAL_ERROR,
                'status_code' => ApiStatus::API_STATUS_INTERNAL_SERVER_ERROR,
            ], ApiStatus::API_STATUS_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * This is a common function which will read actual-master and can be used when called from API and also from command.
     * 
     * @param projectCode - For which project we should read data for
     * @param userId - Which user has executed/read this sheet
     */
    public function readActualMasterProjectSheet($projectCode, $userId)
    {
        try {
            $getProjectDetails = Projects::findProjectByCode($projectCode);
            if (empty($getProjectDetails->sheet_link) || empty($getProjectDetails->sheet_name) || empty($getProjectDetails->sheet_range)) {
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => ApiStatus::GOOGLE_SHEET_DATA_NOT_CONFIGURED,
                    'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                ], ApiStatus::API_STATUS_BAD_REQUEST);
            }
            $spreadsheetId = "";
            $pattern = '/\/d\/([a-zA-Z0-9-_]+)\//';

            if (preg_match($pattern, $getProjectDetails->sheet_link, $matches)) {
                $spreadsheetId = $matches[1];
            }

            if (empty($spreadsheetId)) {
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => ApiStatus::GOOGLE_SHEET_DATA_NOT_CONFIGURED,
                    'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                ], ApiStatus::API_STATUS_BAD_REQUEST);
            }

            $dateColumnRange = $getProjectDetails->sheet_name . '!A28:E28';
            $datesInfo = $this->sheetsService->getSheetData($spreadsheetId, $dateColumnRange);
            if (empty($datesInfo[0][1]) ||  empty($datesInfo[0][4])) {
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => ApiStatus::GOOGLE_SHEET_INVALID_DATE_INFO,
                    'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                ], ApiStatus::API_STATUS_BAD_REQUEST);
            }
            $startDate = date('Y-m-d', strtotime($datesInfo[0][1]));
            $sprintStartDate = date('Y-m-d', strtotime($datesInfo[0][4]));

            $revisionId = time();
            if ($getProjectDetails->start_date != $startDate) {
                $changedColumns['revision_number'] = $revisionId;
                $changedColumns['start_date'] = $startDate;
                $originalAttributes = $getProjectDetails->getOriginal();
                $getProjectDetails->fill($changedColumns);
                $this->loggingService->logChange($getProjectDetails, 'updated', $revisionId, $getProjectDetails->id, $userId, $originalAttributes);
                $getProjectDetails->save();
            }
            if ($getProjectDetails->sprint_start_date != $sprintStartDate) {
                $changedColumns['revision_number'] = $revisionId;
                $changedColumns['sprint_start_date'] = $sprintStartDate;
                $originalAttributes = $getProjectDetails->getOriginal();
                $getProjectDetails->fill($changedColumns);
                $this->loggingService->logChange($getProjectDetails, 'updated', $revisionId, $getProjectDetails->id, $userId, $originalAttributes);
                $getProjectDetails->save();
            }

            $groupIstechRange = $getProjectDetails->sheet_name . '!T28:AQ28';
            $groupIstechInfo = $this->sheetsService->getSheetData($spreadsheetId, $groupIstechRange);

            $range = $getProjectDetails->sheet_name . '!' . $getProjectDetails->sheet_range;
            $data = $this->sheetsService->getSheetData($spreadsheetId, $range);
            //Reading project and sprint start date
            $keys = [];
            $readableTitles = [];
            $teamDetails = [];
            // DB::beginTransaction(); 
            $pahsesIndexes = $teamsIndexes = $userStoryIndexes = [];
            foreach ($data as $index => $row) {
                if ($index == 0) {
                    // First row: keys
                    $keys = $row;
                    if (count($keys) != DefaultExcelColumns::TOTAL_COLUMNS) {
                        return response()->json([
                            'status' => ApiStatus::FAILURE,
                            'message' => ApiStatus::GOOGLE_SHEET_DATA_READ_ERROR,
                            'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                        ], ApiStatus::API_STATUS_BAD_REQUEST);
                    }

                    $pahsesIndexes = $this->findCustomIndexes($keys, DefaultExcelColumns::SEARCH_TEXT_PAHSE);
                    $teamsIndexes = $this->findCustomIndexes($keys, DefaultExcelColumns::SEARCH_TEXT_TEAM);
                    $userStoryIndexes = $this->findCustomIndexes($keys, DefaultExcelColumns::SEARCH_TEXT_USER_STORIES);
                    if (!empty($groupIstechInfo) && isset($groupIstechInfo[0])) {
                        $groupIstechInfo = $groupIstechInfo[0];
                        foreach ($teamsIndexes as $key => $index) {
                            $teamDetails[(int)$index] = $groupIstechInfo[$key];
                        }
                    }
                } elseif ($index == 1) {
                    // Second row: readable titles
                    $readableTitles = $row;
                } else {
                    // Data rows
                    $rowData = $row;
                    $this->processRow($keys, $rowData, $revisionId, $userId, $readableTitles, $pahsesIndexes, $teamsIndexes, $userStoryIndexes, $teamDetails);
                }
            }

            return response()->json([
                'status' => ApiStatus::SUCCESS,
                'message' => ApiStatus::GOOGLE_SHEET_DATA_REFRESHED,
                'status_code' => ApiStatus::API_STATUS_SUCCESS,
            ], ApiStatus::API_STATUS_SUCCESS);
        } catch (Exception $e) {
            Log::error("Excpetion occured in read Actual Master Project Sheet");
            Log::error($e);
            Log::debug("================END=====================");
            return response()->json([
                'status' => ApiStatus::FAILURE,
                'message' => ApiStatus::INTERNAL_ERROR,
                'status_code' => ApiStatus::API_STATUS_INTERNAL_SERVER_ERROR,
            ], ApiStatus::API_STATUS_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * This is a common function which will read change-log and cab be used when called from API and also from command.
     * 
     * @param projectCode - For which project we should read data for
     * @param userId - Which user has executed/read this sheet
     */
    public function readChangeLogProjectSheet($projectCode, $revisionId)
    {
        try {

            $getProjectDetails = Projects::findProjectByCode($projectCode);
            if (empty($getProjectDetails->sheet_link) || empty($getProjectDetails->sheet_name) || empty($getProjectDetails->sheet_range)) {
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => ApiStatus::GOOGLE_SHEET_DATA_NOT_CONFIGURED,
                    'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                ], ApiStatus::API_STATUS_BAD_REQUEST);
            }
            $projectId = $getProjectDetails->id;
            $spreadsheetId = "";
            $pattern = '/\/d\/([a-zA-Z0-9-_]+)\//';

            if (preg_match($pattern, $getProjectDetails->sheet_link, $matches)) {
                $spreadsheetId = $matches[1];
            }

            if (empty($spreadsheetId)) {
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => ApiStatus::GOOGLE_SHEET_DATA_NOT_CONFIGURED,
                    'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                ], ApiStatus::API_STATUS_BAD_REQUEST);
            }

            $currentDate = date('Y-m-d');
            $getLastRow = ChangeLog::getLastRowNumber($projectId, $currentDate);
            if (empty($getLastRow)) {
                $startRowNumber = DefaultExcelColumns::DEFAULT_ROW_NUMBER;
            } else {
                $startRowNumber = $getLastRow->row_number + 1;
            }


            $range = DefaultExcelColumns::CHANGE_LOG_SHEET_NAME . "!A{$startRowNumber}:H";
            $data = $this->sheetsService->getSheetData($spreadsheetId, $range);
            if (empty($data)) {
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => ApiStatus::GOOGLE_SHEET_DATA_READ_NO_CHANGE_LOG_DATA,
                    'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                ], ApiStatus::API_STATUS_BAD_REQUEST);
            }
            DB::beginTransaction();
            foreach ($data as $key => $row) {

                $isUpdated = false;
                if (isset($row[0])) {
                    $date = replace_newline($row[0]);
                    $dateTimeObject = DateTime::createFromFormat('d/m/Y H:i:s', $date);
                    if (!$dateTimeObject) {
                        return response()->json([
                            'status' => ApiStatus::FAILURE,
                            'message' => ApiStatus::GOOGLE_SHEET_DATA_READ_CHANGE_LOG_ERROR_DATE,
                            'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                        ], ApiStatus::API_STATUS_BAD_REQUEST);
                    }
                    $rowModifiedAt = $dateTimeObject->format('Y-m-d H:i:s');
                    ChangeLog::create([
                        'project_id' => $projectId,
                        'revision_number' => $revisionId,
                        'sprint_id' => replace_newline($row[4]),
                        'column_name' => replace_newline($row[5]),
                        'before_data' => replace_newline($row[2]),
                        'after_data' => replace_newline($row[3]),
                        'date_time' => $rowModifiedAt,
                        'row_number' => ($startRowNumber),
                        'is_updated' => $isUpdated,
                        'action' => replace_newline($row[7])
                    ]);
                }
                $startRowNumber++;
            }
            DB::commit();
            return response()->json([
                'status' => ApiStatus::SUCCESS,
                'message' => ApiStatus::GOOGLE_SHEET_DATA_READ_CHANGE_LOG_DATA,
                'status_code' => ApiStatus::API_STATUS_SUCCESS,
            ], ApiStatus::API_STATUS_SUCCESS);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Excpetion occured while read Change Log Project Sheet");
            Log::error($e);
            Log::debug("================END=====================");
            return response()->json([
                'status' => ApiStatus::FAILURE,
                'message' => ApiStatus::INTERNAL_ERROR,
                'status_code' => ApiStatus::API_STATUS_INTERNAL_SERVER_ERROR,
            ], ApiStatus::API_STATUS_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * This is used to clear the sheet named Change Logs by api call.
     * 
     * @param project_code : To clear the sheet data for given project ID 
     */
    public function clearChangeLogSheet(Request $request)
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
            $user = $request->user();
            $userId = $user->id;

            $revisionId = time();
            $response = $this->clearChangeLogSheetData($projectCode, $userId, $revisionId, request()->ip());
            if ($response) {

                return response()->json([
                    'status' => ApiStatus::SUCCESS,
                    'message' => ApiStatus::GOOGLE_SHEET_DATA_REFRESHED,
                    'status_code' => ApiStatus::API_STATUS_SUCCESS,
                ], ApiStatus::API_STATUS_SUCCESS);
            } else {
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => ApiStatus::GOOGLE_SHEET_DATA_READ_ERROR,
                    'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                ], ApiStatus::API_STATUS_BAD_REQUEST);
            }
        } catch (Exception $e) {
            Log::error("Excpetion occured while clear change log sheet");
            Log::error($e);
            Log::debug("================END=====================");
            return response()->json([
                'status' => ApiStatus::FAILURE,
                'message' => ApiStatus::INTERNAL_ERROR,
                'status_code' => ApiStatus::API_STATUS_INTERNAL_SERVER_ERROR,
            ], ApiStatus::API_STATUS_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * This is used to clear the sheet named Change Logs
     * 
     * @param projectCode : To clear the sheet data for given project ID 
     * @param userId : User id, who executes this via command/API
     * @param revisionId : Revision id is nothing but the time when its been called 
     * @param ip : IP Address of the user who calls it via API, null if called by command
     */
    public function clearChangeLogSheetData($projectCode, $userId, $revisionId, $ip = null)
    {
        try {
            $getProjectDetails = Projects::findProjectByCode($projectCode);
            if (empty($getProjectDetails->sheet_link) || empty($getProjectDetails->sheet_name) || empty($getProjectDetails->sheet_range)) {
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => ApiStatus::GOOGLE_SHEET_DATA_NOT_CONFIGURED,
                    'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                ], ApiStatus::API_STATUS_BAD_REQUEST);
            }
            $spreadsheetId = "";
            $pattern = '/\/d\/([a-zA-Z0-9-_]+)\//';

            if (preg_match($pattern, $getProjectDetails->sheet_link, $matches)) {
                $spreadsheetId = $matches[1];
            }

            if (empty($spreadsheetId)) {
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => ApiStatus::GOOGLE_SHEET_DATA_NOT_CONFIGURED,
                    'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                ], ApiStatus::API_STATUS_BAD_REQUEST);
            }

            $range = DefaultExcelColumns::CHANGE_LOG_SHEET_NAME . '!A2:J';

            AuditLogs::create([
                'revision_number' => $revisionId,
                'project_id' => $getProjectDetails->id,
                'user_id' => $userId,
                'action' => 'Change log cleared',
                'request_body' => json_encode(['sheet_id' => $spreadsheetId, 'range' => $range]),
                'ip' => $ip,
                'created_by' => $userId,
            ]);

            return $this->sheetsService->clearSheetData($spreadsheetId, $range);
        } catch (Exception $e) {
            Log::error("Excpetion occured while clear change log sheet data");
            Log::error($e);
            Log::debug("================END=====================");
            return response()->json([
                'status' => ApiStatus::FAILURE,
                'message' => ApiStatus::INTERNAL_ERROR,
                'status_code' => ApiStatus::API_STATUS_INTERNAL_SERVER_ERROR,
            ], ApiStatus::API_STATUS_INTERNAL_SERVER_ERROR);
        }
    }


    public function createSprintData($oldSprintId, $newSprintId, $revisionId)
    {
        $getSprintData = SprintData::findRecentSprintDataBySprintID($oldSprintId);
        $sprintData = $getSprintData->toArray();
        unset($sprintData['id']);
        $sprintData['sprint_id'] = $newSprintId;
        $sprintData['revision_number'] = $revisionId;
        $sprintData['created_at'] = date('Y-m-d H:i:s', strtotime($sprintData['created_at']));
        $sprintData = SprintData::create($sprintData);
        return $sprintData->id;
    }

    public function createPhaseAssignment($oldSprintId, $newSprintId, $revisionId)
    {
        $getPhaseAssignment = PhaseAssignments::findRecentAssignmentDataBySprintID($oldSprintId);
        $phaseData = $getPhaseAssignment->toArray();
        foreach ($phaseData as $value) {
            $value['sprint_id'] = $newSprintId;
            $value['revision_number'] = $revisionId;
            $value['created_at'] = date('Y-m-d H:i:s', strtotime($value['created_at']));
            PhaseAssignments::create($value);
        }
        return true;
    }

    public function createTeamAssignment($oldSprintId, $newSprintId, $revisionId)
    {
        $getTeamAssignment = TeamAssignments::findRecentAssignmentDataBySprintID($oldSprintId);
        $teamData = $getTeamAssignment->toArray();
        foreach ($teamData as $value) {
            $value['sprint_id'] = $newSprintId;
            $value['revision_number'] = $revisionId;
            $value['created_at'] = date('Y-m-d H:i:s', strtotime($value['created_at']));
            TeamAssignments::create($value);
        }
        return true;
    }

    public function createUserStoryAssignment($oldSprintId, $newSprintId, $revisionId)
    {
        $getStoryAssignment = UserStoriesAssignments::findRecentAssignmentDataBySprintID($oldSprintId);
        $storyData = $getStoryAssignment->toArray();
        foreach ($storyData as $value) {
            $value['sprint_id'] = $newSprintId;
            $value['revision_number'] = $revisionId;
            $value['created_at'] = date('Y-m-d H:i:s', strtotime($value['created_at']));
            UserStoriesAssignments::create($value);
        }
        return true;
    }
}
