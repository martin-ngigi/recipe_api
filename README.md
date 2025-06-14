## Recipe
- Create API
```
php artisan install:api
```

## Models with migrations
```
php artisan make:Model AppUser -m
php artisan make:model AllActivity -m
php artisan make:model FirebaseKey -m 
php artisan make:model CustomerNotification -m 
php artisan make:model DeviceFCMToken -m
php artisan make:Model DeviceDetails -m
php artisan make:Model Chef -m
php artisan make:Model Recipe -m
php artisan make:Model Ingredient -m
php artisan make:Model ChefRate -m
```

## API Controllers
```
php artisan make:controller API/AuthControllerAPI
php artisan make:controller API/NotificationControllerAPI
php artisan make:controller API/ChefControllerAPI
php artisan make:controller API/RecipeControllerAPI
php artisan make:controller API/IngredientControllerAPI
php artisan make:controller API/HomeControllerAPI
```

## Middleware for auths
```
php artisan make:middleware CheckBearerToken
```

## Refreshing table
- This drops all tables forcefully in the correct order and re-runs all migrations:
```
php artisan migrate:fresh
```