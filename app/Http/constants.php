<?php

final class Templates{
    public const USER_REGISTRATION_TEMPLATE = "1";
    public const USER_FORGOT_PASSWORD_TEMPLATE = "2";
    public const USER_EMAIL_VERIFICATION = "3";
}

final class ApiStatus{
    public const FAILURE = "FAILURE";
    public const SUCCESS = "SUCCESS";
    public const TOKEN_EXPIRED = "Token expired. Please login";
    public const UN_AUTHORIZE = "Un-authorized access";
    public const VALIDATION_ERROR = "Validation error";
    public const INVALID_CREDENTIALS = "Username/Password do not match";
    public const ACCOUNT_BLOCKED = "Your account is in-active, please contact admin for more information.";
    public const REGISTRATION_SUCCESS = "Registraion completed. Please verify your email.";
    public const INTERNAL_ERROR = "Internal server error. Try after some time.";
    public const LOGIN_SUCCESS = "Login successful";
    public const EMAIL_TEMPLATE_NOT_FOUND = "Unable to send email at the moment. Please try after some time.";
    public const EMAIL_VERIFICATION_LINK_ERROR = "The link provided is either invalid or has expired. Please request a new verification link.";
    public const EMAIL_VERIFICATION_FAILED = "Something went wrong while verifying email.";
    public const EMAIL_VERIFICATION_ALREADY = "Duplicate request. Email verification completed.";
    public const PASSWORD_RESET_ALREADY = "Duplicate request. Password reset completed.";
    public const CURRENT_PASSWORD_INCORRECT='Current password is incorrect.';
    public const PASSWORD_CHANGED_SUCCESS = 'Password changed successfully.';
    public const TOKEN_INVALID="Token is not valid.";
    public const USER_UNAUTHORIZE="Failed to authenticate user";
    public const USER_NOT_FOUND="User not found.Please register";
    public const NEW_CURRENT_PASSWORD_SAME="New Password cannot be same as your current password.";
    public const USER_FOUND="User Successfully fetched";
    public const PASSWORD_RESET_LINK_ERROR = "The link provided is either invalid or has expired. Please request a new verification link.";
    public const PASSWORD_RESET_SUCCESS = 'Password has been changed!';
    public const INVALID_TOKEN = 'Invalid token!';
    public const NO_SPRINT_FOUND = "No sprint data found for given project";
    public const API_SUCCESS_MESSAGE = "Data found";
    public const GOOGLE_SHEET_DATA_NOT_CONFIGURED = "No google sheet data configured for given project.";
    public const GOOGLE_SHEET_DATA_REFRESHED = "Successfully refreshed google sheet data";
    public const GOOGLE_SHEET_DATA_READ_CHANGE_LOG_DATA = "Successfully read change log data";
    public const GOOGLE_SHEET_DATA_READ_CHANGE_LOG_ERROR_DATE = "Invalid date received while reading change log data";
    public const GOOGLE_SHEET_DATA_READ_NO_CHANGE_LOG_DATA = "No change log data found";
    public const GOOGLE_SHEET_DATA_READ_ERROR = "Keys count do not match, while reading from sheet.";
    public const GOOGLE_SHEET_INVALID_DATE_INFO = "No project dates information is configured.";
    public const USER_UPDATED="User Successfully updated";
    public const PASSWORD_RESET_LINK_SENT="Password reset link sent to your email";
    public const UNABLE_TO_SEND_RESET_LINK="Unable to send reset link";    
    public const INVALID_METHOD_ACCESS = "Method not allowed.";
    public const NO_PHASES_FOUND = "No phases data found for given project";
    public const NO_IMPORT_DATA_FOUND = "No import data found for given project";
    public const FUTURE_SEARCH_DATE_ERROR = "Invalid search date received";
    public const PASSWORD_RESET_LINK_VALID='Password reset link is valid.';
    public const EMAIL_TOKEN_REQUIRED='Email and token are required.';
    public const UNAUTHORIZE_ACTION="Unauthorized to perform this action";
    public const NEXT_FRIDAY_REQUEST='You can not see the data for next friday.';
    public const NO_IMPORT_HISTORY_FOUND='No import history found for given project';
    public const NO_SPRINT_DAYS_DATA_FOUND='Value for no of days in sprint is not found for given project';
    public const PROJECT_ADD='Project added successfully';
    public const PROJECT_ALREADY_EXISTED="This project title is already taken";
    public const PROJECT_LIST='Project list fetched successfully';
    public const NO_PROJECT_DATA_FOUND="No project data found";
    
