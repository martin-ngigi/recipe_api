# 1. Install Composer
- User command to install:
```
composer install
```
- NB: IF ABOVE COMMAND DOES NOT WORK, FOLLOW THE STEPS BELOW.
## i. Download the official expected SHA-384 hash of the Composer installer
EXPECTED_SIGNATURE=$(curl -sS https://composer.github.io/installer.sig)

## ii. Download the Composer installer script locally
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"

## iii. Calculate the SHA-384 hash of the downloaded installer script
ACTUAL_SIGNATURE=$(php -r "echo hash_file('sha384', 'composer-setup.php');")

## iv. Compare the expected and actual hash to verify authenticity
if [ "$EXPECTED_SIGNATURE" = "$ACTUAL_SIGNATURE" ]
then
    # 5. Run the installer if verification passes
    php composer-setup.php
    
    # 6. Remove the installer script after successful installation
    RESULT=$?
    rm composer-setup.php
    exit $RESULT
else
    # 7. Display error and delete a corrupt installer
    echo 'Installer corrupt'
    rm composer-setup.php
    exit 1
fi

# 2. Copy .env.example file to .env on the root folder.
- On Windows: 
```
copy .env.example .env
```
- On Mac or Linux:
```
cp .env.example .env 
```

# 3. Open your .env file and change the database name (DB_DATABASE) to whatever you have, username (DB_USERNAME) and password (DB_PASSWORD) field correspond to your configuration.

# 4. Clear caches:
```
php artisan config:cache
php artisan route:cache
php artisan route:clear
php artisan optimize
php artisan cache:clear
php artisan view:clear
php artisan clear-compiled
```

# 5. Run:
```
php artisan key:generate
php artisan migrate
php artisan serve
```

## NB:
1. Make sure you run commands for clearing caches in the serve terminal so that changes can reflect. i.e. command "# 4. Clear caches:"
2. Remember to update credential. Settings > Secrets and variables > Actions
- Sample deployment video [Tutorial](https://www.youtube.com/watch?v=VRewkhslC9s&ab_channel=TechWithTea)