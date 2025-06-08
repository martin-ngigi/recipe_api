<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RecipeControllerAPI extends Controller
{
    //
    public function createRecipe(Request $request){
        try{
            $validaor = Validator::make($request->all(), [
                'name' => 'required|string',
                'description' => 'required|string',
                'ingredients' => 'required|array',
                'instructions' => 'required|string',
                'chef_id' => 'required|exists:chefs,chef_id', // Ensure chef_id exists in chefs table
            ]);

            if($validaor->fails()){
                return response()->json([
                    'message' => 'Invalid data.',
                    'status_code' => 422,
                    'error' => $validaor->errors(),
                ], 422);
            }

            $data = [
                'name'=> $request->name,
                'description'=> $request->description,
                'ingredients'=> json_encode($request->ingredients), // Assuming ingredients is an array
                'instructions'=> $request->instructions,
                'chef_id'=> $request->chef_id, // Ensure this is a valid chef_id
                'image'=> $request->image ?? null, // Optional image field
            ];

            $recipe = Recipe::create($data);

            if(!$recipe){
                return response()->json([
                    'message' => 'Recipe creation failed.',
                    'error' => 'Unable to create Recipe at this time.',
                    'status_code' => 500,
                ], );
            }

            return response()->json([
                'message' => 'Recipe created successfully.',
                'data' => $recipe,
                'status_code' => 201,
            ], 201);
        } catch(\Exception $e){
             Log::info("Error: $e");
            return response()->json([
                'message' => 'An error occurred while creating Recipe.',
                'error' => $e->getMessage(),
                'status_code' => $e->getCode(),
            ], 500);
         }
    }

    public function getAllRecipes(Request $request){
        try {
            $recipes = Recipe::all();

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