    public const API_STATUS_SUCCESS = 200;
    public const API_STATUS_CREATED = 201;
    public const API_STATUS_BAD_REQUEST = 400;
    public const API_STATUS_UN_AUTHORIZED = 401;
    public const API_STATUS_INTERNAL_SERVER_ERROR = 500;
    public const RANDOM_STR_TOKEN=60;
    public const API_STATUS_INVALID_HTTP_METHOD = 405;
}

final class SprintStatus{
    public const STATUS_COMPLETED='Completed';
    public const STATUS_USER_ACCEPTANCE='User acceptance';
    public const STATUS_TESTING_PHASES='Testing phase';
    public const STATUS_IN_PROGRESS='In Progress';
    public const STATUS_PENDING='Pending';
    public const STATUS_BLOCKED="Blocked";
    public const STATUS_REWORK='Rework';
    public const STATUS_DENIED='Denied';
    public const STATUS_INCONSISTENT='Inconsistent';
    public const STATUS_REPLACED='Replaced';
    public const DEFAULT_STATUS_WEIGHT=1.0;
}

final class EmailConfig{
    public const FORGOT_PASSWORD_VALIDITY=1;
    public const EMAIL_VERIFICATION_VALIDITY=24;
}

final class UserRoles{
    public const SUPER_ADMIN = "Super Admin";
    public const SUPER_ADMIN_ID = 1;
}

final class SuperAdminDetails{
    public const SUPER_ADMIN_NAME="Admin";
    public const SUPER_ADMIN_EMAIL="admin@bs.com";
    public const SUPER_ADMIN_PASSWORD="Pmo@1234";
}

final class ProjectsDetails{
    public const PROJECT_CODE="PMOID001";
    public const NO_OF_SPRINTS=12;
    public const NO_OF_PAHSES=12;
    public const NO_OF_RESOURCE_GROUP=12;
}

final class DefaultExcelColumns{

    public const YES = "Yes";
    public const NO = "No";
    public const DEFAULT_SPRINT_SIZE = 0.00;
    public const DEFAULT_COMPLEXITY = 0;
    public const DEFAULT_PERCENTAGE = 0.00;
    public const DEFAULT_ROW_NUMBER = 2;
    public const TABLE_SPRINTS = "sprints";
    public const TABLE_SPRINT_DATA = "sprint_data";
    public const SEARCH_TEXT_PAHSE = "phase_";
    public const SEARCH_TEXT_TEAM = "group_";
    public const SEARCH_TEXT_USER_STORIES = "user_story_";
    public const CHANGE_LOG_SHEET_NAME = "Change Log";

