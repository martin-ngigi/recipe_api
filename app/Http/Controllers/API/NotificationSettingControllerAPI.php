<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AppUser;
use App\Models\UserNotificationSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class NotificationSettingControllerAPI extends Controller
{

    public function getNotificationSettings(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'openId' => 'required'
            ]);
        
            if ($validator->fails()) {
                $first_error = $validator->errors()->first();

                return response()->json([
                    'message' => "Invalid data passed ",
                    'errors' => $validator->errors(),
                    'status_code' => 422,
                ]);

            }

            $openId = $request->openId;

            $user = AppUser::where('open_id', $openId)->first();

            if(!$user){
                return response()->json([
                    "message" => "User not found.",
                    "error" => "User not found.",
                    "status_code" => 404
                ]);
            }

            $userNotificationSetting = UserNotificationSetting::where("open_id", $openId)->first();

            if (!$userNotificationSetting){
                return response()->json([
                    "message" => "Notification Settings not found.",
                    "error" => "Notification Settings not found.",
                    "status_code" => 404
                ]);
            }

            return response()->json([
                "message" => "Notification Settings retrieved successfully.",
                "status_code" => 200,
                "notification_setting" => $userNotificationSetting,
            ]);

        }
        catch(\Exception $e){
            Log::info("Error: $e");
            return response()->json([
                "message" => "Failed to retrieve Notification Settings.",
                "error" => $e->getMessage(),
                "status_code" => (int)($e->getCode()),
            ],);
        }

    }

    //
    public function updateNotifications(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'open_id' => 'required',
                'in_app' => 'required|in:true,false',
                'email' => 'required|in:true,false',
                'sms' => 'required|in:true,false',
            ]);
        
            if ($validator->fails()) {
                $first_error = $validator->errors()->first();

                return response()->json([
                    'message' => "Invalid data passed ",
                    'errors' => $validator->errors(),
                    'status_code' => 422,
                ]);

            }

    
            $open_id = $request->open_id;
            $in_app = $request->in_app;
            $email = $request->email;
            $sms = $request->sms;

            $user = AppUser::where('open_id', $open_id)->first();

            if(!$user){
                return response()->json([
                    "message" => "User not found.",
                    "error" => "User not found.",
                    "status_code" => 404
                ]);
            }

            $userNotificationSetting = UserNotificationSetting::where("open_id", $open_id)->first();

            if (!$userNotificationSetting){
                return response()->json([
                    "message" => "Notification Settings not found.",
                    "error" => "Notification Settings not found.",
                    "status_code" => 404
                ]);
            }

            $userNotificationSetting->update([
                "in_app" => $in_app,
                "email" => $email,
                "sms" => $sms,
            ]);

            return response()->json([
                "message" => "Notification Settings updated successfully.",
                "status_code" => 200,
                "notification_setting" => $userNotificationSetting,
            ]);

        }
        catch(\Exception $e){
            Log::info("Error: $e");
            return response()->json([
                "message" => "Failed to update Notification Settings.",
                "error" => $e->getMessage(),
                "status_code" => (int)($e->getCode()),
            ],);
        }
    }
    
}
