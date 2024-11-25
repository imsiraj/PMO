<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Hashids\Hashids;
use App\Services\LoggingService;
use ApiStatus;
use App\Models\User;
use UserRoles;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UsersController extends Controller
{
    protected $hashids;
    protected $loggingService;

    public function __construct(LoggingService $loggingService)
    {
        $this->hashids = new Hashids();
        $this->loggingService = $loggingService;
    }

    /**
     * Update users data by token and id.
     * 
     * @return json user data.
     **/
    public function updateUsers(Request $request, $id)
    {
        try {
            $authUser = $request->user()->id;
            $targetUser = User::getUserById($id);
            if (!$targetUser) {
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => ApiStatus::USER_NOT_FOUND,
                    'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                ], ApiStatus::API_STATUS_BAD_REQUEST);
            }
            if ($targetUser->u_roles != UserRoles::SUPER_ADMIN_ID && $id != $targetUser->id) {
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => ApiStatus::UNAUTHORIZE_ACTION,
                    'status_code' => ApiStatus::API_STATUS_UN_AUTHORIZED,
                ], ApiStatus::API_STATUS_UN_AUTHORIZED);
            }

            $validationRules = [
                'name' => 'required|max:50|regex:/^[a-zA-Z\s]*$/',
                'country_phonecode' => 'required|regex:/^\+\d{1,3}$/',
                'mobile_number' => 'required|regex:/^\d{10,15}$/|unique:users,mobile_number,' . $targetUser->id,
                'email' => 'required|email|max:100|unique:users,email,' . $targetUser->id,
            ];
            $customMessages = [
                'name.required' => 'Name field is required.',
                'name.regex' => 'Invalid name field. It only accepts characters.',
                'name.max' => 'Name field should not exceed 50 characters.',
                'country_phonecode.required' => 'Country phonecode field is required.',
                'country_phonecode.regex' => 'Invalid country phonecode field. Expected format is +91',
                'mobile_number.required' => 'Mobile number field is required ',
                'mobile_number.regex' => 'Invalid mobile number field. It accepts min 10, & max 15 digits only.',
                'mobile_number.unique' => 'Mobile number field must be unique. Provided mobile number already taken.',
                'email.required' => 'Email field is required.',
                'email.email' => 'Invalid email field.',
                'email.max' => 'Email field should not exceed 100 characters.',
                'email.unique' => 'Email field must be unique. Provided email already taken.',
            ];
            $validator = Validator::make($request->all(), $validationRules, $customMessages);
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
            $validatedData = $request->only([
                'name',
                'country_phonecode',
                'mobile_number',
                'email',
                'created_by'
            ]);
            if ($targetUser->status == false && $request->input('status') == true) {
                $validatedData['status'] = true;
                $validatedData['created_by'] = $authUser;
            }
            $targetUser->update($validatedData);
            $targetUser->makeHidden(['email_verified_at', 'created_at', 'updated_at', 'mobile_verified_at', 'status']);
            return response()->json([
                'status' => ApiStatus::SUCCESS,
                'message' => ApiStatus::USER_UPDATED,
                'data' => $targetUser,
                'status_code' => ApiStatus::API_STATUS_SUCCESS,
            ], ApiStatus::API_STATUS_SUCCESS);
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug("Exception occurred while Updating user data.");
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