    public const TOTAL_COLUMNS = 107;
    public const ID = "id";
    public const SPRINT_ITEM = "sprint_item";
    public const COMPLEXITY = "complexity";
    public const RESOURCES = "resources";
    public const SPRINT_ESTIMATION = "sprint_estimation";
    public const SPRINT_ADJUSTMENTS = "sprint_adjustments";
    public const SPRINT_SIZE = "sprint_size";
    public const SPRINT_SIZE_ADHOC_TASK = "sprint_size_adhoc_task";
    public const FINAL_SPRINT_SIZE = "final_sprint_size";
    public const SPRINT_COMMENT = "sprint_comment";
    public const STATUS = "status";
    public const MOBILE_COMPLETION_STATUS = "mobile_completion_status";
    public const PHASE_1 = "phase_1";
    public const PHASE_2 = "phase_2";
    public const PHASE_3 = "phase_3";
    public const PHASE_4 = "phase_4";
    public const PHASE_5 = "phase_5";
    public const ALL_PROJECT = "all_project";
    public const SERIAL_NUMBER = "serial_number";
    public const GROUP_1 = "group_1";
    public const GROUP_2 = "group_2";
    public const GROUP_3 = "group_3";
    public const GROUP_4 = "group_4";
    public const GROUP_5 = "group_5";
    public const GROUP_6 = "group_6";
    public const GROUP_7 = "group_7";
    public const GROUP_8 = "group_8";
    public const GROUP_9 = "group_9";
    public const GROUP_10 = "group_10";
    public const GROUP_11 = "group_11";
    public const GROUP_12 = "group_12";
    public const GROUP_13 = "group_13";
    public const GROUP_14 = "group_14";
    public const GROUP_15 = "group_15";
    public const GROUP_16 = "group_16";
    public const GROUP_17 = "group_17";
    public const GROUP_18 = "group_18";
    public const GROUP_19 = "group_19";
    public const GROUP_20 = "group_20";
    public const GROUP_21 = "group_21";
    public const GROUP_22 = "group_22";
    public const GROUP_23 = "group_23";
    public const GROUP_24 = "group_24";
    public const NEW_SR_NO = "new_sr_no";
    public const NEW_MODULE_SCREENS = "new_module_screens";
    public const SPRINT_NUMBER = "sprint_number";
    public const TEAM_NAME = "team_name";
    public const MODULE_SCREENS = "module_screens";
    public const REQUIREMENT = "requirement";
    public const ACCESS_REQUIRED_FOR_USER_ROLE = "access_required_for_user_role";
    public const PRIORITY = "priority";
    public const EXTRA_COLUMN_1 = "extra_column_1";
    public const EXTRA_COLUMN_2 = "extra_column_2";
    public const WEB_BACKEND = "web_backend";
    public const PRIORITY_BY_STAKEHOLDER = "priority_by_stakeholder";
    public const PRIORITY_BS = "priority_bs";
    public const RESPONSIBLE_OWNER = "responsible_owner";
    public const RESPONSIBLE_TEAM = "responsible_team";
    public const OWNER_OR_TEAM_COMMENTS = "owner_or_team_comments";
    public const COMMENTS_POST_DISCUSSION_WITH_STAKEHOLDER = "comments_post_discussion_with_stakeholder";
    public const DERIVED_PRIORITY = "derived_priority";
    public const MEMBER_SIZE = "member_size";
    public const USER_STORY_1 = "user_story_1";
    public const USER_STORY_2 = "user_story_2";
    public const USER_STORY_3 = "user_story_3";
    public const USER_STORY_4 = "user_story_4";
    public const USER_STORY_5 = "user_story_5";
    public const USER_STORY_6 = "user_story_6";
    public const USER_STORY_7 = "user_story_7";
    public const USER_STORY_8 = "user_story_8";
    public const USER_STORY_9 = "user_story_9";
    public const USER_STORY_10 = "user_story_10";
    public const USER_STORY_11 = "user_story_11";
    public const USER_STORY_12 = "user_story_12";
    public const USER_STORY_13 = "user_story_13";
    public const USER_STORY_14 = "user_story_14";
    public const USER_STORY_15 = "user_story_15";
    public const USER_STORY_16 = "user_story_16";
    public const USER_STORY_17 = "user_story_17";
    public const IN_APP_COMMUNICATIONS = "in_app_communications";
    public const CHANGES_DONE_IN_ACTUAL_MASTER = "changes_done_in_actual_master";
    public const CHANGE_TYPE = "change_type";
    public const DESCRIPTION_OF_CHANGES = "description_of_changes";
    public const CHANGE_DONE_ON_DATE = "change_done_on_date";
    public const WHERE_THESE_TASKS_HAVE_BEEN_ADDED = "where_these_tasks_have_been_added";
    public const EXTRA_COLUMN_3 = "extra_column_3";
    public const EXTRA_COLUMN_4 = "extra_column_4";
    public const INCOMPLETE_BASIS_WEIGHTAGES = "incomplete_basis_weightages";
    public const COMPLETED_BASIS_ACTUAL_PROGRESS = "completed_basis_actual_progress";
    public const COMPLETE_STATUS_PERCENTAGE = "complete_status_percentage";
    public const INCOMPLETE_STATUS_PERCENTAGE = "incomplete_status_percentage";
    public const INCOMPLETE_BASIS_ACTUAL = "incomplete_basis_actual";
    public const INCOMPLETE_SPRINT_SIZE_BASIS_WEIGHTAGE = "incomplete_sprint_size_basis_weightage";
    public const FEATURE = "feature";
    public const DEMO_GIVEN_YES_OR_NO = "demo_given_yes_or_no";
    public const DEMO_GIVEN_ON_YYYYMMDD = "demo_given_on_yyyymmdd";
    public const APPROVED_BY = "approved_by";
    public const APPROVED_ON_YYYYMMDD = "approved_on_yyyymmdd";
    public const FEEDBACK_BY_STSAKEHOLDER = "feedback_by_stsakeholder";
    public const STAKEHOLDER_COMMENTS = "stakeholder_comments";
    public const SCREENSHOT_USER_MANUAL_LINK_1 = "screenshot_user_manual_link_1";
    public const SCREENSHOT_USER_MANUAL_LINK_2 = "screenshot_user_manual_link_2";
    public const SCREENSHOT_USER_MANUAL_LINK_3 = "screenshot_user_manual_link_3";
    public const SCREENSHOT_USER_MANUAL_LINK_4 = "screenshot_user_manual_link_4";
    public const SCREENSHOT_USER_MANUAL_LINK_5 = "screenshot_user_manual_link_5";
    public const USER_ACCEPTANCE_DEMO_STATUS = "user_acceptance_demo_status";
    public const WEB_LOGIN_URL_CREDENTIALS = "web_login_url_credentials";

