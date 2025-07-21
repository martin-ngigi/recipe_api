<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AppUser;
use App\Models\Chef;
use App\Models\Recipe;
use App\Models\UserRoleEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HomeControllerAPI extends Controller
{
    //
    public function fetchHomeData(Request $request)
    {
        try {
            // Here you can fetch data from various models and return it as needed
            // For example, fetching chefs, recipes, etc.

           $justForYou = Recipe::with('chef')
                        ->with('ingredients_list')
                        ->with('chef.rate')
                        ->inRandomOrder()  // fetch in random order
                        ->take(4)          // limit to 4 records
                        ->get();

            $trendingRecipes = Recipe::with('chef')
                        ->with('ingredients_list')
                        ->with('chef.rate')
                        ->inRandomOrder()  // fetch in random order
                        ->take(4)          // limit to 4 records
                        ->get();

            $popularChefs = AppUser::with('recipesList')
                        ->with('allRates')
                        ->with('allRates.rater')
                        ->with('rate')
                        ->where('role', UserRoleEnum::Chef->value) 
                        ->whereHas('recipesList') // Ensure the chef has recipes
                        ->with('recipesList.ingredients_list')
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

    public function searchAll(Request $request){
        try {
            $searchTerm = $request->searchTerm ?? "";
            
            if ($searchTerm == ""){
                return response()->json([
                    "message" => "Searching recipes success...",
                    "status_code" =>  200,
                    "search_response_data" => []
                ], 200);
            }
    
            $recipes = Recipe::with("chef")
            ->orWhereHas("chef", function($query) use ($searchTerm){ // search whether chef has name like searchTerm
                $query->where('name', 'like', '%' . $searchTerm . '%');
            })
            ->orWhereHas('chef', function($query) use ($searchTerm) { // search whether chef email has name like searchTerm
                $query->where('email', 'like', '%' . $searchTerm . '%');
            })
            ->orWhere('name', 'like', '%' . $searchTerm . '%') //Recipe name like searchTerm 
            ->orWhere('description', 'like', '%' . $searchTerm . '%') //Recipe description like searchTerm 
            ->with('ingredients_list')
            ->with('chef.rate')
            ->get();

            $chefs = AppUser::with('recipesList')
            ->orWhere('name', 'like', '%' . $searchTerm . '%') //Recipe name like searchTerm 
            ->orWhere('email', 'like', '%' . $searchTerm . '%')
            ->with('allRates')
            ->with('allRates.rater')
            ->with('rate')
            ->where('role', UserRoleEnum::Chef->value) 
            ->whereHas('recipesList') // Ensure the chef has recipes
            ->with('recipesList.ingredients_list')
            ->get();

            return response()->json([
                "message" => "Searching recipes success.",
                "status_code" =>  200,
                "recipes" => $recipes,
                "chefs" => $chefs 
            ], 200);

        }
         catch(\Exception $e) {
            Log::info("Error: $e");
            return response()->json([
                "message" => "Searching recipes failed.",
                "error" => $e->getMessage(),
                "status_code" => $e->getCode(),
            ], 200);
        }
    }
}
