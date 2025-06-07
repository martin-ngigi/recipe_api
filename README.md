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
```

## API Controllers
```
php artisan make:controller API/AuthControllerAPI
php artisan make:controller API/NotificationControllerAPI
```

## Middleware for auths
```
php artisan make:middleware CheckBearerToken
```