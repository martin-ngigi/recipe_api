<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Chef;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ChefControllerAPI extends Controller
{
    //
    public function createChef(Request $request){
         try{
            $validaor = Validator::make($request->all(), [
                'name' => 'required|string',
                'email' => 'required|email',
                'phone' => 'required|string',
                'avatar'=> 'required|string',
            ]);

            if($validaor->fails()){
                return response()->json([
                    'message' => 'Invalid data.',
                    'status_code' => 422,
                    'error' => $validaor->errors(),
                ], 422);
            }

            $data = [
                'chef_id' => Str::uuid(),
                'name'=> $request->name,
                'email'=> $request->email,
                'phone'=> $request->phone,
                'avatar'=> $request->avatar ?? '/images/profile/chef.png', // Default avatar if not provided
            ];

            $chef = Chef::create($data);

            if(!$chef){
                return response()->json([
                    'message' => 'Chef creation failed.',
                    'error' => 'Unable to create Chef at this time.',
                    'status_code' => 500,
                ], );
            }

            return response()->json([
                'message' => 'Chef created successfully.',
                'data' => $chef,
                'status_code' => 201,
            ], 201);
        } catch(\Exception $e){
             Log::info("Error: $e");
            return response()->json([
                'message' => 'An error occurred while creating Chef.',
                'error' => $e->getMessage(),
                'status_code' => $e->getCode(),
            ], 500);
         }
    }

    public function getChefById(Request $request){
        try {
             $validator = Validator::make($request->all(), [
                'chef_id' =>'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                    "message" => "Invalid data",
                    "status_code" => 422,
                ], 200);
            }

            $chefId = $request->chef_id;
            $chef = Chef::where( 'chef_id', $chefId)
            ->with('recipesList')
            ->with('chefRateList')
            ->first();

            if (!$chef) {
                return response()->json([
                    'message' => 'Chef not found.',
                    'status_code' => 404,
                    'error' => 'No Chef found with the provided ID.',
                ], );
            }

            return response()->json([
                'message' => 'Chef retrieved successfully.',
                'data' => $chef,
                'status_code' => 200,
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error: {$e->getMessage()}");
            return response()->json([
                'message' => 'An error occurred while retrieving Chef.',
                'error' => $e->getMessage(),
                'status_code' => $e->getCode(),
            ], 500);
        }
    }

    public function getAllChefs(Request $request){
        try {
            $chefs = Chef::with('recipesList')
            ->with('chefRateList')
            ->with('chefRateList.rater')
            ->get();

            if ($chefs->isEmpty()) {
                return response()->json([
                    'message' => 'No chefs found.',
                    'status_code' => 404,
                    'error'=> 'No chefs available at the moment.',
                ],);
            }

            return response()->json([
                'message' => 'Chefs retrieved successfully.',
                'data' => $chefs,
                'status_code' => 200,
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error: {$e->getMessage()}");
            return response()->json([
                'message' => 'An error occurred while retrieving chefs.',
                'error' => $e->getMessage(),
                'status_code' => $e->getCode(),
            ], 500);
        }
    }

    public function updateChef(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'chef_id' => 'required',
                'name' => 'required|string',
                'email' => 'required|email',
                'phone' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                    "message" => "Invalid data",
                    "status_code" => 422,
                ], );
            }

            $chef_id = $request->chef_id;
            $name = $request->name;
            $email = $request->email;
            $phone = $request->phone;
            $avatar = $request->avatar;

            $chef = Chef::where( 'chef_id', $chef_id)->first();


            if (!$chef) {
                return response()->json([
                    'message' => 'Chef not found.',
                    'status_code' => 404,
                    'error'=> 'No Chef found with the provided ID.',
                ], );
            }

            //$chef->update($request->all());

            $chef->name = $name;
            $chef->email = $email;
            $chef->phone = $phone;
            if($avatar != null){
                $chef->avatar = $avatar;
            }
            $chef->save();

            return response()->json([
                'message' => 'Chef updated successfully.',
                'data' => $chef,
                'status_code' => 200,
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error: {$e->getMessage()}");
            return response()->json([
                'message' => 'An error occurred while updating Chef.',
                'error' => $e->getMessage(),
                'status_code' => $e->getCode(),
            ], 500);
        }
    }

    public function deleteChef(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'chef_id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                    "message" => "Invalid data",
                    "status_code" => 422,
                ], );
            }

            $chef_id = $request->chef_id;
            $chef = Chef::where('chef_id', $chef_id)->first();

            if (!$chef) {
                return response()->json([
                    'message' => 'Chef not found.',
                    'status_code' => 404,
                    'error'=> 'No Chef found with the provided ID.',
                ], );
            }

            $chef->delete();

            return response()->json([
                'message' => 'Chef deleted successfully.',
                'status_code' => 200,
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error: {$e->getMessage()}");
            return response()->json([
                'message' => 'An error occurred while deleting Chef.',
                'error' => $e->getMessage(),
                'status_code' => $e->getCode(),
            ], 500);
        }
    }


}
