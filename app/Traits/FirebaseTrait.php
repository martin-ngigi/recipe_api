<?php 

namespace App\Traits;

use App\Models\CustomerNotification;
use App\Models\DeviceFCMToken;
use App\Models\FirebaseKey;
use App\Models\SafiriUser;

trait FirebaseTrait{

    use APICall;


    public function sendFirebaseNotificationToOneUserDevice($deviceId, $title, $body, $data=null){
       
        $serverKey = "";

        $firebaseKey = FirebaseKey::where('firebase_id', 1)->first();
        if($firebaseKey!= null){
            $serverKey = $firebaseKey->server_key;
        }

        $url = "https://fcm.googleapis.com/fcm/send";

        $headers = [
            "Content-Type: application/json",
            'Authorization: key='. $serverKey,
        ];

        $fields = [
            "to"=> $deviceId,    
            "notification"=> [
                "title"=> $title,
                "body"=> $body,
                "event_type"=>$data['event_type']??"",
            ],    
            "data"=>$data
        ];

        $response = $this->postRequest($url, $headers, $fields);
        
        // return [
        //     'status_code' => $response['api_info']['http_code'], 
        //     'data' => $response['data']
        // ];
        return $response;
        
    }

    /* USE THIS ONE*/
    public function sendFirebaseNotificationToOneUserDevices($openId, $title, $body, $eventType, $data=null){
        $serverKey = "";
        $data = $data ?? [];

        $firebaseKey = FirebaseKey::first();
        if($firebaseKey!= null){
            $serverKey = $firebaseKey->server_key;
        }

        $url = "https://fcm.googleapis.com/fcm/send";

        $ids = [];

        $customerNotification = CustomerNotification::orderBy('created_at', 'desc')->first();
        $notificationID = $customerNotification->notification_id;

        $deviceIds = DeviceFCMToken::where('open_id', $openId)->get();
        $unreadCount = CustomerNotification::where('open_id', $openId)
                                            ->where('is_read', "false")
                                            ->count();

        if (! $deviceIds){
            return [
                "message" => "No registered user devices found",
                "error" => "No registered user devices found",
            ];
        }

        foreach ($deviceIds as $deviceId) {
            if ($deviceId->android_token != null) {
                $ids[] = $deviceId->android_token;
            }
            
            if ($deviceId->ios_token != null) {
                $ids[] = $deviceId->ios_token;
            }
            
            if ($deviceId->web_token != null) {
                $ids[] = $deviceId->web_token;
            }
        }
        // If count is more than one device
        /*
        if($deviceIds->android_token != null){
            // array_push($ids, $deviceIds->android_id);
            $ids[] = $deviceIds->android_token;
        }
        if($deviceIds->ios_token != null){
            // array_push($ids, $deviceIds->ios_id);
            $ids[] = $deviceIds->ios_token;
        }
        if($deviceIds->web_token != null){
            // array_push($ids, $deviceIds->web_id);
            $ids[] = $deviceIds->web_token;
        }
        */

        $aps = [
            "alert"=> [
                "title"=> $title,
                "body"=> $body,
                "event_type"=>$eventType,
            ],
        ];

        $link = "recipe-deeplink://notifications/$notificationID";


        $notification =[
            "title"=> $title,
            "body"=> $body,
            "message" => $body,
            "event_type"=>$eventType,
            "sound" => true,
            "badge" => $unreadCount, // ios badge
            
            "link" => $link // deep link
        ];

        $headers = [
            "Content-Type: application/json",
            'Authorization: key='. $serverKey,
        ];

        $fields = [
            "registration_ids"=> $ids,    
            "notification"=> $notification ,    
            "data"=>$notification,
            "aps" => $aps, // Apple Push Notification.
        ];

        $response = $this->postRequest($url, $headers, $fields);
        
        // return [
        //     'status_code' => $response['api_info']['http_code'], 
        //     'data' => $response['data']
        // ];

        return $response['data'];

        
    }

}

?>