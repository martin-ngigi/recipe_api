<?php 

namespace App\Traits;

use App\Models\AppUser;
use App\Models\AppUserNotification;
use App\Models\AppUsers;
use App\Models\CustomerNotification;
use App\Models\UserNotificationSetting;
use Illuminate\Support\Str;

trait NotificationTrait{


    //use AfricasTalkingTrait;
    use EmailTrait;
    use FirebaseTrait;

    public function sendNotification($openId, $isSms, $isEmail, $isInApp, $data){
        $user = AppUser::where('open_id', $openId)->first();
    
        if(!$user){
            return response()->json(['error' => 'User not found'], 404);
        }

        $email = $user->email;
        $name = $user->name;
        $phone = $user->phone_complete;
        $message = $data["message"];
        $subject = $data["subject"];
        $eventType = $data["event_type"];
        $banner = $data["banner"] ?? "";

        $userNotificationSetting = UserNotificationSetting::where("open_id", $openId)->first();
        $emailEnable = $userNotificationSetting->email == "true" ? true : false;
        $smsEnable = $userNotificationSetting->sms == "true"? true : false;
        $inAppEnable = $userNotificationSetting->in_app == "true"? true : false;
        $customerNotificationId = null;

        if (!$userNotificationSetting){
            return response()->json([
                "message" => "Notification Settings not found.",
                "error" => "Notification Settings not found.",
                "status_code" => 404
            ]);
        }

        if ( $inAppEnable ) {
            $customerNotification = CustomerNotification::create([
                'notification_id' => Str::uuid(),
                'open_id' => $openId,
                'title' => $subject,
                'message' => $message,
                'banner' => $banner,
            ]);
            $customerNotificationId = $customerNotification->id;
        }
        

        $respone =[];

        if($isSms && $smsEnable){
            if(($phone != null) && (substr($phone, 0, 3) === "254")){
                //$respone['sms'] = $this->sendAfricasTalkingMessage("+$phone", $message);
            }
        }

        if($isEmail && $emailEnable){
            $respone['email'] = $this->sendEmailTrait($email, $subject, $message, $name);
        }

        if($isInApp && $inAppEnable){
            $respone['push'] = $this->sendFirebaseNotificationToOneUserDevices($openId, $subject, $message, $eventType, $data);
        }

        

        return $respone;
    }

}

?>