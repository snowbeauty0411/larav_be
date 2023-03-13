
- Step 1:
Install composer.
Link: https://getcomposer.org/doc/00-intro.md#using-the-installer

<!-- You can specify the filename (default: composer.phar) using the --filename option. Example: -->
<!-- php composer-setup.php --version=1.0.0-alpha8 -->

- Step 2:
Run cmd: composer install

- Step 3:
Run cmd: composer update

- Step 4:
Run cmd: npm install

- Step 5:
php artisan storage:link

<!-- # Config Email
- Step 6: Set email to .env (copy from .env.example and create schema mysql)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=<your Email>
MAIL_PASSWORD=<your email password>
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=null
MAIL_FROM_NAME=<display name>

- Step 7: setting DB
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD= -->

- Step 8: reload config 
php artisan config:cache

- Step 9: migrate DB
Run cmd: php artisan migrate:fresh --seed

- Step 10: Generation APP key
php artisan key:generate 

- Step 11:
Run cmd: php artisan serve


Install JWT
Run cmd: composer require tymon/jwt-auth:^1.0

Install Swagger

Run cmd: composer require "darkaonline/l5-swagger"


"Required @OA\Info() not found"
=> add to Controller
/**
 * @OA\Info(
 *   title="Your super ApplicationAPI",
 *   version="1.0.0",
 * )
*/