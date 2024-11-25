## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects.

## Requirements:

- Php >=7.4
- Laravel 8.75
- Mysql >=8.0.35


## Getting Started

- To setup .env file use below command.
```
cp  .env.example  .env
```
- This command will copy the ***.env.example*** file and create a  new file called ***.env.*** You can then edit the new .env file to include the necessary values for your application.

- Then, install all the dependencies using below command.
```
composer install
```

## Database Change
- Create the database new named **bs_pmo**.
- Go to path **.env** and update the below details as per your system configuration such as username and password etc.
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bs_pmo
DB_USERNAME=root
DB_PASSWORD=
```

### Database migration
- To run the migration use below command which will create the necessary tables.
```
php artisan migrate
```

### Database Seeder
- To have the default data into respective tables, use below command which will add data into necessary tables.
```
php artisan db:seed --class=UserRoleSeeder
php artisan db:seed --class=AdminUserSeeder
php artisan db:seed --class=EmailTemaplateSeeder
```

### How to run 
- To run the development server use below command
```
php artisan serve
```
