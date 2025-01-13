# Structure My Module

A Laravel package to generate structured modules with controllers, models, services, repositories, and other related files, following a clean and organized structure.

## Installation

1. Install the package via Composer:

```bash
composer require kareem22t/structure-my-module
```

2. For Laravel 11+ and API/Auth-Sanctum types, install Laravel Sanctum:

```bash
php artisan install:api
```

## Command Overview

The package provides the `make:module` command, which creates a complete module structure based on the specified parameters.

### Command Signature

```bash
php artisan make:module {name} {type} {prefix?}
```

### Parameters

- **`prefix`** (optional): A prefix for the module files (e.g., "Admin").
- **`name`** (required): The singular, uppercase name of the module (e.g., "User").
- **`type`** (required): The type of module to create. Must be one of:
  - `mvc`: Creates an MVC-based module
  - `api`: Creates an API-based module
  - `auth-mvc`: Creates an MVC-based authentication module
  - `auth-sanctum`: Creates an API-based authentication module with Sanctum

### Validation Rules

1. **`type`**: Must be either `mvc`, `api`, `auth-mvc`, or `auth-sanctum`
2. **`name`**: Must be singular and uppercase (e.g., `User`, not `Users` or `user`)
3. **`prefix`**: If provided, must be singular and uppercase (e.g., `Admin`, not `Admins` or `admin`)

## Generated Files

The command generates different files based on the module type:

### Common Files for All Types

- Model
- Repository Interface and Implementation
- Service Class

### Type-Specific Files

#### MVC Type

- Web Controller with CRUD operations
- Store and Update request classes
- Views (to be created manually)

#### API Type

- API Controller with CRUD operations
- Store and Update request classes
- Resource and Collection classes
- API Response handling

#### Auth MVC Type

- Web Authentication Controller with:
  - Register
  - Login
  - Show Profile
  - Update Profile
  - Logout
- Login, Register, and Update request classes
- Views (to be created manually)

#### Auth Sanctum Type

- API Authentication Controller with:
  - Register (returns token)
  - Login (returns token)
  - Show Profile
  - Update Profile
  - Logout (revokes tokens)
- Login, Register, and Update request classes
- Resource class
- API Response handling

## Post-Generation Steps

After generating your module, follow these steps to complete the setup:

### 1. Create and Run Migration

Create a migration file for your module if it doesn't exist:

```bash
php artisan make:migration create_[module_name_plural]_table
```

Example migration for a User module:

```php
// database/migrations/xxxx_xx_xx_create_users_table.php
public function up(): void
{
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->string('password');
        $table->timestamps();
    });
}
```

Run the migration:

```bash
php artisan migrate
```

### 2. Set Up Routes

#### For MVC/Auth-MVC Types

```php
// web.php
Route::prefix('users')->group(function () {
    // For regular MVC
    Route::resource('users', UserController::class);

    // For Auth MVC
    Route::post('register', [UserController::class, 'register'])->name('register');
    Route::post('login', [UserController::class, 'login'])->name('login');
    Route::middleware('auth')->group(function () {
        Route::get('profile', [UserController::class, 'show'])->name('profile');
        Route::put('profile', [UserController::class, 'update'])->name('profile.update');
        Route::post('logout', [UserController::class, 'logout'])->name('logout');
    });
});
```

#### For API/Auth-Sanctum Types

```php
// api.php
Route::prefix('v1')->group(function () {
    // For regular API
    Route::apiResource('users', UserController::class);

    // For Auth Sanctum
    Route::post('register', [UserController::class, 'register']);
    Route::post('login', [UserController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('profile', [UserController::class, 'show']);
        Route::put('profile', [UserController::class, 'update']);
        Route::post('logout', [UserController::class, 'logout']);
    });
});
```

### 3. Customize Request Validation

Add your validation rules in the generated request classes:

```php
// Example for RegisterUserRequest.php
public function rules(): array
{
    return [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
    ];
}

// Example for UpdateUserRequest.php
public function rules(): array
{
    return [
        'name' => 'sometimes|string|max:255',
        'email' => 'sometimes|string|email|max:255|unique:users,email,' . auth()->id(),
        'password' => 'sometimes|string|min:8|confirmed',
    ];
}
```

### 4. Configure User Model (for Auth Types)

Make sure your User model is properly configured:

```php
// app/Models/User.php
use Laravel\Sanctum\HasApiTokens; // For auth-sanctum type

class User extends Authenticatable
{
    use HasApiTokens; // For auth-sanctum type

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}
```

## Examples

### Creating an MVC Authentication Module

```bash
php artisan make:module User auth-mvc
```

### Creating a Sanctum API Authentication Module

```bash
php artisan make:module User auth-sanctum
```

### Creating a Prefixed Module

```bash
php artisan make:module User auth-sanctum Admin
# Creates AdminUserController, etc.
```

## API Response Structure

For API and Auth-Sanctum types, responses follow this structure:

### Success Response

```json
{
  "status": true,
  "message": "Success message",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "token": "1|abcdef..." // Only for auth-sanctum type
  }
}
```

### Error Response

```json
{
  "status": false,
  "message": "Error message",
  "errors": {
    "email": ["The email field is required."]
  }
}
```
