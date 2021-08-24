# Setup Instructions

1. Git clone
2. Perform the operations below:
```
composer install
cp .env.example .env
php artisan key:generate
```
3. Create a database in your mysql server
4. Modify your `DB_DATABASE` in .env to point to that database
5. `php artisan migrate`
6. Go to `/register` and create an account
7. To run the test suite, do `php artisan test`