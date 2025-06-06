<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AppUser;
use App\Models\UserNotificationSetting;
use App\Traits\NotificationTrait;
use App\Traits\TokenGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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
                'type' => 'required|in:Email,Google,Apple,Facebook,Twitter',
                'open_id' => 'required|string'
            ]);

            if($validaor->fails()){
                return response()->json([
                    'message' => 'Invalid data.',
                    'status_code' => 422,
                    'error' => $validaor->errors(),
                ], 422);
            }

            $userId = Str::uuid();
            $name = $request->name;
            $email = $request->email;
            $type = $request->type;
            $openId = $request->open_id;
            $avatar = $request->avatar??"/images/profile/default.png";
            $token = $this->generateToken(101);
            $access_token = $this->generateToken(101);

            $user = AppUser::where('open_id', $openId)
                ->first();

            if ($user) {
                //User exists, update the user information
                $user->type = $type;
                $user->save();

                return response()->json([
                    'message' => 'User authenticated successfully.',
                    'status_code' => 200,
                    'user' => $user,
                ], 200);
            }

            //user does not exist, create a new user
            $user = AppUser::create([
                'user_id' => $userId,
                'name' => $name,
                'email'=> $email,
                'type'=> $type,
                'open_id' => $openId,
                'avatar'=> $avatar,
                'token'=> $token,
                'access_token' => $access_token,
            ]);

            // User Notification Setting
            $userNotificationSetting = UserNotificationSetting::create([
                'id' => Str::uuid(),
                'open_id' => $openId,
                'email' => 'true',
                'sms' => 'true',
                'in_app' => 'true',
            ]);


            // Send notification
            $subject = "Welcome aboard.";
            $body = "Welcome to Recipe App, $name!\n We're excited to have you with us. Start exploring now and enjoy exclusive recipes just for you!\n Happy cooking!";

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
                "user" => $user
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

}
