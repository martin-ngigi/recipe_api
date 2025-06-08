<?php

use App\Http\Controllers\API\AuthControllerAPI;
use App\Http\Controllers\API\ChefControllerAPI;
use App\Http\Controllers\API\ChefRateControllerAPI;
use App\Http\Controllers\API\NotificationControllerAPI;
use App\Http\Controllers\API\NotificationSettingControllerAPI;
use App\Http\Controllers\API\RecipeControllerAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/ping', function(){
    return "pong";
});


Route::group(['prefix'=>'auth'], function(){
    Route::post('/authentication', [AuthControllerAPI::class, 'authentication']);
    Route::post('/save-device-details', [AuthControllerAPI::class, 'saveDeviceDetails'])->middleware('app_user_middleware');
    Route::post('/save-device-fcm-token', [AuthControllerAPI::class, 'saveDeviceFCMToken'])->middleware('app_user_middleware');
    Route::get('/country-codes' ,[ AuthControllerAPI::class, 'countryCodes']);
});

Route::middleware(['app_user_middleware'])->group(function () {
    // Notifications
    Route::group(['prefix' => 'notifications'], function(){
        Route::post('/send-sms', [NotificationControllerAPI::class,'sendSMS'])->name('/notifications/send-sms');
        Route::post('/send-email', [NotificationControllerAPI::class, 'sendEmail']);
        Route::post('/send-fcm-push-to-one-user-device', [NotificationControllerAPI::class, 'sendPushNotificationToOneUserDevice']);
        Route::post('/send-fcm-push-to-one-user-devices', [NotificationControllerAPI::class, 'sendPushNotificationToOneUserDevices']);
        Route::post('/send-all-notifications', [NotificationControllerAPI::class, 'sendAllNotificationsAPI']);
        Route::get('/get-user-notifications', [NotificationControllerAPI::class, 'getUserNotifications']);
        Route::get('/update-is-read', [NotificationControllerAPI::class, 'updateIsReadNotification']);
    });

    // Notification Settings
    Route::group(['prefix'=>'settings'], function(){
        Route::group(['prefix'=>'notifications'], function(){
            Route::get('/fetch' ,[ NotificationSettingControllerAPI::class, 'getNotificationSettings']);
            Route::post('/update' ,[ NotificationSettingControllerAPI::class, 'updateNotifications']);
        });
    });

    // Protected Chefs
    Route::group(['prefix'=>'chefs'], function(){
        Route::post('/create', [ChefControllerAPI::class, 'createChef']);
        Route::put('/update', [ChefControllerAPI::class, 'updateChef']);
        Route::delete('/delete', [ChefControllerAPI::class, 'deleteChef']);      
    });

    // Recipes
    Route::group(['prefix'=> 'recipes'], function(){
        Route::post('/create', [RecipeControllerAPI::class, 'createRecipe']);
        Route::put('/update', [RecipeControllerAPI::class, 'updateRecipe']);
        Route::delete('/delete', [RecipeControllerAPI::class, 'deleteRecipe']);
    });

    Route::group(['prefix'=> 'chef-rates'], function(){
        Route::post('/create-update', [ChefRateControllerAPI::class, 'createUpdateChefRate']);
    });

});


Route::group(['prefix'=>'chefs'], function(){
    Route::get('/get-all', [ChefControllerAPI::class, 'getAllChefs']);
    Route::get('/get-by-id', [ChefControllerAPI::class, 'getChefById']);
});

Route::group(['prefix'=> 'recipes'], function(){
    Route::get('/get-all', [RecipeControllerAPI::class, 'getAllRecipes']);
});