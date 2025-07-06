<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AppUser;
use App\Models\UserRoleEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ChefControllerAPI extends Controller
{

    public function getChefById(Request $request){
        try {
             $validator = Validator::make($request->all(), [
                'open_id' =>'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                    "message" => "Invalid data",
                    "status_code" => 422,
                ], 200);
            }

            $open_id = $request->open_id;
            $chef = AppUser::where('open_id', $open_id)
            ->where('role', UserRoleEnum::Chef->value)
            ->with('recipesList')
            ->with('recipesList.ingredients_list')
            ->with('allRates')
            ->with('allRates.rater')
             ->with('rate')
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
            $chefs = AppUser::with('recipesList')
            ->where('role', 'Chef')
            ->with('allRates')
            ->with('allRates.rater')
            ->with('rate')
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


}