    public static function getAllKeys(){

        return [
            [ "title"=>"ID", "key"=>"id" ],
            [ "title"=>"Sprint Item", "key"=>"sprint_item" ],
            [ "title"=>"Complexity", "key"=>"complexity" ],
            [ "title"=>"Resources", "key"=>"resources" ],
            [ "title"=>"Sprint Estimation", "key"=>"sprint_estimation" ],
            [ "title"=>"Sprint Adjustments", "key"=>"sprint_adjustments" ],
            [ "title"=>"Sprint Size", "key"=>"sprint_size" ],
            [ "title"=>"Sprint Size (ADHOC Tasks)", "key"=>"sprint_size_adhoc_task" ],
            [ "title"=>"Final Sprint Size", "key"=>"final_sprint_size" ],
            [ "title"=>"Sprint Comment", "key"=>"sprint_comment" ],
            [ "title"=>"Status", "key"=>"status" ],
            [ "title"=>"Mobile Completion Status", "key"=>"mobile_completion_status" ],

            [ "title"=>"All Project", "key"=>"all_project" ],
            [ "title"=>"Serial Number", "key"=>"serial_number" ],
 
            [ "title"=>"New Sr. No", "key"=>"new_sr_no" ],
            [ "title"=>"New Module / Screens", "key"=>"new_module_screens" ],
            [ "title"=>"Sprint Number", "key"=>"sprint_number" ],
            [ "title"=>"Team Name", "key"=>"team_name" ],
            [ "title"=>"Module / Screens", "key"=>"module_screens" ],
            [ "title"=>"Requirement", "key"=>"requirement" ],
            [ "title"=>"Access required for user role", "key"=>"access_required_for_user_role" ],
            [ "title"=>"Priority", "key"=>"priority" ],
            [ "title"=>"Extra Column 1", "key"=>"extra_column_1" ],
            [ "title"=>"Extra Column 2", "key"=>"extra_column_2" ],
            [ "title"=>"Web / Backend", "key"=>"web_backend" ],
            [ "title"=>"Priority (By Stakeholder)", "key"=>"priority_by_stakeholder" ],
            [ "title"=>"Priority (BS)", "key"=>"priority_bs" ],
            [ "title"=>"Responsible Owner", "key"=>"responsible_owner" ],
            [ "title"=>"Responsible Team", "key"=>"responsible_team" ],
            [ "title"=>"Owner OR Team Comments", "key"=>"owner_or_team_comments" ],
            [ "title"=>"Comments (post discussion with Stakeholder)", "key"=>"comments_post_discussion_with_stakeholder" ],
            [ "title"=>"Derived Priority", "key"=>"derived_priority" ],
            [ "title"=>"Member Size", "key"=>"member_size" ],
  
            [ "title"=>"In App Communications", "key"=>"in_app_communications" ],
            [ "title"=>"Changes done in actual master(Yes or No)", "key"=>"changes_done_in_actual_master" ],
            [ "title"=>"Change Type", "key"=>"change_type" ],
            [ "title"=>"Description of changes", "key"=>"description_of_changes" ],
            [ "title"=>"Change done on Date", "key"=>"change_done_on_date" ],
            [ "title"=>"Where these tasks have been added", "key"=>"where_these_tasks_have_been_added" ],
            [ "title"=>"Extra Column 3", "key"=>"extra_column_3" ],
            [ "title"=>"Extra Column 4", "key"=>"extra_column_4" ],
            [ "title"=>"Incomplete Basis Weightages", "key"=>"incomplete_basis_weightages" ],
            [ "title"=>"Completed Basis Actual Progress", "key"=>"completed_basis_actual_progress" ],
            [ "title"=>"Complete Status Percentage", "key"=>"complete_status_percentage" ],
            [ "title"=>"Incomplete Status Percentage", "key"=>"incomplete_status_percentage" ],
            [ "title"=>"Incomplete basis Actual", "key"=>"incomplete_basis_actual" ],
            [ "title"=>"Incomplete Sprint size basis weightage", "key"=>"incomplete_sprint_size_basis_weightage" ],
            [ "title"=>"Feature", "key"=>"feature" ],
            [ "title"=>"Demo Given?", "key"=>"demo_given_yes_or_no" ],
            [ "title"=>"Demo given on (YYYYMMDD)", "key"=>"demo_given_on_yyyymmdd" ],
            [ "title"=>"Approved By", "key"=>"approved_by" ],
            [ "title"=>"Approved on (YYYYMMDD)", "key"=>"approved_on_yyyymmdd" ],
            [ "title"=>"Feedback by Stakeholder", "key"=>"feedback_by_stsakeholder" ],
            [ "title"=>"Stakeholder Comments", "key"=>"stakeholder_comments" ],
            [ "title"=>"Screenshot/User manual link", "key"=>"screenshot_user_manual_link_1" ],
            [ "title"=>"Screenshot/User manual link", "key"=>"screenshot_user_manual_link_2" ],
            [ "title"=>"Screenshot/User manual link", "key"=>"screenshot_user_manual_link_3" ],
            [ "title"=>"Screenshot/User manual link", "key"=>"screenshot_user_manual_link_4" ],
            [ "title"=>"Screenshot/User manual link", "key"=>"screenshot_user_manual_link_5" ],
            [ "title"=>"User Acceptance Demo Status(Done/Pending/NA)", "key"=>"user_acceptance_demo_status" ],
            [ "title"=>"Web Login URL & Credentials", "key"=>"web_login_url_credentials" ]
        ];
    }

