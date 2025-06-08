<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Chef;
use App\Models\Recipe;
use Illuminate\Http\Request;

class HomeControllerAPI extends Controller
{
    //
    public function fetchHomeData(Request $request)
    {
        try {
            // Here you can fetch data from various models and return it as needed
            // For example, fetching chefs, recipes, etc.

           $justForYou = Recipe::with('chef')
                        ->inRandomOrder()  // fetch in random order
                        ->take(4)          // limit to 4 records
                        ->get();

            $trendingRecipes = Recipe::with('chef')
                        ->inRandomOrder()  // fetch in random order
                        ->take(4)          // limit to 4 records
                        ->get();

            $popularChefs =  Chef::with('recipesList')
                        ->with('chefRateList')
                        ->with('chefRateList.rater')
                        ->inRandomOrder()  // fetch in random order
                        ->take(4)          // limit to 4 records
                        ->get();

            $data = [
                'just_for_you' => $justForYou,
                'trending_recipes' => $trendingRecipes,
                'popular_chefs' => $popularChefs,
            ];


            $data = [
                'message' => 'Home data fetched successfully.',
                'status_code' => 200,
                'data' => $data,
            ];

            return response()->json($data, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while fetching home data.',
                'error' => $e->getMessage(),
                'status_code' => 500,
            ], 500);
        }
    }
}
