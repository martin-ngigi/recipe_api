<?php

namespace App\Traits;

use App\Models\AllActivity;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

trait AllActivityTrait
{
    public function createAllActivity($data)
    {
        try{
            $allActivity = new AllActivity();
            $allActivity->activity_id = Str::uuid();
            $allActivity->message = $data['message'];
            $allActivity->feature = $data['feature']??"";
            $allActivity->open_id = $data['open_id']??"";
            $allActivity->admin_id = $data['admin_id']??0;
            $allActivity->save();
            return $allActivity;
        }
        catch(Exception $e){
            Log::info("Error: $e");
            return [
                "message"=>"Could not save the activity",
                "status_code"=>$e->getCode(),
                "error"=>$e->getMessage(),
            ];
        }

    }


}