    public static function getDatabaseDetail(){

        return [
            [ "table"=>"sprints", "column"=>"task_id", "key"=>"id" ],
            [ "table"=>"sprints", "column"=>"title", "key"=>"sprint_item" ],
            [ "table"=>"sprints", "column"=>"complexity", "key"=>"complexity" ],
            [ "table"=>"sprints", "column"=>"resources", "key"=>"resources" ],
            [ "table"=>"sprints", "column"=>"sprint_estimation", "key"=>"sprint_estimation" ],
            [ "table"=>"sprints", "column"=>"sprint_adjustment", "key"=>"sprint_adjustments" ],
            [ "table"=>"sprints", "column"=>"sprint_size", "key"=>"sprint_size" ],
            [ "table"=>"sprints", "column"=>"sprint_adhoc_task", "key"=>"sprint_size_adhoc_task" ],
            [ "table"=>"sprints", "column"=>"sprint_total", "key"=>"final_sprint_size" ],
            [ "table"=>"sprints", "column"=>"comments", "key"=>"sprint_comment" ],
            [ "table"=>"sprints", "column"=>"status_id", "key"=>"status" ],
            [ "table"=>"sprints", "column"=>"mobile_completion_status", "key"=>"mobile_completion_status" ],
            [ "table"=>"sprints", "column"=>"sprint_number", "key"=>"sprint_number" ],
            [ "table"=>"sprints", "column"=>"requirement", "key"=>"requirement" ],
            [ "table"=>"sprints", "column"=>"priority_id", "key"=>"priority" ],
            [ "table"=>"sprints", "column"=>"priority_id_stakeholder", "key"=>"priority_by_stakeholder" ],


            [ "table"=>"sprint_data", "column"=>"all_project", "key"=>"all_project" ],
            [ "table"=>"sprint_data", "column"=>"serial_number", "key"=>"serial_number" ],
            [ "table"=>"sprint_data", "column"=>"new_sr_no", "key"=>"new_sr_no" ],
            [ "table"=>"sprint_data", "column"=>"new_module_screens", "key"=>"new_module_screens" ],
            [ "table"=>"sprint_data", "column"=>"team_name", "key"=>"team_name" ],
            [ "table"=>"sprint_data", "column"=>"module_screens", "key"=>"module_screens" ],
            [ "table"=>"sprint_data", "column"=>"access_required_for_role", "key"=>"access_required_for_user_role" ],
            [ "table"=>"sprint_data", "column"=>"extra_column_one", "key"=>"extra_column_1" ],
            [ "table"=>"sprint_data", "column"=>"extra_column_two", "key"=>"extra_column_2" ],
            [ "table"=>"sprint_data", "column"=>"web_backend", "key"=>"web_backend" ],
            [ "table"=>"sprint_data", "column"=>"priority_bs", "key"=>"priority_bs" ],
            [ "table"=>"sprint_data", "column"=>"responsible_owner", "key"=>"responsible_owner" ],
            [ "table"=>"sprint_data", "column"=>"responsible_team", "key"=>"responsible_team" ],
            [ "table"=>"sprint_data", "column"=>"owner_or_team_comments", "key"=>"owner_or_team_comments" ],
            [ "table"=>"sprint_data", "column"=>"comments_post_discussion_with_stakeholder", "key"=>"comments_post_discussion_with_stakeholder" ],
            [ "table"=>"sprint_data", "column"=>"derived_priority", "key"=>"derived_priority" ],
            [ "table"=>"sprint_data", "column"=>"member_size", "key"=>"member_size" ],
            [ "table"=>"sprint_data", "column"=>"in_app_communications", "key"=>"in_app_communications" ],
            [ "table"=>"sprint_data", "column"=>"changes_done_in_actual_master", "key"=>"changes_done_in_actual_master" ],
            [ "table"=>"sprint_data", "column"=>"change_type", "key"=>"change_type" ],
            [ "table"=>"sprint_data", "column"=>"description_of_changes", "key"=>"description_of_changes" ],
            [ "table"=>"sprint_data", "column"=>"change_done_on_date", "key"=>"change_done_on_date" ],
            [ "table"=>"sprint_data", "column"=>"where_these_tasks_have_been_added", "key"=>"where_these_tasks_have_been_added" ],
            [ "table"=>"sprint_data", "column"=>"extra_column_three", "key"=>"extra_column_3" ],
            [ "table"=>"sprint_data", "column"=>"extra_column_four", "key"=>"extra_column_4" ],
            [ "table"=>"sprint_data", "column"=>"incomplete_basis_weightages", "key"=>"incomplete_basis_weightages" ],
            [ "table"=>"sprint_data", "column"=>"completed_basis_actual_progress", "key"=>"completed_basis_actual_progress" ],
            [ "table"=>"sprint_data", "column"=>"complete_status_percentage", "key"=>"complete_status_percentage" ],
            [ "table"=>"sprint_data", "column"=>"incomplete_status_percentage", "key"=>"incomplete_status_percentage" ],
            [ "table"=>"sprint_data", "column"=>"incomplete_basis_actual", "key"=>"incomplete_basis_actual" ],
            [ "table"=>"sprint_data", "column"=>"incomplete_sprint_size_basis_weightage", "key"=>"incomplete_sprint_size_basis_weightage" ],
            [ "table"=>"sprint_data", "column"=>"feature", "key"=>"feature" ],
            [ "table"=>"sprint_data", "column"=>"demo_given", "key"=>"demo_given_yes_or_no" ],
            [ "table"=>"sprint_data", "column"=>"demo_given_on", "key"=>"demo_given_on_yyyymmdd" ],
            [ "table"=>"sprint_data", "column"=>"approved_by", "key"=>"approved_by" ],
            [ "table"=>"sprint_data", "column"=>"approved_on", "key"=>"approved_on_yyyymmdd" ],
            [ "table"=>"sprint_data", "column"=>"feedback_by_stakeholder", "key"=>"feedback_by_stsakeholder" ],
            [ "table"=>"sprint_data", "column"=>"stakeholder_comments", "key"=>"stakeholder_comments" ],
            [ "table"=>"sprint_data", "column"=>"screenshot_link_one", "key"=>"screenshot_user_manual_link_1" ],
            [ "table"=>"sprint_data", "column"=>"screenshot_link_two", "key"=>"screenshot_user_manual_link_2" ],
            [ "table"=>"sprint_data", "column"=>"screenshot_link_three", "key"=>"screenshot_user_manual_link_3" ],
            [ "table"=>"sprint_data", "column"=>"screenshot_link_four", "key"=>"screenshot_user_manual_link_4" ],
            [ "table"=>"sprint_data", "column"=>"screenshot_link_five", "key"=>"screenshot_user_manual_link_5" ],
            [ "table"=>"sprint_data", "column"=>"uat_demo_status", "key"=>"user_acceptance_demo_status" ],
            [ "table"=>"sprint_data", "column"=>"web_login_details", "key"=>"web_login_url_credentials" ]
        ];
    }
}

final class WeekDays{
    public const MONDAY = "Monday";
    public const SATURDAY = "Saturday";
}

final class Users{
    public const SUPER_ADMIN = 1;
}

final class SprintSummary{
    public const USER_ACCEPTANCE = "User acceptance";
    public const ONE_SPRINT_DURATION_DAYS = 10;
}

