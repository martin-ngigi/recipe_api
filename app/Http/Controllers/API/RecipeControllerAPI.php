<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RecipeControllerAPI extends Controller
{

    public function createRecipe(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'description' => 'required|string',
                //'ingredients' => 'required|array',
                'ingredients.*.name' => 'required|string',
                'ingredients.*.image' => 'required|string',
                'ingredients.*.quantity' => 'required|string',
                'instructions' => 'required|string',
                'open_id' => 'required|exists:app_users,open_id',
                'image'=> 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Invalid data.',
                    'status_code' => 422,
                    'error' => $validator->errors(),
                ], 422);
            }

            DB::beginTransaction();

            $recipeId = Str::uuid()->toString();

            $recipe = Recipe::create([
                'recipe_id' => $recipeId,
                'name' => $request->name,
                'description' => $request->description,
                //'ingredients' => json_encode($request->ingredients), // optional if you still want to store ingredients as a string too
                'instructions' => $request->instructions,
                'open_id' => $request->open_id,
                'image' => $request->image,
            ]);

            // Create ingredients
            $createdIngredients = [];

            foreach ($request->ingredients as $ingredient) {
                $createdIngredients[] = Ingredient::create([
                    'ingredient_id' => Str::uuid(),
                    'name' => $ingredient['name'],
                    'quantity' => $ingredient['quantity'],
                    'image' => $ingredient['image'],
                    'recipe_id' => $recipeId,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Recipe and ingredients created successfully.',
                'data' => [
                    'recipe' => $recipe,
                    'ingredients' => $createdIngredients,
                ],
                'status_code' => 201,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error creating recipe: " . $e->getMessage());

            return response()->json([
                'message' => 'An error occurred while creating the recipe.',
                'error' => $e->getMessage(),
                'status_code' => 500,
            ]);
        }
    }


    public function getAllRecipes(Request $request){
        try {
            $recipes = Recipe::with('chef')
           // ->with('chef.getChefRateAttribute') // Eager load the chefRate relationship
           ->with('ingredients_list') // Eager load the ingredients_list relationship
            ->get(); // Eager load the chef relationship

            if ($recipes->isEmpty()) {
                return response()->json([
                    'message' => 'No Recipes found.',
                    'status_code' => 404,
                    'error'=> 'No Recipes available at the moment.',
                ],);
            }

            return response()->json([
                'message' => 'Recipe retrieved successfully.',
                'data' => $recipes,
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

    public function updateRecipe(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'recipe_id' => 'required|exists:recipes,recipe_id',
                'name' => 'sometimes|required|string',
                'description' => 'sometimes|required|string',
                'ingredients' => 'sometimes|required|array',
                'instructions' => 'sometimes|required|string',
                'chef_id' => 'sometimes|required|exists:chefs,chef_id', // Ensure chef_id exists in chefs table
                'image' => 'sometimes|nullable|string', // Optional image field
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                    "message" => "Invalid data",
                    "status_code" => 422,
                ], );
            }

            $recipe_id = $request->recipe_id;
            $name = $request->name;
            $description = $request->description;
            $ingredients = $request->ingredients;
            $instructions = $request->instructions;
            $chef_id = $request->chef_id;
            $image = $request->image;

            $recipe = Recipe::where( 'recipe_id', $recipe_id)->first();

            if (!$recipe) {
                return response()->json([
                    'message' => 'Recipe not found.',
                    'status_code' => 404,
                    'error'=> 'No Recipe found with the provided ID.',
                ], );
            }

            //$recipe->update($request->all());
            $recipe->name = $name;
            $recipe->description = $description;
            $recipe->ingredients = $ingredients;
            $recipe->instructions = $instructions;
            $recipe->chef_id = $chef_id;
            $recipe->image = $image;
            $recipe->chef_id = $chef_id;
            $recipe->save();
            if (!$recipe) {
                return response()->json([
                    'message' => 'Recipe update failed.',
                    'error' => 'Unable to update Recipe at this time.',
                    'status_code' => 500,
                ], );
            }

            return response()->json([
                'message' => 'Recipe updated successfully.',
                'data' => $recipe,
                'status_code' => 200,
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error: {$e->getMessage()}");
            return response()->json([
                'message' => 'An error occurred while updating Recipe.',
                'error' => $e->getMessage(),
                'status_code' => $e->getCode(),
            ], 500);
        }
    }

    public function deleteRecipe(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'recipe_id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                    "message" => "Invalid data",
                    "status_code" => 422,
                ], );
            }

            $recipe_id = $request->recipe_id;
            $recipe = Recipe::where('recipe_id', $recipe_id)->first();

            if (!$recipe) {
                return response()->json([
                    'message' => 'Recipe not found.',
                    'status_code' => 404,
                    'error'=> 'No Recipe found with the provided ID.',
                ], );
            }

            $recipe->delete();

            return response()->json([
                'message' => 'Recipe deleted successfully.',
                'status_code' => 200,
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error: {$e->getMessage()}");
            return response()->json([
                'message' => 'An error occurred while deleting Recipe.',
                'error' => $e->getMessage(),
                'status_code' => $e->getCode(),
            ], 500);
        }
    }

}
