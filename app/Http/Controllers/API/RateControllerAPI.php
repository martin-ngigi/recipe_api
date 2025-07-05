<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AllRate;
use App\Models\AppUser;
use App\Models\Chef;
use App\Models\ChefRate;
use App\Models\Rate;
use App\Models\TotalRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RateControllerAPI extends Controller
{
    //
     public function createUpdateRate(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'rater_id' => 'required|exists:app_users,open_id',
                'ratee_id' => 'required|exists:app_users,open_id',
                'rating' => 'required|numeric|min:1|max:5',
                'comment' => 'nullable|string|max:255', // Optional comment field
            ]);

            if($validator->fails()){
                return response()->json([
                    'message' => 'Invalid data.',
                    'status_code' => 422,
                    'error' => $validator->errors(),
                ], 422);
            }

            $rater_id = $request->rater_id;
            $ratee_id = $request->ratee_id;
            $rating = $request->rating;
            $comment = $request->comment;

            if($rater_id == $ratee_id){
                return response()->json([
                    'message' => 'Smart move, but you cannot rate yourself.',
                    'status_code' => 422,
                    'error' => 'You cannot rate yourself.',
                ], 422);
            }

            // Assuming you have a ChefRate model to handle the rating logic
            $allRateData = [
                'rate_id' => Str::uuid(),
                'rater_id' => $rater_id,
                'ratee_id' => $ratee_id,
                'rating' => $rating,
                'comment' => $comment,
            ];

            
             // Create or update the rating
             /*
            $allRate = AllRate::updateOrCreate(
                ['rater_id' => $rater_id, 'ratee_id' => $ratee_id], // Assuming user_id is available from auth
                $allRateData
            );
            */
            $allRate = AllRate::create($allRateData);

            $totalRateData = [
                'rate_id' => Str::uuid(),
                'open_id' => $ratee_id,
                'rating' => AllRate::where('ratee_id', $ratee_id)->avg('rating') ?: 0.0,
                'total_ratings' => AllRate::where('ratee_id', $ratee_id)->count(),
            ];

            $totalRate = TotalRate::updateOrCreate(
                ['open_id' => $ratee_id],
                $totalRateData
            );

            return response()->json([
                'message' => 'Rating created/updated successfully.',
                'data' => [
                    "all_rates" => $allRate,
                    "total_rate" => $totalRate
                ],
                'status_code' => 201,
            ], 201);
        } catch(\Exception $e){
             Log::info("Error: $e");
            return response()->json([
                'message' => 'An error occurred while creating/updating rating.',
                'error' => $e->getMessage(),
                'status_code' => 500,
            ], 500);
        }
    }

    public function getSpecificUserRatings(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'ratee_id' => 'required|exists:app_users,open_id', // Ensure ratee_id exists in app_users table
            ]);

            if($validator->fails()){
                return response()->json([
                    'message' => 'Invalid data.',
                    'status_code' => 422,
                    'error' => $validator->errors(),
                ], 422);
            }

            // Fetch all ratings for the specified chef
            $ratings = AllRate::where('ratee_id', $request->ratee_id)->get();

            if($ratings->isEmpty()){
                return response()->json([
                    'message' => 'No ratings found for this id.',
                    'error' => 'No ratings available at the moment.',
                    'status_code' => 404,
                ], 404);
            }

            return response()->json([
                'message' => 'Ratings retrieved successfully.',
                'data' => $ratings,
                'status_code' => 200,
            ], 200);
        } catch(\Exception $e){
             Log::info("Error: $e");
            return response()->json([
                'message' => 'An error occurred while retrieving ratings.',
                'error' => $e->getMessage(),
                'status_code' => 500,
            ], 500);
        }
    }
}
