<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\SmartMail;
use App\Models\User;
use App\Models\PasswordReset;

use App\Models\EmailTemplates;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\PersonalAccessToken;
use Hashids\Hashids;
use App\Services\LoggingService;
use Illuminate\Support\Facades\Password;
use ApiStatus;
use Templates;
use App\Models\AuditLogs;
use EmailConfig;
use Exception;
use UserRoles;
use Illuminate\Support\Str;
use Google\Service\VMwareEngine\LoggingServer;
// use Illuminate\Auth\Events\PasswordReset;

class AuthController extends Controller
{

    protected $hashids;
    protected $loggingService;

    public function __construct(LoggingService $loggingService)
    {
        $this->hashids = new Hashids();
        $this->loggingService = $loggingService;
    }

    /**
     * Register the user to the portal and send the email with the template to verify the user's email address.
     * It also keep the status to false unitl made true by Admin from dashboard. 
     * It validates the input and sends the appropriate  json response.
     * 
     *  @param name - TThe user's full name or first name
     *  @param country_phonecode - This the country code for mobile number
     *  @param mobile_number - This is the user mobile number
     *  @param email - This is the user email address
     *  @param password - This is the user's password
     */
    public function register(Request $request)
    {
        try {
            $params = $request->all();
            $validationRules = [
                'name' => 'required|max:50|regex:/^[a-zA-Z\s]*$/',
                'country_phonecode' => 'required|regex:/^\+\d{1,3}$/',
                'mobile_number' => 'required|regex:/^\d{10,15}$/|unique:users,mobile_number',
                'email' => 'required|email|max:100|unique:users',
                'password' => 'required|string|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{8,10}$/',
                'password_confirmation' => 'required_with:password|string|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{8,10}$/',
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
                'password.regex' => 'Password must contain minimum 8 and maximum 10 characters, at least one uppercase letter, one lowercase letter, one number and one special character.',
                'password_confirmation.required_with' => 'Password confirmation is required.',
                'password_confirmation.regex' => 'Password confirmation must contain minimum 8 and maximum 10 characters, at least one uppercase letter, one lowercase letter, one number, and one special character.',
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

            DB::beginTransaction();
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'country_phonecode' => $request->country_phonecode,
                'mobile_number' => $request->mobile_number,
                'password' => Hash::make($request->password),

            ]);
            $token = $user->createToken('email_verify_token')->plainTextToken;
            $getRegistrationTemplate = EmailTemplates::find(Templates::USER_REGISTRATION_TEMPLATE);
            if (empty($getRegistrationTemplate)) {
                DB::rollBack();
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => ApiStatus::EMAIL_TEMPLATE_NOT_FOUND,
                    'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                ], ApiStatus::API_STATUS_BAD_REQUEST);
            }
            $getEmailVerificationTemplate = EmailTemplates::find(Templates::USER_EMAIL_VERIFICATION);
            if (empty($getEmailVerificationTemplate)) {
                DB::rollBack();
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => ApiStatus::EMAIL_TEMPLATE_NOT_FOUND,
                    'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                ], ApiStatus::API_STATUS_BAD_REQUEST);
            }
            $encodedId = $this->hashids->encode($user->id);
            $base64Id = base64_encode($encodedId);
            $base64Token = base64_encode($token);
            $verificationLink = URL::temporarySignedRoute(
                'user-email-verify',
                now()->addHours(EmailConfig::EMAIL_VERIFICATION_VALIDITY),
                ['user_id' =>  $base64Id, 'token' => $base64Token]
            );
            $shortcodeArgs = array(
                '{APP_NAME}' => env('APP_NAME'),
                '{PORTAL_URL}' => env('APP_URL'),
                '{FULL_NAME}' => $request->name,
                '{VERIFY_EMAIL_LINK}' => $verificationLink
            );
            $registrationTemplate = [
                'subject' => shortcode($shortcodeArgs, $getRegistrationTemplate->subject),
                'body' => shortcode($shortcodeArgs, $getRegistrationTemplate->content)
            ];
            $verificationTemplate = [
                'subject' => shortcode($shortcodeArgs, $getEmailVerificationTemplate->subject),
                'body' => shortcode($shortcodeArgs, $getEmailVerificationTemplate->content)
            ];
            // Mail::to($request->email)->send(new SmartMail($registrationTemplate));
            // Mail::to($request->email)->send(new SmartMail($verificationTemplate));
            DB::commit();
            return response()->json([
                'status' => ApiStatus::SUCCESS,
                'message' => ApiStatus::REGISTRATION_SUCCESS,
                'data' => $verificationLink,
                'status_code' => ApiStatus::API_STATUS_SUCCESS,
            ], ApiStatus::API_STATUS_SUCCESS);
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug("Exception occured while user registration.");
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
     * User logs-in to the portal and receives the token to be used for further actions and operation within the portal.
     * 
     *  @param email
     *  @param password
     */
    public function login(Request $request)
    {
        try {
            $params = $request->all();
            $validationRules = [
                'email' => 'required|email|max:100',
                'password' => 'required|min:8|max:10',
            ];
            $customMessages = [
                'email.required' => 'Email field is required.',
                'email.email' => 'Invalid email field.',
                'email.max' => 'Email field should not exceed 100 characters.',
                'password.required' => 'Password field is required.',
                'password.min' => 'Password field must be at least 8 characters.',
                'password.max' => 'Password field must not be greater than 10 characters.'
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
            $credentials = $request->only('email', 'password');
            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => ApiStatus::INVALID_CREDENTIALS,
                    'status_code' => ApiStatus::API_STATUS_UN_AUTHORIZED,
                ], ApiStatus::API_STATUS_UN_AUTHORIZED);
            }
            $user = Auth::user();
            if ($user->status) {
                $token = $user->createToken('login_token')->plainTextToken;
                return response()->json([
                    'status' => ApiStatus::SUCCESS,
                    'data' => ['token' => $token, 'name' => $user->name],
                    'message' => ApiStatus::LOGIN_SUCCESS,
                    'status_code' => ApiStatus::API_STATUS_SUCCESS,
                ], ApiStatus::API_STATUS_SUCCESS);
            } else {
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => ApiStatus::ACCOUNT_BLOCKED,
                    'status_code' => ApiStatus::API_STATUS_UN_AUTHORIZED,
                ], ApiStatus::API_STATUS_UN_AUTHORIZED);
            }
        } catch (Exception $e) {
            Log::debug("Exception occured while user login.");
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
     * Handle the user email verification process.
     * @param userId 
     * @param token
     **/
    public function userEmailVerification(Request $request, $userId, $token)
    {
        try {
            $base64Id = base64_decode($userId);
            $decodeToken = base64_decode($token);
            $decodedId = $this->hashids->decode($base64Id)[0];
            $getUser = User::getUserById($decodedId);
            if (empty($getUser)) {
                return response()->view('errors.invalid-token', ['msg' => ApiStatus::EMAIL_VERIFICATION_LINK_ERROR], 400);
            }
            if ($getUser->email_verified_at) {
                return response()->view('errors.invalid-token', ['msg' => ApiStatus::EMAIL_VERIFICATION_ALREADY], 200);
            }

            $accessToken = PersonalAccessToken::findToken($decodeToken);
            if (empty($accessToken)) {
                return response()->view('errors.invalid-token', ['msg' => ApiStatus::EMAIL_VERIFICATION_LINK_ERROR], 400);
            }

            $update = User::updateEmailVerifiedByUser($decodedId);
            if ($update) {
                return response()->view('email.web.user-email-verified');
            } else {
                return response()->view('errors.invalid-token', ['msg' => ApiStatus::EMAIL_VERIFICATION_FAILED], 400);
            }
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug("Exception occured while user email verification.");
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
     * Handle the password change request..
     * 
     *  @param current_password - This is the user's password
     *  @param new_password- This is the user's new password
     */
    public function changePassword(Request $request)
    {
        $revisionId = time();
        try {
            $validationRules = [
                'current_password' => 'required|string',
                'new_password' => 'required|string|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{8,10}$/',
                'new_password_confirmation' => 'required|string|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{8,10}$/',
            ];
            $customMessages = [
                'current_password.required' => 'Current Password is required',
                'new_password.required' => 'New Password is required',
                'new_password.regex' => 'New password must contain minimum 8 and maximum 10 characters, at least one uppercase letter, one lowercase letter, one number and one special character.',
                'new_password.confirmed' => 'New password and confirm password do not match.',
                'new_password_confirmation.required' => 'Confirmed password is required.',
                'new_password_confirmation.regex' => 'Confirmed password must contain minimum 8 and maximum 10 characters, at least one uppercase letter, one lowercase letter, one number, and one special character.',
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
            $auth = $request->user();
            if (!$auth) {
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => ApiStatus::USER_UNAUTHORIZE,
                    'status_code' => ApiStatus::API_STATUS_UN_AUTHORIZED,
                ], ApiStatus::API_STATUS_UN_AUTHORIZED);
            }
            if (!Hash::check($request->get('current_password'), $auth->password)) {
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => ApiStatus::CURRENT_PASSWORD_INCORRECT,
                    'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                ], ApiStatus::API_STATUS_BAD_REQUEST);
            }
            if (strcmp($request->get('current_password'), $request->new_password) == 0) {
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => ApiStatus::NEW_CURRENT_PASSWORD_SAME,
                    'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                ], ApiStatus::API_STATUS_BAD_REQUEST);
            }
            $originalAttributes = $auth->getAttributes();
            DB::beginTransaction();
            try {
                $newPasswordHash = Hash::make($request->new_password);
                $auth->password = $newPasswordHash;
                $auth->save();
                $this->loggingService->logChange($auth, 'updated', $revisionId, null, $auth->id, $originalAttributes);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
            }
            return response()->json([
                'status' => ApiStatus::SUCCESS,
                'message' => ApiStatus::PASSWORD_CHANGED_SUCCESS,
                'status_code' => ApiStatus::API_STATUS_SUCCESS,
            ], ApiStatus::API_STATUS_SUCCESS);
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug("Exception occurred while changing password.");
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
     * Retrieve a user by their token.
     * 
     * @return json format user data
     **/
    public function getUser(Request $request)
    {
        try {
            $user = $request->user();
            if ($user) {
                $user->makeHidden(['email_verified_at', 'created_at', 'updated_at', 'mobile_verified_at', 'status']);
                return response()->json([
                    'status' => ApiStatus::SUCCESS,
                    'message' => ApiStatus::USER_FOUND,
                    'data' => $user,
                    'status_code' => ApiStatus::API_STATUS_SUCCESS,
                ], ApiStatus::API_STATUS_SUCCESS);
            } else {
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => ApiStatus::USER_NOT_FOUND,
                    'data' => null,
                    'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                ], ApiStatus::API_STATUS_BAD_REQUEST);
            }
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug("Exception occurred while getting user data.");
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
     * Update user data by token.
     * 
     * @return json user data.
     **/
    public function updateProfile(Request $request)
    {
        try {
            $user = $request->user();
            $validationRules = [
                'name' => 'required|max:50|regex:/^[a-zA-Z\s]*$/',
                'country_phonecode' => 'required|regex:/^\+\d{1,3}$/',
                'mobile_number' => 'required|regex:/^\d{10,15}$/|unique:users,mobile_number,' . $user->id,
                'email' => 'required|email|max:100|unique:users,email,' . $user->id,
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
            ]);
            if ($user->status == 0 && $request->input('status') == 1) {
                $validatedData['status'] = 1;
                $validatedData['created_by'] = $user->id;
            }
            $user->update($validatedData);
            $user->makeHidden(['email_verified_at', 'created_at', 'updated_at', 'mobile_verified_at', 'status']);
            return response()->json([
                'status' => ApiStatus::SUCCESS,
                'message' => ApiStatus::USER_UPDATED,
                'data' => $user,
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

    /**
     * Handle the Reset Password request.
     * This function validates the user's email and mobile number, generates a password reset token,
     * and sends an email with the password reset link to the user's registered email address.
     * @param Request user's email and mobile number.
     **/
    public function resetPassword(Request $request)
    {
        try {
            $params = $request->all();
            $currentEnv = env('APP_ENV');
            $stagingUrl = env('FRONTEND_URL');
            $localUrl = env('LOCAL_FRONTEND_URL');
            $appUrl = $currentEnv === 'local' ? $localUrl : $stagingUrl;
            $validationRules = [
                "email" => "required|email|exists:users,email",
                'mobile_number' => 'required|regex:/^\d{10,15}$/|exists:users,mobile_number'
            ];
            $customMessages = [
                'email.required' => 'Email is required.',
                'email.email' => 'Please provide a valid email address.',
                'email.exists' => 'The provided email does not exist in our records.',
                'mobile_number.required' => 'Mobile number is required.',
                'mobile_number.regex' => 'Mobile number must be between 10 and 15 digits.',
                'mobile_number.exists' => 'The provided mobile number does not exist in our records.',
            ];
            $validator = Validator::make($params, $validationRules, $customMessages);
            if ($validator->fails()) {
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => $validator->errors()->first(),
                    'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                ], ApiStatus::API_STATUS_BAD_REQUEST);
            }
            DB::beginTransaction();
            $user = User::getUserByEmail($request->email);
            if (!$user) {
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => ApiStatus::USER_NOT_FOUND,
                    'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                ], ApiStatus::API_STATUS_BAD_REQUEST);
            }
            if ($user->status == false) {
                DB::rollBack();
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => ApiStatus::ACCOUNT_BLOCKED,
                    'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                ], ApiStatus::API_STATUS_BAD_REQUEST);
            }
            $token = Str::random(ApiStatus::RANDOM_STR_TOKEN);
            PasswordReset::updateOrInsertPasswordReset($request->email, $token);
            $resetLink = URL::temporarySignedRoute(
                'verify-reset-password',
                now()->addHours(EmailConfig::FORGOT_PASSWORD_VALIDITY),
                ['email' => base64_encode($request->email), 'token' => base64_encode($token)]
            );
            $resetLink = $appUrl . '/resetpassword?email=' . base64_encode($request->email) . '&token=' . base64_encode($token);
            $emailTemplate = EmailTemplates::find(Templates::USER_FORGOT_PASSWORD_TEMPLATE);
            if (empty($emailTemplate)) {
                DB::rollBack();
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => ApiStatus::EMAIL_TEMPLATE_NOT_FOUND,
                    'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                ], ApiStatus::API_STATUS_BAD_REQUEST);
            }
            $shortcodeArgs = [
                '{APP_NAME}' => env('APP_NAME'),
                '{PORTAL_URL}' => $appUrl,
                '{FULL_NAME}' => $user->name,
                '{RESET_PASSWORD_LINK}' => urldecode($resetLink),
            ];
            $emailContent = [
                'subject' => shortcode($shortcodeArgs, $emailTemplate->subject),
                'body' => shortcode($shortcodeArgs, $emailTemplate->content),
            ];
            Mail::to($user->email)->send(new SmartMail($emailContent));
            DB::commit();
            return response()->json([
                'status' => ApiStatus::SUCCESS,
                'message' => ApiStatus::PASSWORD_RESET_LINK_SENT,
                'status_code' => ApiStatus::API_STATUS_SUCCESS,
            ], ApiStatus::API_STATUS_SUCCESS);
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug("Exception occurred while Forgetting Password.");
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
     * Verifying the email and token from reset link.
     * @param string $email The base64-encoded email of the user requesting password reset.
     * @param string $token The base64-encoded password reset token.
     */
    public function verifyResetPasswordLink(Request $request)
    {
        try {
            $validationRules = [
                'email' => 'required|string',
                'token' => 'required|string'
            ];
            $customMessages = [
                'email.required' => 'Email is required',
                'email.string' => 'Email must be a string.',
                'token.required' => 'Token is required',
                'token.string' => 'Token must be a string.'
            ];
            $validator = Validator::make($request->all(), $validationRules, $customMessages);

            if ($validator->fails()) {
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => $validator->errors()->first(),
                    'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                ], ApiStatus::API_STATUS_BAD_REQUEST);
            }

            $email = $request->email;
            $token = $request->token;
            $decodedEmail = base64_decode($email);
            $decodeToken = base64_decode($token);
            $userEmail = User::getUserByEmail($decodedEmail);
            if (!$userEmail) {
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => ApiStatus::USER_NOT_FOUND,
                    'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                ], ApiStatus::API_STATUS_BAD_REQUEST);
            }
            $resetRecord = PasswordReset::getResetRequestByEmail($decodedEmail);
            if (empty($resetRecord)) {
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => ApiStatus::PASSWORD_RESET_LINK_ERROR,
                    'status_code' => ApiStatus::API_STATUS_BAD_REQUEST
                ], ApiStatus::API_STATUS_BAD_REQUEST);
            }
            if (!Hash::check($decodeToken, $resetRecord->token) || Carbon::parse($resetRecord->created_at)->addHours(EmailConfig::FORGOT_PASSWORD_VALIDITY)->isPast()) {
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => ApiStatus::PASSWORD_RESET_LINK_ERROR,
                    'status_code' => ApiStatus::API_STATUS_BAD_REQUEST
                ], ApiStatus::API_STATUS_BAD_REQUEST);
            }
            return response()->json([
                'status' => ApiStatus::SUCCESS,
                'message' => ApiStatus::PASSWORD_RESET_LINK_VALID,
                'status_code' => ApiStatus::API_STATUS_SUCCESS
            ], ApiStatus::API_STATUS_SUCCESS);
        } catch (Exception $e) {
            Log::debug("Exception occurred while verifying password link.");
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
     * Handle the submission of the Reset Password request.
     * @param new password and confirmation, along with the email and token.
     */
    public function submitResetPassword(Request $request)
    {
        $revisionId = time();
        try {
            $validationRules = [
                'email' => 'required|string',
                'token' => 'required|string',
                'password' => 'required|string|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{8,10}$/',
                'password_confirmation' => 'required|string|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{8,10}$/'
            ];
            $customMessages = [
                'email.required' => 'Email is required',
                'email.string' => 'Email must be a string.',
                'token.required' => 'Token is required',
                'token.string' => 'Token must be a string.',
                'password.required' => 'New Password is required',
                'password.regex' => 'New password must contain minimum 8 and maximum 10 characters, at least one uppercase letter, one lowercase letter, one number and one special character.',
                'password.confirmed' => 'New password and confirm password do not match',
                'password_confirmation.required' => 'Confirmed password is required.',
                'password_confirmation.regex' => 'Confirmed password must contain minimum 8 and maximum 10 characters, at least one uppercase letter, one lowercase letter, one number, and one special character.',
            ];
            $validator = Validator::make($request->all(), $validationRules, $customMessages);

            if ($validator->fails()) {
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => $validator->errors()->first(),
                    'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                ], ApiStatus::API_STATUS_BAD_REQUEST);
            }
            $decodedEmail = base64_decode($request->email);
            $decodeToken = base64_decode($request->token);
            $user = User::getUserByEmail($decodedEmail);
            if (!$user) {
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => ApiStatus::USER_NOT_FOUND,
                    'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                ], ApiStatus::API_STATUS_BAD_REQUEST);
            }
            $resetRecord = PasswordReset::getResetRequestByEmail($decodedEmail);
            if (!$resetRecord) {
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => ApiStatus::INVALID_TOKEN,
                    'status_code' => ApiStatus::API_STATUS_BAD_REQUEST,
                ], ApiStatus::API_STATUS_BAD_REQUEST);
            }
            if (!Hash::check($decodeToken, $resetRecord->token) || Carbon::parse($resetRecord->created_at)->addHours(EmailConfig::FORGOT_PASSWORD_VALIDITY)->isPast()) {
                return response()->json([
                    'status' => ApiStatus::FAILURE,
                    'message' => ApiStatus::PASSWORD_RESET_LINK_ERROR,
                    'status_code' => ApiStatus::API_STATUS_BAD_REQUEST
                ], ApiStatus::API_STATUS_BAD_REQUEST);
            }
            $originalAttributes = $user->getAttributes();
            $user->password = Hash::make($request->password);
            $user->save();
            User::updatePasswordUpdatedAtByUser($decodedEmail);
            $this->loggingService->logChange($user, 'updated', $revisionId, null, $user->id, $originalAttributes);
            PasswordReset::deleteResetRequestByEmail($decodedEmail);
            return response()->json([
                'status' => ApiStatus::SUCCESS,
                'message' => ApiStatus::PASSWORD_RESET_SUCCESS,
                'status_code' => ApiStatus::API_STATUS_SUCCESS,
            ], ApiStatus::API_STATUS_SUCCESS);
        } catch (Exception $e) {
            Log::debug("Exception occurred while resetting Password.");
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
