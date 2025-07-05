<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AppState;
use App\Models\AppVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AppStateControllerAPI extends Controller
{
    //
        public function createAppState(Request $request){
        
        try{
            $validator = Validator::make($request->all(), [
                'state' => 'required',
                'description' => 'required',
                // 'image' => 'required',
                // 'current' => 'required'
            ]);
        
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                    'message' => 'Invalid data',
                    "status_code" => 422
                ]);
            }
    
            $state = $request->state;
            $description = $request->description;
            $image = $request->image??"";
            $current = $request->current??"false";
    
            $appExist = AppState::where('state', $state)->exists();
            if($appExist){
                return response()->json([
                    'error' => 'App state already exists',
                    'message' => 'State already exists',
                    "status_code" => 422
                ]);
            }
    
            $appState = AppState::create([
                "state" => $state,
                "description" => $description,
                "image" => $image,
                "current" => $current
            ]);

            // create createAllActivity
            $data = [
                "message" => "Admin created App State successfully with request: $request",
                "feature" => "AppState",
                "status" => "Success",
            ];
            //$this->createAllActivity($data);

            return response()->json($appState, 200);
        }
        catch(\Exception $e){
            Log::info("Error: $e");
            return response()->json([
                "message" => "Failed to update user account.",
                "error" => $e->getMessage(),
                "status_code" => (int)($e->getCode()),
            ],);
        }

    }

    public function getCurrentAppStateAndVersion(Request $request){
        try{
            $appState = AppState::where("current", "true")->first();
            if(!$appState){
                return response()->json([
                    'error' => 'App state not found',
                    'message' => 'App state not found',
                    "status_code" => 404
                ], 200);
            }
    
            $appVersion = AppVersion::first();
            if(!$appVersion){
                return response()->json([
                    'error' => 'App version not found',
                    'message' => 'App state not found',
                    "status_code" => 404
                ], 200);
            }
    
            return response()->json([
                "message" =>"Received App Version and App State successfully.",
                "app_state" => $appState,
                "app_version" => $appVersion
            ], 200);
        }
        catch(\Exception $e){
            Log::info("Error: $e");
            return response()->json([
                "message" => "Failed get Current App State And Version.",
                "error" => $e->getMessage(),
                "status_code" => (int)($e->getCode()),
            ],);
        }


    }

    public function updateCurrentAppState(Request $request){
        try{

            $validator = Validator::make($request->all(), [
                'state' => 'required'
            ]);
        
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                    'message' => 'App state not found',
                    "status_code" => 404
                ], 200);
            }
    
            $state = $request->state;
    
            $appState = AppState::where('state', $state)->first();
            if(!$appState){
                return response()->json([
                    'error' => "App state doesn't exists",
                    'message' => 'App state not found',
                    "status_code" => 404
                ], 200);
            }
    
            // get current state
            $currentAppState = AppState::where('current', "true")->first();
            // if(!$currentAppState){
            //     return response()->json(['error' => 'Current app state not found'], 200);
            // }
            $currentAppState->current = "false";
            $currentAppState->save();
    
            // Update state
            // $appState->update(['current', "true"]);
            $appState->current = "true";
            $appState->save();

            // create createAllActivity
            $data = [
                "message" => "Admin updated App State successfully with request: $request",
                "status" => "Success",
            ];
            //$this->createAllActivity($data);

    
            return response()->json($appState, 200);
        }
        catch(\Exception $e){
            Log::info("Error: $e");
            return response()->json([
                "message" => "Failed update Current App State And Version.",
                "error" => $e->getMessage(),
                "status_code" => (int)($e->getCode()),
            ],);
        }


    }

    public function getAllAppStates(Request $request){
        try{
            return AppState::all();
        }
        catch(\Exception $e){
            Log::info("Error: $e");
            return response()->json([
                "message" => "Failed to get All App States",
                "error" => $e->getMessage(),
                "status_code" => (int)($e->getCode()),
            ],);
        }

       
    }
}
