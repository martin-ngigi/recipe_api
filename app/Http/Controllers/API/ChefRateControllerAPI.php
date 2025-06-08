<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Chef;
use App\Models\ChefRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ChefRateControllerAPI extends Controller
{
    //
    public function createUpdateChefRate(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'open_id' => 'required|exists:app_users,open_id', // Ensure user_id exists in users table
                'chef_id' => 'required|exists:chefs,chef_id', // Ensure chef_id exists in chefs table
                'rating' => 'required|numeric|min:1|max:5', // Rating should be between 1 and 5
                'comment' => 'nullable|string|max:255', // Optional comment field
            ]);

            if($validator->fails()){
                return response()->json([
                    'message' => 'Invalid data.',
                    'status_code' => 422,
                    'error' => $validator->errors(),
                ], 422);
            }

            // Assuming you have a ChefRate model to handle the rating logic
            $data = [
                'rate_id' => Str::uuid(),
                'chef_id' => $request->chef_id,
                'open_id'=> $request->open_id,
                'rating' => $request->rating,
                'comment' => $request->comment,
            ];

            // Create or update the chef rating
            $chefRate = ChefRate::updateOrCreate(
                ['chef_id' => $request->chef_id, 'open_id' => $request->open_id], // Assuming user_id is available from auth
                $data
            );

            Chef::where('chef_id', $request->chef_id)
            ->update([
                'rating' => ChefRate::where('chef_id', $request->chef_id)->avg('rating') ?: 0.0,
                'total_ratings' => ChefRate::where('chef_id', $request->chef_id)->count(),
            ]);

            return response()->json([
                'message' => 'Chef rating created/updated successfully.',
                'data' => $chefRate,
                'status_code' => 201,
            ], 201);
        } catch(\Exception $e){
             Log::info("Error: $e");
            return response()->json([
                'message' => 'An error occurred while creating/updating Chef rating.',
                'error' => $e->getMessage(),
                'status_code' => 500,
            ], 500);
        }
    }


    public function getAllChefRatings(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'chef_id' => 'required|exists:chefs,chef_id', // Ensure chef_id exists in chefs table
            ]);

            if($validator->fails()){
                return response()->json([
                    'message' => 'Invalid data.',
                    'status_code' => 422,
                    'error' => $validator->errors(),
                ], 422);
            }

            // Fetch all ratings for the specified chef
            $ratings = ChefRate::where('chef_id', $request->chef_id)->get();

            if($ratings->isEmpty()){
                return response()->json([
                    'message' => 'No ratings found for this chef.',
                    'error' => 'No ratings available at the moment.',
                    'status_code' => 404,
                ], 404);
            }

            return response()->json([
                'message' => 'Chef ratings retrieved successfully.',
                'data' => $ratings,
                'status_code' => 200,
            ], 200);
        } catch(\Exception $e){
             Log::info("Error: $e");
            return response()->json([
                'message' => 'An error occurred while retrieving Chef ratings.',
                'error' => $e->getMessage(),
                'status_code' => 500,
            ], 500);
        }
    }
}
