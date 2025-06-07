<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AppUser;
use App\Models\CustomerNotification;
use App\Traits\AllActivityTrait;
use App\Traits\EmailTrait;
use App\Traits\FirebaseTrait;
use App\Traits\NotificationTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class NotificationControllerAPI extends Controller
{
    use AllActivityTrait;
    use EmailTrait;
    use FirebaseTrait;
    use NotificationTrait;

    //
    public function sendSMS(Request $request){
        $validator = Validator::make($request->all(), [
            'phone' => 'required|integer',
            'message' => 'required',
        ]);

        $phone = $request->phone;
        $message = $request->message;
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try{
            $phone = $request->phone;
            $message = $request->message;

            // create createAllActivity
            $data = [
                "message" => "SMS was sent to +$phone with message $message successfully, with request: $request",
                "feature" => "Notification",
                "status" => "Success",
            ];
            $this->createAllActivity($data);

            //$result = $this->sendAfricasTalkingMessage("+$phone", $message);
            $resul = "";
            return response()->json([
                'message' => 'SMS sent successfully',
                'status_code' => 200,
                'result' => $resul
            ], 200);
        }
        catch(Exception $e){
            Log::info("Error: $e");
            return response()->json([
              'error' => $e->getMessage()
            ]);
        }
    }

    public function sendEmail(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'email' =>'required|email',
                'subject' =>'required',
                'body' =>'required',
            ]);

            if($validator->fails()){
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $email = $request->email;
            $subject = $request->subject;
            $body = $request->body;
            $user_name = $request->user_name??'';

            $this->sendEmailTrait( $email,$subject,$body, $user_name);
            //OR
            //$this->sendEmailTrait1( $email,$subject,$body, $user_name);

            // create createAllActivity
            $data = [
                "message" => "Email was sent to +$email with message $body to $user_name  successfully, with request: $request",
                "feature" => "Developer",
                "status" => "Success",
            ];
            $this->createAllActivity($data);

            return response()->json([
                'message' => 'email sent successfully',
                'status_code' => 200
            ], 200); 
        }
        catch (Exception $e) {
            Log::info("Error: $e");
            return response()->json([
                'message' => 'Sorry :-(  Could not send email....',
                'error' => $e,
                'status_code' => 500
            ], 500);
        }

    }


    public function sendPushNotificationToOneUserDevice(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'fcm_token' =>'required', // to
                'title' =>'required',
                'body' =>'required',
            ]);

            if($validator->fails()){
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $fcmToken = $request->fcm_token;
            $title = $request->title;
            $body = $request->body;
            $data = $request->data??[];


           $response =  $this->sendFirebaseNotificationToOneUserDevice( $fcmToken,$title,$body, $data);

            // create createAllActivity
            $data = [
                "message" => "Push Notification was sent to +$fcmToken Token  with message $body  successfully, with request: $request",
                "feature" => "Notification",
                "status" => "Success",
            ];
            $this->createAllActivity($data);

            // return response()->json([
            //     'message' => 'notification sent successfully',
            //     'status_code' => 200
            // ], 200); 
            return $response;
        }
        catch (Exception $e) {
            Log::info("Error: $e");
            return response()->json([
                'message' => 'Sorry :-(  Could not send FCM',
                'error' => $e,
                'status_code' => 500
            ], 500);
        }

    }

    public function sendPushNotificationToOneUserDevices(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'open_id' => 'required',
                'title' =>'required',
                'body' =>'required',
                "event_type" => 'required',
            ]);

            if($validator->fails()){
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $open_id = $request->open_id;
            $title = $request->title;
            $body = $request->body;
            $eventType = $request->event_type;
            $data = $request->data??[];

            $response =  $this->sendFirebaseNotificationToOneUserDevices($open_id, $title, $body, $eventType, $data);

            // create createAllActivity
            $data = [
                "message" => "Push Notification was sent to $open_id OpenID  with message $body  successfully, with request: $request",
                "feature" => "Notification",
                "status" => "Success",
                "open_id" => $open_id
            ];
            $this->createAllActivity($data);

            // return response()->json([
            //     'message' => 'email sent successfully',
            //     'status_code' => 200
            // ], 200); 
            return $response;
        }
        catch (Exception $e) {
            Log::info("Error: $e");
            return response()->json([
                'message' => 'Sorry :-(  Could not send FCM',
                'error' => $e,
                'status_code' => 500
            ], 500);
        }

    }


    public function sendAllNotificationsAPI(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'is_email' =>'required|boolean',
                'is_sms' =>'required|boolean',
                'is_push' =>'required|boolean',
                'open_id' => 'required',
                'subject' => 'required',
                'message' => 'required',
                "event_type" =>'required',
            ]);

            if($validator->fails()){
                return response()->json([
                    'errors' => $validator->errors(),
                    "message" => 'Invalid data',
                    "status_code" => 422,
                ], 200);    
            }

            $isEmail = $request->is_email;
            $isSms = $request->is_sms;
            $isPush = $request->is_push;
            $openId = $request->open_id;
            $subject = $request->subject;
            $message = $request->message;
            $eventType = $request->event_type;

            $user = AppUser::where('open_id', $openId)->first();
    
            if(!$user){
                return response()->json([
                    'error' =>'User not found',
                    "message" => 'User not found',
                    "status_code" => 404,
                ], 200);
            }

            $data = [
                "subject" => $subject,
                "message" => $message,
                "event_type" => $eventType, 
            ];


           $response =  $this->sendNotification($openId, $isSms, $isEmail, $isPush, $data);


            // create createAllActivity
            $data = [
                "message" => "SMS | Email | Push Notification was sent to $openId OpenID  with message $message. successfully, with request: $request",
                "feature" => "Notification",
                "status" => "Success",
                "open_id" => $openId
            ];
            $this->createAllActivity($data);

            return response()->json([
                'message' => 'Notifications sent successfully',
                'status_code' => 200,
                'data' => $response
            ], 200); 
            return $response;
        }
        catch (Exception $e) {
            Log::info("Error: $e");
            return response()->json([
                'message' => 'Sorry :-(  Could not send notification',
                "error" => $e->getMessage(),
                "status_code" => $e->getCode(),
            ]);
        }
    }

    public function getUserNotifications(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'openId' =>'required',
            ]);

            if($validator->fails()){
                return response()->json([
                    'errors' => $validator->errors(),
                    "message" => 'Invalid data',
                    "status_code" => 422,
                ], 200);
            }

            $openId = $request->openId;

            $user = AppUser::where('open_id', $openId)->first();

            // create createAllActivity
            // $data = [
            //     "message" => "  $user->name retrieved all notifications successfully with request: $request",
            //     "feature" => "Notification",
            //     "status" => "Success",
            //     "open_id" => $openId
            // ];
            // $this->createAllActivity($data);

            if(!$user){
                return response()->json([
                    'error' =>'User not found',
                    "message" => ' User not found',
                    "status_code" => 404,
                ], 200);
            }

            $notifications = CustomerNotification::where('open_id', $openId)
            ->orderBy('created_at', 'desc')
            ->get();

            return response()->json([
                "message" => "Retrieved notifications successfully",
                "status_code" => 200,
                "notifications" => $notifications,
            ], 200);

        }
        catch (Exception $e) {
            Log::info("Error: $e");
            return response()->json([
               'message' => 'Could not retrieve user  notifications.',
               "error" => $e->getMessage(),
               "status_code" => $e->getCode(),
            ]);
        }
    }

    public function updateIsReadNotification(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'notificationId' =>'required',
                //'openId' =>'required',
            ]);
    
            if($validator->fails()){
                return response()->json([
                    'errors' => $validator->errors(),
                    "message" => 'Invalid data',
                    "status_code" => 422,
                ], 200);
            }
    
            $notificationId = $request->notificationId;
            //$openId = $request->openId;

            $customerNotification = CustomerNotification::where('notification_id', $notificationId)->first();
            if(!$customerNotification){
                return response()->json([
                    'error' =>'notifiaction not found',
                    "message" => ' notifiaction not found',
                    "status_code" => 404,
                ], 200);
            }
            $customerNotification->is_read = "true";
            $customerNotification->save();

            $notifications = CustomerNotification::where('open_id', $customerNotification->open_id)
            ->orderBy('created_at', 'desc')
            ->get();

            return response()->json([
                "message" => "Updated and Retrieved notifications successfully",
                "status_code" => 200,
                "notifications" => $notifications,
            ], 200);
        }
        catch (Exception $e) {
            Log::info("Error: $e");
            return response()->json([
               'message' => 'Could not update user  notifications.',
               "error" => $e->getMessage(),
               "status_code" => $e->getCode(),
            ]);
        }
    }
}

