<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Log;
use ApiStatus;
use App\Models\Projects;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{

    /**
     * To create project
     * 
     * This function validates the input parameters, and returns a JSON response
     * @param Request $request
     * @return json response.
     */
    public function createProject(Request $request)
    {

        try {
            $params = $request->all();
            $userId = $request->user()->id;
            $validationRules = [
                'project_name' => 'required|string|max:50|regex:/^[a-zA-Z\s]*$/',
                'sprint_days' => 'required|integer|min:1',
                'no_of_phases' => 'required|integer|min:1',
                'no_of_resource_groups' => 'required|integer|min:1',
                'sheet_url' => 'required|url',
                'sheet_name' => 'required|string|max:255',
                'sheet_range' => 'required|string|max:255',
                'project_start_date' => 'required|date',
                'sprint_start_date' => 'required|date|after_or_equal:project_start_date',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ];
            $customMessages = [
                'project_name.required' => 'The project name is required.',
                'project_name.regex' => 'Invalid name field. It only accepts characters.',
                'project_name.string' => 'The project name must be a string.',
                'project_name.max' => 'The project name must not exceed 50 characters.',
                'sprint_days.required' => 'The number of days in a sprint is required.',
                'sprint_days.integer' => 'The number of days in a sprint must be an integer.',
                'sprint_days.min' => 'The number of days in a sprint must be at least 1.',
                'no_of_phases.required' => 'The number of phases is required.',
                'no_of_phases.integer' => 'The number of phases must be an integer.',
                'no_of_phases.min' => 'The number of phases must be at least 1.',
                'no_of_resource_groups.required' => 'The number of resource groups is required.',
                'no_of_resource_groups.integer' => 'The number of resource groups must be an integer.',
                'no_of_resource_groups.min' => 'The number of resource groups must be at least 1.',
                'sheet_url.required' => 'The sheet URL is required.',
                'sheet_url.url' => 'The sheet URL must be a valid URL.',
                'sheet_name.required' => 'The sheet name is required.',
                'sheet_name.string' => 'The sheet name must be a string.',
                'sheet_name.max' => 'The sheet name must not exceed 255 characters.',
                'sheet_range.required' => 'The sheet range is required.',
                'sheet_range.string' => 'The sheet range must be a string.',
                'sheet_range.max' => 'The sheet range must not exceed 255 characters.',
                'project_start_date.required' => 'The project start date is required.',
                'project_start_date.date' => 'The project start date must be a valid date.',
                'sprint_start_date.required' => 'The sprint start date is required.',
                'sprint_start_date.date' => 'The sprint start date must be a valid date.',
                'sprint_start_date.after_or_equal' => 'The sprint start date must be after or equal to the project start date.',
                'photo.image' => 'The photo must be an image.',
                'photo.mimes' => 'The photo must be a file of type: jpeg, png, jpg, gif, svg.',
                'photo.max' => 'The photo may not be greater than 2048 kilobytes.',
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

            $newTitle = $params['project_name'];
            $projectTitle = Projects::findProjectByTitle($newTitle);
            if ($projectTitle) {
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => ApiStatus::PROJECT_ALREADY_EXISTED,
                    'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                ], ApiStatus::API_STATUS_BAD_REQUEST);
            }

            $lastId = Projects::getLastProjectId();
            $newId = $lastId + 1;
            $zeros = strlen($newId) > 2 ? '' : str_repeat('0', 3 - strlen($newId));
            $newCode = 'PMOID' . $zeros . $newId;

            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $photoPath = $photo->store('photos', 'public');
            }

            $project = Projects::create([
                'code' => $newCode,
                'title' => $params['project_name'],
                'no_of_days_in_sprint' => $params['sprint_days'],
                'no_of_phases' => $params['no_of_phases'],
                'no_of_resource_groups' => $params['no_of_resource_groups'],
                'sheet_link' => $params['sheet_url'],
                'sheet_name' => $params['sheet_name'],
                'sheet_range' => $params['sheet_range'],
                'start_date' => date('Y-m-d', strtotime($params['project_start_date'])),
                'sprint_start_date' => date('Y-m-d', strtotime($params['sprint_start_date'])),
                'photo_path' => $photoPath,
                'created_by' => $userId,
            ]);

            return response()->json([
                'status' => ApiStatus::SUCCESS,
                'message' => ApiStatus::PROJECT_ADD,
                'status_code' => ApiStatus::API_STATUS_SUCCESS,
            ], ApiStatus::API_STATUS_SUCCESS);
        } catch (Exception $e) {
            Log::debug("Exception occured while create project API.");
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
     * Retrieves a list of project.
     * Fetches project details
     * @return json response containg title and photo_path along with standard response keys.
     */
    public function getProjectsList(Request $request)
    {
        try {
            $projects = Projects::getAllProjectWithTitle();
            foreach ($projects as $project) {
                if ($project["photo_path"]) {
                    $project["photo_path"] = url('storage/' . $project["photo_path"]);
                }
            }
            if (empty($projects)) {
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => ApiStatus::NO_PROJECT_DATA_FOUND,
                    'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                ], ApiStatus::API_STATUS_BAD_REQUEST);
            }
            return response()->json([
                'status' => ApiStatus::SUCCESS,
                'data' => $projects,
                'message' => ApiStatus::PROJECT_LIST,
                'status_code' => ApiStatus::API_STATUS_SUCCESS,
            ], ApiStatus::API_STATUS_SUCCESS);
        } catch (Exception $e) {
            Log::debug("Exception occured while getting project list API.");
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
