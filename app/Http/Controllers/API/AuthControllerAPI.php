<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AccountState;
use App\Models\AccountStatusEnum;
use App\Models\AppUser;
use App\Models\AuthTypeEnum;
use App\Models\DeviceDetails;
use App\Models\DeviceFCMToken;
use App\Models\UserNotificationSetting;
use App\Models\UserRoleEnum;
use App\Traits\NotificationTrait;
use App\Traits\TokenGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuthControllerAPI extends Controller
{
    use TokenGenerator;
    use NotificationTrait;

    //
    public function authentication(Request $request){
        try {

            $validaor = Validator::make($request->all(), [
                'name' => 'required|string',
                'email' => 'required|email',
                'auth_type' => ['required', Rule::in(array_map(fn($case) => $case->value, AuthTypeEnum::cases()))],
                'open_id' => 'required|string',
                //'role' => 'required|in:Customer,Chef,Admin',
                'role' => ['required', Rule::in(array_map(fn($case) => $case->value, UserRoleEnum::cases()))],
            ]);

            if($validaor->fails()){
                return response()->json([
                    'message' => 'Invalid data.',
                    'status_code' => 422,
                    'error' => $validaor->errors(),
                ], 422);
            }

            //$user->role = UserRoleEnum::Admin; // Assigning enum value

            $userId = Str::uuid();
            $name = $request->name;
            $email = $request->email;
            $authType = $request->auth_type;
            $openId = $request->open_id;
            $role = $request->role;
            $avatar = $request->avatar??"/images/profile/default.png";
            $token = $this->generateToken(101);
            $access_token = $this->generateToken(101);

            $user = AppUser::where('open_id', $openId)
                ->first();

            if ($user) {
                //User exists, update the user information
                $user->auth_type = $authType;
                $user->save();

                return response()->json([
                    'message' => 'User authenticated successfully.',
                    'status_code' => 200,
                    'user' => $user,
                ], 200);
            }

            $status = AccountStatusEnum::Active->value;
            if($role != UserRoleEnum::Customer->value){
                $status = AccountStatusEnum::PendingVerification->value;
            }

            //user does not exist, create a new user
            $user = AppUser::create([
                'user_id' => $userId,
                'name' => $name,
                'email'=> $email,
                'user_type'=> $authType,
                'open_id' => $openId,
                'avatar'=> $avatar,
                'role'=> $role,
                'token'=> $token,
                'access_token' => $access_token,
            ]);

            // User Notification Setting
            UserNotificationSetting::create([
                'id' => Str::uuid(),
                'open_id' => $openId,
                'email' => 'true',
                'sms' => 'true',
                'in_app' => 'true',
            ]);

            $accountState = AccountState::create([
                'state_id' => Str::uuid(),
                'status' => $status,
                'description' => "User $name has been created.",
                'open_id' => $openId,
            ]);


            // Send notification
            $subject = "Welcome aboard $name.";
             $body = "";
            if ($user_type = "Chef") {
                $body = "Welcome to Recipe App, $name!\n We're excited to have you as a Chef. Share your culinary skills and connect with food lovers around the world.\n Happy cooking!";
            } 
            elseif ($user_type == "Admin") {
                $body = "Welcome to Recipe App, $name!\n We're thrilled to have you on board as an Admin. Your expertise will help us create a better experience for our users.\n Happy cooking!";
            }
            elseif ($user_type == " Customer") {
                $body = "Welcome to Recipe App, $name!\n We're excited to have you with us. Start exploring now and enjoy exclusive recipes just for you!\n Happy cooking!";
            }

            $openId = $user->open_id;

            $data = [
                "subject" => $subject,
                "message" => $body,
                "event_type" => "welcome",
            ];
            $this->sendNotification($openId, false, true, true,  $data); // use NotificationTrait;

    
            return response()->json([
                "message" => "Registration successful.",
                "status_code" => 200,
                "user" => $user,
                "account_state" => $accountState,
            ], 200);

        } catch (\Exception $e) {
            Log::info("Error: $e");
            return response()->json([
                'message' => 'An error occurred during authentication.',
                'error' => $e->getMessage(),
                'status_code' => $e->getCode(),
            ], 500);
        }
    }

    public function saveDeviceDetails(Request $request){
        try {

            $validator = Validator::make($request->all(), [
                'device_id' => 'required',
                'name' => 'required',
                'model' => 'required',
                'localized_model' => 'required',
                'system_name' => 'required',
                'version' => 'required',
                'type' => 'required',
            ]);

            if ($validator->fails()){
                return response()->json([
                    "message" => "Invalid data",
                    "status_code" => 422,
                    "error" => $validator->errors()
                ]);
            }

            $id = Str::uuid();
            $device_id = $request->device_id;
            $name = $request->name;
            $model = $request->model;
            $localized_model = $request->localized_model;
            $system_name = $request->system_name;
            $version = $request->version;
            $type = $request->type;
            $open_id = $request->open_id ?? "";

            $queryData = [
                'open_id' => $open_id,
                "device_id" => $device_id
             ];

             $createData = [
                'id' => $id,
                'device_id' => $device_id,
                'name' => $name,
                'model' => $model,
                'system_name' => $system_name,
                'version' => $version,
                'type' => $type,
                'localized_model' => $localized_model,
                'open_id' => $open_id,
             ];


            $deviceDetails =  DeviceDetails::updateOrCreate(
                $queryData,
                $createData
            );

           
            if(!$deviceDetails){
                return response()->json([
                    "message" => "Failed to save device details.",
                    "status_code" => 500,
                ]);
            }

            return response()->json([
                "message" => "Device details saved successfully.",
                "status_code" => 200,
                 //"deviceDetails" => $deviceDetails
            ]);

        }
        catch(\Exception $e) {
            Log::info("Error: $e");
            return response()->json([
                "message" => "Saving Device details failed.",
                "error" => $e->getMessage(),
                "status_code" => $e->getCode(),
            ], 200);
        }
    }

    public function updateAppUser(Request $request){
        try {
             
            $validaor = Validator::make($request->all(), [
                'name' => 'required|string',
                'email' => 'required|email',
                'auth_type' => 'required|in:Email,Google,Apple,Facebook,Twitter',
                'open_id' => 'required|string',
                'role' => 'required|in:Customer,Chef,Admin',
            ]);

            if($validaor->fails()){
                return response()->json([
                    'message' => 'Invalid data.',
                    'status_code' => 422,
                    'error' => $validaor->errors(),
                ]);
            }

            //$user->role = UserRoleEnum::Admin; // Assigning enum value

            $name = $request->name;
            $email = $request->email;
            $role = $request->role;
            $openId = $request->open_id;
            $role = $request->role;
            $auth_type = $request->auth_type;
            $avatar = $request->avatar??"/images/profile/default.png";
            $gender = $request->gender;
            $date_of_birth = $request->date_of_birth;
            $phone_complete = $request->phone_complete;
            $country_code = $request->country_code;
            $account_status = $request->account_status;


            //$token = $this->generateToken(101);
            //$access_token = $this->generateToken(101);

            $user = AppUser::where('open_id', $openId)
                ->first();

            if(!$user){
                return response()->json([
                    'message' => 'User not found.',
                    'status_code' => 404,
                    'error'=> 'No User found with the provided ID.',
                ]);
            }
    

            $user->name = $name;
            if($user->role == UserRoleEnum::Admin){
                $user->email = $email;
                $user->role = $role;
                $user->account_status = $account_status;
            }
            $user->avatar = $avatar;
            $user->auth_type = $auth_type;
            $user->gender = $gender;
            $user->date_of_birth = $date_of_birth;
            $user->phone_complete = $phone_complete;
            $user->country_code = $country_code;

            $user->save();

            return response()->json([
                'message' => 'User updated successfully.',
                'status_code' => 200,
                'user' => $user,
            ], 200);

        } catch (\Exception $e) {
            Log::error("Error: {$e->getMessage()}");
            return response()->json([
                'message' => 'An error occurred while updating User.',
                'error' => $e->getMessage(),
                'status_code' => $e->getCode(),
            ], 500);
        }
    }

    public function deleteAccount(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'openId' => 'required',
            ]);

            if ($validator->fails()){
                return response()->json([
                    "message" => "Invalid data",
                    "status_code" => 422,
                    "error" => $validator->errors()
                ]);
            }

            $open_id = $request->openId;
            $deviceId = $request->deviceId ?? "";

            $appUser = AppUser::where('open_id', $open_id)->first();

            if(!$appUser){
                return response()->json([
                    "message" => "User not found.",
                    "status_code" => 404,
                ]);
            }

            // Send email notification
            $subject = "Account deletion.";
            $name = $appUser->name;
            $email = $appUser->email;
            $openId = $appUser->open_id;

            $isDeleted =  $appUser->delete();

            if ( !$isDeleted) {
                $body = "Dear $name, Your attempt to delete your account failed. Please contact administrator for further instructions.";

                $data = [
                    "subject" => $subject,
                    "message" => $body,
                    "event_type" => "delete_account",
                ];
                $this->sendNotification($openId, false, true, true,  $data); // use NotificationTrait;

                return response()->json([
                    "message" => "Failed to delete account.",
                    "status_code" => 500,
                ]);
            }

            $body = "Dear $name, Your Style Yangu account has been deleted successfully.";

            $data = [
                "subject" => $subject,
                "message" => $body,
                "event_type" => "delete_account",
            ];

            //$this->sendNotification($openId, false, true, false,  $data); // use NotificationTrait;
            $this->sendEmailTrait($email, $subject, $body, $name);

            return response()->json([
                "message" => "Account deleted successfully.",
                "status_code" => 200,
            ]);

        }
        catch(\Exception $e) {
            Log::info("Error: $e");
            return response()->json([
                "message" => "Deleting account details failed.",
                "error" => $e->getMessage(),
                "status_code" => $e->getCode(),
            ], 200);
        }
    }

    public function countryCodes(){
        /// https://gist.github.com/ally-commits/9073ff23fc7f96fab1290fdec22775bc
            /// https://gist.github.com/Goles/3196253

        try {
            $list = '[{ "code": "AD", "label": "Andorra", "phone": "376", "phoneLength": 6},
            { "code": "AE", "label": "United Arab Emirates", "phone": "971", "phoneLength": 9},
            { "code": "AF", "label": "Afghanistan", "phone": "93", "phoneLength": 9},
            { "code": "AG", "label": "Antigua and Barbuda", "phone": "1268", "phoneLength": 10},
            { "code": "AI", "label": "Anguilla", "phone": "1264", "phoneLength": 10},
            { "code": "AL", "label": "Albania", "phone": "355", "phoneLength": 9},
            { "code": "AM", "label": "Armenia", "phone": "374", "phoneLength": 6},
            { "code": "AO", "label": "Angola", "phone": "244", "phoneLength": 9},
            { "code": "AQ", "label": "Antarctica", "phone": "672", "phoneLength": 6},
            { "code": "AR", "label": "Argentina", "phone": "54", "phoneLength": 8},					
            { "code": "AS", "label": "American Samoa", "phone": "1684", "phoneLength": 10},
            { "code": "AT", "label": "Austria", "phone": "43", "phoneLength": 11},			
            { "code": "AU", "label": "Australia", "phone": "61", "suggested": true, "phoneLength": 9},
            { "code": "AW", "label": "Aruba", "phone": "297", "phoneLength": 7},
            { "code": "AX", "label": "Alland Islands", "phone": "358", "phoneLength": 10},				
            { "code": "AZ", "label": "Azerbaijan", "phone": "994", "phoneLength": 9},
            { "code": "BA", "label": "Bosnia and Herzegovina", "phone": "387", "phoneLength": 8},
            { "code": "BB", "label": "Barbados", "phone": "1246", "phoneLength": 10},
            { "code": "BD", "label": "Bangladesh", "phone": "880", "phoneLength": 10},
            { "code": "BE", "label": "Belgium", "phone": "32", "phoneLength": 9},
            { "code": "BF", "label": "Burkina Faso", "phone": "226", "phoneLength": 8},
            { "code": "BG", "label": "Bulgaria", "phone": "359", "phoneLength": 9},
            { "code": "BH", "label": "Bahrain", "phone": "973", "phoneLength": 8},
            { "code": "BI", "label": "Burundi", "phone": "257", "phoneLength": 8},
            { "code": "BJ", "label": "Benin", "phone": "229", "phoneLength": 8},
            { "code": "BL", "label": "Saint Barthelemy", "phone": "590", "phoneLength": 9},
            { "code": "BM", "label": "Bermuda", "phone": "1441", "phoneLength": 10},
            { "code": "BN", "label": "Brunei Darussalam", "phone": "673", "phoneLength": 7},
            { "code": "BO", "label": "Bolivia", "phone": "591", "phoneLength": 9},
            { "code": "BR", "label": "Brazil", "phone": "55", "phoneLength": 11},
            { "code": "BS", "label": "Bahamas", "phone": "1242", "phoneLength": 10},
            { "code": "BT", "label": "Bhutan", "phone": "975", "phoneLength": 7},
            { "code": "BV", "label": "Bouvet Island", "phone": "47", "phoneLength": 10},
            { "code": "BW", "label": "Botswana", "phone": "267", "phoneLength": 7},
            { "code": "BY", "label": "Belarus", "phone": "375", "phoneLength": 9},
            { "code": "BZ", "label": "Belize", "phone": "501", "phoneLength": 7},
            { "code": "CA", "label": "Canada", "phone": "1", "suggested": true, "phoneLength": 10},
            { "code": "CC", "label": "Cocos (Keeling) Islands", "phone": "61", "phoneLength": 10},
            { "code": "CD", "label": "Congo, Democratic Republic of the", "phone": "243", "phoneLength": 7},
            { "code": "CF", "label": "Central African Republic", "phone": "236", "phoneLength": 8},
            { "code": "CG", "label": "Congo, Republic of the", "phone": "242", "phoneLength": 9},
            { "code": "CH", "label": "Switzerland", "phone": "41", "phoneLength": 9},
            { "code": "CI", "label": "Cote d\'Ivoire", "phone": "225", "phoneLength": 8},
            { "code": "CK", "label": "Cook Islands", "phone": "682", "phoneLength": 5},
            { "code": "CL", "label": "Chile", "phone": "56", "phoneLength": 9},
            { "code": "CM", "label": "Cameroon", "phone": "237", "phoneLength": 9},
            { "code": "CN", "label": "China", "phone": "86", "phoneLength": 11},
            { "code": "CO", "label": "Colombia", "phone": "57", "phoneLength": 10},
            { "code": "CR", "label": "Costa Rica", "phone": "506", "phoneLength": 8},
            { "code": "CU", "label": "Cuba", "phone": "53", "phoneLength": 8},
            { "code": "CV", "label": "Cape Verde", "phone": "238", "phoneLength": 7},
            { "code": "CW", "label": "Curacao", "phone": "599", "phoneLength": 7},
            { "code": "CX", "label": "Christmas Island", "phone": "61", "phoneLength": 9},
            { "code": "CY", "label": "Cyprus", "phone": "357", "phoneLength": 8},
            { "code": "CZ", "label": "Czech Republic", "phone": "420", "phoneLength": 9},
            { "code": "DE", "label": "Germany", "phone": "49", "suggested": true, "phoneLength": 10},
            { "code": "DJ", "label": "Djibouti", "phone": "253", "phoneLength": 10},
            { "code": "DK", "label": "Denmark", "phone": "45", "phoneLength": 8},
            { "code": "DM", "label": "Dominica", "phone": "1767", "phoneLength": 10},
            { "code": "DO", "label": "Dominican Republic", "phone": "1809", "phoneLength": 10},
            { "code": "DZ", "label": "Algeria", "phone": "213", "phoneLength": 9},
            { "code": "EC", "label": "Ecuador", "phone": "593", "phoneLength": 9},
            { "code": "EE", "label": "Estonia", "phone": "372", "phoneLength": 8},
            { "code": "EG", "label": "Egypt", "phone": "20", "phoneLength": 10},
            { "code": "EH", "label": "Western Sahara", "phone": "212", "phoneLength": 9},
            { "code": "ER", "label": "Eritrea", "phone": "291", "phoneLength": 7},
            { "code": "ES", "label": "Spain", "phone": "34", "phoneLength": 9},
            { "code": "ET", "label": "Ethiopia", "phone": "251", "phoneLength": 9},
            { "code": "FI", "label": "Finland", "phone": "358","phoneLength": 11},
            { "code": "FJ", "label": "Fiji", "phone": "679", "phoneLength": 7},
            { "code": "FK", "label": "Falkland Islands (Malvinas)", "phone": "500", "phoneLength": 5},
            { "code": "FM", "label": "Micronesia, Federated States of", "phone": "691", "phoneLength": 7},
            { "code": "FO", "label": "Faroe Islands", "phone": "298", "phoneLength": 5},
            { "code": "FR", "label": "France", "phone": "33", "suggested": true, "phoneLength": 9},
            { "code": "GA", "label": "Gabon", "phone": "241", "phoneLength": 7},
            { "code": "GB", "label": "United Kingdom", "phone": "44", "phoneLength": 10},
            { "code": "GD", "label": "Grenada", "phone": "1473", "phoneLength": 10},
            { "code": "GE", "label": "Georgia", "phone": "995", "phoneLength": 9},
            { "code": "GF", "label": "French Guiana", "phone": "594", "phoneLength": 9},
            { "code": "GG", "label": "Guernsey", "phone": "44", "phoneLength": 10},
            { "code": "GH", "label": "Ghana", "phone": "233", "phoneLength": 9},
            { "code": "GI", "label": "Gibraltar", "phone": "350", "phoneLength": 8},
            { "code": "GL", "label": "Greenland", "phone": "299", "phoneLength": 6},
            { "code": "GM", "label": "Gambia", "phone": "220", "phoneLength": 7},
            { "code": "GN", "label": "Guinea", "phone": "224", "phoneLength": 9},
            { "code": "GP", "label": "Guadeloupe", "phone": "590", "phoneLength": 9},
            { "code": "GQ", "label": "Equatorial Guinea", "phone": "240", "phoneLength": 9},
            { "code": "GR", "label": "Greece", "phone": "30", "phoneLength": 10},
            { "code": "GS", "label": "South Georgia and the South Sandwich Islands", "phone": "500", "phoneLength": 5},	
            { "code": "GT", "label": "Guatemala", "phone": "502", "phoneLength": 8},
            { "code": "GU", "label": "Guam", "phone": "1671", "phoneLength": 10},
            { "code": "GW", "label": "Guinea-Bissau", "phone": "245", "phoneLength": 9},
            { "code": "GY", "label": "Guyana", "phone": "592", "phoneLength": 7},
            { "code": "HK", "label": "Hong Kong", "phone": "852", "phoneLength": 8},
            { "code": "HM", "label": "Heard Island and McDonald Islands", "phone": "672","phoneLength": 10 },		
            { "code": "HN", "label": "Honduras", "phone": "504", "phoneLength": 8},
            { "code": "HR", "label": "Croatia", "phone": "385", "phoneLength": 9},
            { "code": "HT", "label": "Haiti", "phone": "509", "phoneLength": 8},
            { "code": "HU", "label": "Hungary", "phone": "36", "phoneLength": 9},
            { "code": "ID", "label": "Indonesia", "phone": "62", "phoneLength": 11},
            { "code": "IE", "label": "Ireland", "phone": "353", "phoneLength": 9},
            { "code": "IL", "label": "Israel", "phone": "972", "phoneLength": 9},
            { "code": "IM", "label": "Isle of Man", "phone": "44", "phoneLength": 10},
            { "code": "IN", "label": "India", "phone": "91", "phoneLength": 10},
            { "code": "IO", "label": "British Indian Ocean Territory", "phone": "246", "phoneLength": 7},
            { "code": "IQ", "label": "Iraq", "phone": "964", "phoneLength": 10},	
            { "code": "IR", "label": "Iran, Islamic Republic of", "phone": "98", "phoneLength": 11},
            { "code": "IS", "label": "Iceland", "phone": "354", "phoneLength": 7},
            { "code": "IT", "label": "Italy", "phone": "39", "phoneLength": 10},
            { "code": "JE", "label": "Jersey", "phone": "44", "phoneLength": 10},
            { "code": "JM", "label": "Jamaica", "phone": "1876", "phoneLength": 10},
            { "code": "JO", "label": "Jordan", "phone": "962", "phoneLength": 9},	
            { "code": "JP", "label": "Japan", "phone": "81","phoneLength": 9 },
            { "code": "KE", "label": "Kenya", "phone": "254", "phoneLength": 9},
            { "code": "KG", "label": "Kyrgyzstan", "phone": "996", "phoneLength": 9},
            { "code": "KH", "label": "Cambodia", "phone": "855", "phoneLength": 9},
            { "code": "KI", "label": "Kiribati", "phone": "686", "phoneLength": 8},
            { "code": "KM", "label": "Comoros", "phone": "269", "phoneLength": 7 },
            { "code": "KN", "label": "Saint Kitts and Nevis", "phone": "1869", "phoneLength": 10 },
            { "code": "KP", "label": "Korea, Democratic People\'s Republic of", "phone": "850", "phoneLength": 13 },
            { "code": "KR", "label": "Korea, Republic of", "phone": "82", "phoneLength": 8 },
            { "code": "KW", "label": "Kuwait", "phone": "965", "phoneLength": 8 },
            { "code": "KY", "label": "Cayman Islands", "phone": "1345", "phoneLength": 7 },
            { "code": "KZ", "label": "Kazakhstan", "phone": "7", "phoneLength": 10 },
            { "code": "LA", "label": "Lao People\'s Democratic Republic", "phone": "856", "phoneLength": 9 },
            { "code": "LB", "label": "Lebanon", "phone": "961", "phoneLength": 8 },
            { "code": "LC", "label": "Saint Lucia", "phone": "1758", "phoneLength": 7 },
            { "code": "LI", "label": "Liechtenstein", "phone": "423", "phoneLength": 7 },
            { "code": "LK", "label": "Sri Lanka", "phone": "94", "phoneLength": 7 },
            { "code": "LR", "label": "Liberia", "phone": "231", "phoneLength": 9 },
            { "code": "LS", "label": "Lesotho", "phone": "266", "phoneLength": 8 },
            { "code": "LT", "label": "Lithuania", "phone": "370", "phoneLength": 8 },
            { "code": "LU", "label": "Luxembourg", "phone": "352", "phoneLength": 9 },
            { "code": "LV", "label": "Latvia", "phone": "371", "phoneLength": 8 },
            { "code": "LY", "label": "Libya", "phone": "218", "phoneLength": 10 },
            { "code": "MA", "label": "Morocco", "phone": "212", "phoneLength": 9 },
            { "code": "MC", "label": "Monaco", "phone": "377", "phoneLength": 8 },
            { "code": "MD", "label": "Moldova, Republic of", "phone": "373", "phoneLength": 8 },
            { "code": "ME", "label": "Montenegro", "phone": "382", "phoneLength": 8 },
            { "code": "MF", "label": "Saint Martin (French part)", "phone": "590", "phoneLength": 6 },
            { "code": "MG", "label": "Madagascar", "phone": "261", "phoneLength": 7 },
            { "code": "MH", "label": "Marshall Islands", "phone": "692", "phoneLength": 7 },
            { "code": "MK", "label": "Macedonia, the Former Yugoslav Republic of", "phone": "389", "phoneLength": 8 },
            { "code": "ML", "label": "Mali", "phone": "223", "phoneLength": 8 },
            { "code": "MM", "label": "Myanmar", "phone": "95", "phoneLength": 10 },
            { "code": "MN", "label": "Mongolia", "phone": "976", "phoneLength": 8 },
            { "code": "MO", "label": "Macao", "phone": "853", "phoneLength": 8 },
            { "code": "MP", "label": "Northern Mariana Islands", "phone": "1670", "phoneLength": 7 },
            { "code": "MQ", "label": "Martinique", "phone": "596", "phoneLength": 9 },
            { "code": "MR", "label": "Mauritania", "phone": "222", "phoneLength": 8 },
            { "code": "MS", "label": "Montserrat", "phone": "1664", "phoneLength": 10 },
            { "code": "MT", "label": "Malta", "phone": "356", "phoneLength": 8 },
            { "code": "MU", "label": "Mauritius", "phone": "230", "phoneLength": 8 },
            { "code": "MV", "label": "Maldives", "phone": "960", "phoneLength": 7 },
            { "code": "MW", "label": "Malawi", "phone": "265", "phoneLength": 9 },
            { "code": "MX", "label": "Mexico", "phone": "52", "phoneLength": 10 },
            { "code": "MY", "label": "Malaysia", "phone": "60", "phoneLength": 7 },
            {"code": "MZ", "label": "Mozambique", "phone": "258", "phoneLength": 12},
            {"code": "NA", "label": "Namibia", "phone": "264", "phoneLength": 7},
            {"code": "NC", "label": "New Caledonia", "phone": "687", "phoneLength": 6},
            {"code": "NE", "label": "Niger", "phone": "227", "phoneLength": 8},
            {"code": "NF", "label": "Norfolk Island", "phone": "672", "phoneLength": 6},
            {"code": "NG", "label": "Nigeria", "phone": "234", "phoneLength": 8},
            {"code": "NI", "label": "Nicaragua", "phone": "505", "phoneLength": 8},
            {"code": "NL", "label": "Netherlands", "phone": "31", "phoneLength": 9},
            {"code": "NO", "label": "Norway", "phone": "47", "phoneLength": 8},
            {"code": "NP", "label": "Nepal", "phone": "977", "phoneLength": 10},
            {"code": "NR", "label": "Nauru", "phone": "674", "phoneLength": 7},
            {"code": "NU", "label": "Niue", "phone": "683", "phoneLength": 4},
            {"code": "NZ", "label": "New Zealand", "phone": "64", "phoneLength": 9},
            {"code": "OM", "label": "Oman", "phone": "968", "phoneLength": 8},
            {"code": "PA", "label": "Panama", "phone": "507", "phoneLength": 8},
            {"code": "PE", "label": "Peru", "phone": "51", "phoneLength": 9},
            {"code": "PF", "label": "French Polynesia", "phone": "689", "phoneLength": 8},
            {"code": "PG", "label": "Papua New Guinea", "phone": "675", "phoneLength": 8},
            {"code": "PH", "label": "Philippines", "phone": "63", "phoneLength": 10},
            {"code": "PK", "label": "Pakistan", "phone": "92", "phoneLength": 10},
            {"code": "PL", "label": "Poland", "phone": "48", "phoneLength": 9},
            {"code": "PM", "label": "Saint Pierre and Miquelon", "phone": "508", "phoneLength": 6},
            {"code": "PN", "label": "Pitcairn", "phone": "870", "phoneLength": 9},
            {"code": "PR", "label": "Puerto Rico", "phone": "1", "phoneLength": 10},
            {"code": "PS", "label": "Palestine, State of", "phone": "970", "phoneLength": 9},
            {"code": "PT", "label": "Portugal", "phone": "351", "phoneLength": 9},
            {"code": "PW", "label": "Palau", "phone": "680", "phoneLength": 7},
            {"code": "PY", "label": "Paraguay", "phone": "595", "phoneLength": 9},
            {"code": "QA", "label": "Qatar", "phone": "974", "phoneLength": 8},
            {"code": "RE", "label": "Reunion", "phone": "262", "phoneLength": 10},
            {"code": "RO", "label": "Romania", "phone": "40", "phoneLength": 10},
            {"code": "RS", "label": "Serbia", "phone": "381", "phoneLength": 9},
            {"code": "RU", "label": "Russian Federation", "phone": "7", "phoneLength": 10},
            {"code": "RW", "label": "Rwanda", "phone": "250", "phoneLength": 9},
            {"code": "SA", "label": "Saudi Arabia", "phone": "966", "phoneLength": 9},
            {"code": "SB", "label": "Solomon Islands", "phone": "677", "phoneLength": 7},
            {"code": "SC", "label": "Seychelles", "phone": "248", "phoneLength": 7},
            {"code": "SD", "label": "Sudan", "phone": "249", "phoneLength": 7},
            {"code": "SE", "label": "Sweden", "phone": "46", "phoneLength": 7},
            {"code": "SG", "label": "Singapore", "phone": "65", "phoneLength": 8},
            {"code": "SH", "label": "Saint Helena", "phone": "290", "phoneLength": 4},
            {"code": "SI", "label": "Slovenia", "phone": "386", "phoneLength": 9},
            {"code": "SJ", "label": "Svalbard and Jan Mayen", "phone": "47", "phoneLength": 8},
            {"code": "SK", "label": "Slovakia", "phone": "421", "phoneLength": 9},
            {"code": "SL", "label": "Sierra Leone", "phone": "232", "phoneLength": 8},
            {"code": "SM", "label": "San Marino", "phone": "378", "phoneLength": 10},
            { "code": "SM", "label": "San Marino", "phone": "378", "phoneLength": 10},	
            { "code": "SN", "label": "Senegal", "phone": "221", "phoneLength": 9},
            { "code": "SO", "label": "Somalia", "phone": "252", "phoneLength": 9},	
            { "code": "SR", "label": "Suriname", "phone": "597", "phoneLength": 7},	
            { "code": "SS", "label": "South Sudan", "phone": "211", "phoneLength": 7},
            { "code": "ST", "label": "Sao Tome and Principe", "phone": "239", "phoneLength": 7},
            { "code": "SV", "label": "El Salvador", "phone": "503", "phoneLength": 8},
            { "code": "SX", "label": "Sint Maarten (Dutch part)", "phone": "1721", "phoneLength": 10},
            { "code": "SY", "label": "Syrian Arab Republic", "phone": "963", "phoneLength": 7},					
            { "code": "SZ", "label": "Swaziland", "phone": "268", "phoneLength": 8},	
            { "code": "TC", "label": "Turks and Caicos Islands", "phone": "1649", "phoneLength": 10},
            { "code": "TD", "label": "Chad", "phone": "235", "phoneLength": 6},
            { "code": "TF", "label": "French Southern Territories", "phone": "262", "phoneLength": 10},
            { "code": "TG", "label": "Togo", "phone": "228", "phoneLength": 8},
            { "code": "TH", "label": "Thailand", "phone": "66", "phoneLength": 9},
            { "code": "TJ", "label": "Tajikistan", "phone": "992", "phoneLength": 9},	
            { "code": "TK", "label": "Tokelau", "phone": "690", "phoneLength": 5},
            { "code": "TL", "label": "Timor-Leste", "phone": "670", "phoneLength": 7},
            { "code": "TM", "label": "Turkmenistan", "phone": "993", "phoneLength": 8},	
            { "code": "TN", "label": "Tunisia", "phone": "216", "phoneLength": 8},
            { "code": "TO", "label": "Tonga", "phone": "676", "phoneLength": 5},
            { "code": "TR", "label": "Turkey", "phone": "90", "phoneLength": 11},
            { "code": "TT", "label": "Trinidad and Tobago", "phone": "1868", "phoneLength": 7},
            { "code": "TV", "label": "Tuvalu", "phone": "688", "phoneLength": 5},
            { "code": "TW", "label": "Taiwan, Province of China", "phone": "886", "phoneLength": 9},	
            { "code": "TZ", "label": "United Republic of Tanzania", "phone": "255", "phoneLength": 7},
            { "code": "UA", "label": "Ukraine", "phone": "380", "phoneLength": 9},
            { "code": "UG", "label": "Uganda", "phone": "256", "phoneLength": 7},
            { "code": "US", "label": "United States", "phone": "1", "phoneLength": 10},
            { "code": "UY", "label": "Uruguay", "phone": "598", "phoneLength": 8},
            { "code": "UZ", "label": "Uzbekistan", "phone": "998", "phoneLength": 9},
            { "code": "VA", "label": "Holy See (Vatican City State)", "phone": "379" ,"phoneLength": 10},			
            { "code": "VC", "label": "Saint Vincent and the Grenadines", "phone": "1784", "phoneLength": 7},
            { "code": "VE", "label": "Venezuela", "phone": "58", "phoneLength": 7},
            { "code": "VG", "label": "British Virgin Islands", "phone": "1284", "phoneLength": 7},
            { "code": "VI", "label": "US Virgin Islands", "phone": "1340", "phoneLength": 10},
            { "code": "VN", "label": "Vietnam", "phone": "84", "phoneLength": 9},
            { "code": "VU", "label": "Vanuatu", "phone": "678", "phoneLength": 5},
            { "code": "WF", "label": "Wallis and Futuna", "phone": "681", "phoneLength": 6},
            { "code": "WS", "label": "Samoa", "phone": "685", "phoneLength": 7},	 
            { "code": "XK", "label": "Kosovo", "phone": "383", "phoneLength": 8},	
            { "code": "YE", "label": "Yemen", "phone": "967", "phoneLength": 9},
            { "code": "YT", "label": "Mayotte", "phone": "262", "phoneLength": 9},
            { "code": "ZA", "label": "South Africa", "phone": "27", "phoneLength": 9},
            { "code": "ZM", "label": "Zambia", "phone": "260", "phoneLength": 9},
            { "code": "ZW", "label": "Zimbabwe", "phone": "263", "phoneLength": 9}]';
            
        $data = json_decode($list, true);
        

        return response()->json([
            "message" => "Retrieved data successfully.",
            "status_code" => 200,
            "country_phones" => $data
        ], 200);
        }
        catch(\Exception $e) {
            Log::info("Error: $e");
            return response()->json([
                "message" => "Failed to retrieve country codes data",
                "error" => $e->getMessage(),
                "status_code" => $e->getCode(),
            ], 200);
        }
    }

    public function saveDeviceFCMToken(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'open_id' => 'required'
            ]);
    
            if ($validator->fails()){
                return response()->json([
                    "message" => "Invalid data",
                    "status_code" => 422,
                    "error" => $validator->errors()
                ]);
            }
    
            $device_fcm_id = Str::uuid();
            $open_id = $request->open_id;
            $android_token = $request->android_token;
            $ios_token = $request->ios_token;
            $web_token = $request->web_token;

            if (
                ! $request->has('android_token') &&
                ! $request->has('ios_token') &&
                ! $request->has('web_token') 
            ) {
                return response()->json([
                    "message" => "Provided either android_token, ios_token or web_token ",
                    "error" => "Provided either android_token, ios_token or web_token ",
                    "status_code" => 404,
                ]);
            }

            $user = AppUser::where(['open_id' => $open_id, ])->first();
            if(!$user){ 
                return response()->json([
                    "message" => "User not found",
                    "status_code" => 404,
                    "error" => "User not found",
                ]);
            }

            $data["device_fcm_id"] = $device_fcm_id;
            $data["open_id"] = $open_id;

            if($android_token !=  null ){
                $data["android_token"] = $android_token;
            }
            if($ios_token !=  null ){
                $data["ios_token"] = $request->ios_token;
            }
            if($web_token !=  null ){
                $data["web_token"] = $request->web_token;
            }

             $queryData = [
                'open_id' => $open_id,
                "device_fcm_id" => $device_fcm_id
             ];

            $deviceFCMToken =  DeviceFCMToken::updateOrCreate(
                $queryData,
                $data
            );

            if (!$deviceFCMToken) {
                return response()->json([
                    "message" => "DeviceFCMToken could not be created",
                    "error" => "DeviceFCMToken could not be created",
                    "status_code" => 500,
                ]);
            }

            return response()->json([
                "message" => "DeviceFCMToken saved successfully",
                "status_code" => 200,
                "data" => $deviceFCMToken
            ]);
    
        }
        catch(\Exception $e) {
            Log::info("Error: $e");

            return response()->json([
                "message" => "Authentication failed.",
                "error" => $e->getMessage(),
                "status_code" => $e->getCode(),
            ], 200);
        }
    }

}
