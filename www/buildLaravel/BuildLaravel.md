<h1 align="center">Restful API 建置教學</h1>

## 步驟一：初始化專案與資料庫設定

首先，我們需要建立一個新的 Laravel 專案，並設定好資料庫連線。

```bash
# 建立名為 memo-api 的 Laravel 專案
composer create-project laravel/laravel memo-api

# 進入專案目錄
cd memo-api
```

### 1. 設定 .env 檔案
打開專案根目錄下的 .env 檔案，修改資料庫連線資訊（請根據你的本地環境調整）：

```code
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=memo_api_db
DB_USERNAME=root
DB_PASSWORD=your_password
```
  注意： 請記得先在你的資料庫（使用 phpMyAdmin）中建立一個名為 memo_api_db 的空資料庫。

## 步驟二：設計資料庫結構（Migration）
我們需要兩張表：users（Laravel 已內建）和 memos（備忘錄表）。每個備忘錄都必須屬於某一個特定會員。

### 1. 建立 Memo 模型與遷移檔
在終端機執行以下指令，同時建立 Model 和 Migration：
```code
php artisan make:model Memo -m
```
### 2. 編輯 Memos 遷移檔
打開 database/migrations/xxxx_xx_xx_xxxxxx_create_memos_table.php，定義欄位：

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memos', function (Blueprint $table) {
            $table->id();
            // 建立與 users 表的外鍵關聯，當使用者被刪除時，其備忘錄也一併刪除
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('content')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memos');
    }
};
```
### 3. 執行遷移
在終端機執行指令，將資料表建立至資料庫：

```Bash
php artisan migrate
```
## 步驟三：設定模型關聯（Model Relations）
為了讓程式碼更直覺，我們需要在 User 和 Memo 模型中設定「一對多」的關聯。

### 1. 編輯 app/Models/User.php
在 User 類別內加入 memos 方法：

```PHP
public function memos()
{
    return $this->hasMany(Memo::class);
}
```
### 2. 編輯 app/Models/Memo.php
設定可批量寫入的欄位（$fillable），並加入 user 方法：

```PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Memo extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'content', 'is_completed'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```
## 步驟四：撰寫控制器與 API 路由
我們會建立兩個控制器：AuthController（處理註冊/登入）與 MemoController（處理備忘錄的 CRUD）。

### 1. 建立控制器
```Bash
php artisan make:controller AuthController
php artisan make:controller MemoController --api
```
### 2. 實作 app/Http/Controllers/AuthController.php
```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // 會員註冊
    public function register(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed'
        ]);

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => Hash::make($fields['password'])
        ]);

        $token = $user->createToken('myapptoken')->plainTextToken;

        return response([
            'user' => $user,
            'token' => $token
        ], 201);
    }

    // 會員登入
    public function login(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $fields['email'])->first();

        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response(['message' => '帳號或密碼錯誤'], 401);
        }

        $token = $user->createToken('myapptoken')->plainTextToken;

        return response([
            'user' => $user,
            'token' => $token
        ], 200);
    }

    // 會員登出
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response(['message' => '已成功登出'], 200);
    }
}
```
### 3. 實作 app/Http/Controllers/MemoController.php
這裡我們要確保使用者只能操作屬於自己的備忘錄。

```PHP
<?php

namespace App\Http\Controllers;

use App\Models\Memo;
use Illuminate\Http\Request;

class MemoController extends Controller
{
    // 取得該登入會員的所有備忘錄
    public function index(Request $request)
    {
        return $request->user()->memos;
    }

    // 新增備忘錄
    public function store(Request $request)
    {
        $fields = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
        ]);

        $memo = $request->user()->memos()->create($fields);

        return response($memo, 201);
    }

    // 取得單一備忘錄
    public function show(Request $request, $id)
    {
        $memo = $request->user()->memos()->find($id);
        
        if (!$memo) {
            return response(['message' => '找不到該備忘錄'], 404);
        }
        
        return $memo;
    }

    // 更新備忘錄
    public function update(Request $request, $id)
    {
        $memo = $request->user()->memos()->find($id);

        if (!$memo) {
            return response(['message' => '找不到該備忘錄'], 404);
        }

        $fields = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'nullable|string',
            'is_completed' => 'sometimes|required|boolean'
        ]);

        $memo->update($fields);

        return response($memo, 200);
    }

    // 刪除備忘錄
    public function destroy(Request $request, $id)
    {
        $memo = $request->user()->memos()->find($id);

        if (!$memo) {
            return response(['message' => '找不到該備忘錄'], 404);
        }

        $memo->delete();

        return response(['message' => '備忘錄已刪除'], 200);
    }
}
```
### 4. 設定 API 路由 routes/api.php
提示： 如果你在 Laravel 11 中找不到 routes/api.php，請先在終端機執行 php artisan install:api 來啟用 API 功能與 Sanctum 認證。

打開 routes/api.php 並修改如下：

```PHP
<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MemoController;
use Illuminate\Support\Facades\Route;

// 公開路由（不需要登入）
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// 保護路由（必須攜帶 Token 才能訪問）
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // 自動產生 CRUD 的 API 路由 (index, store, show, update, destroy)
    Route::apiResource('memos', MemoController::class);
});
```
## 修正步驟：編輯 User 模型
請打開專案中的 app/Models/User.php 檔案，確保它引入了 HasApiTokens，並在類別內部使用了它。

修改後的完整程式碼如下：
```php
<?php

namespace App\Models;

// 1. 確保有引入這個命名空間
use Laravel\Sanctum\HasApiTokens; 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    // 2. 確保在 class 內部引入了 HasApiTokens
    use HasApiTokens, HasFactory, Notifiable; 

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // 我們之前設定的備忘錄關聯
    public function memos()
    {
        return $this->hasMany(Memo::class);
    }
}
```
## 修正步驟：編輯 app/Models/Memo.php
請打開你的 app/Models/Memo.php 檔案，檢查最上方引入 HasFactory 的程式碼。

正確的命名空間應該是 Illuminate\Database\Eloquent\Factories\HasFactory，而不是 App\Models\HasFactory。

請將 Memo.php 的內容修改為以下完整的正確程式碼：
```php
<?php

namespace App\Models;

// 1. 請確保這一行完全正確（很多時候是這行漏掉或寫錯，導致系統去 App\Models 裡面找）
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Memo extends Model
{
    // 2. 在類別內部使用它
    use HasFactory;

    // 設定可批量寫入的欄位
    protected $fillable = ['title', 'content', 'is_completed'];

    // 與 User 的關聯
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

## 步驟五：使用 Postman 測試流程
啟動 Laravel 內建伺服器：

```Bash
php artisan serve
```
預設網址為：http://127.0.0.1:8000，所有的 API 路由都會帶有 /api 前綴。

### 1. 會員註冊 (Register)
- 方法 (Method): POST

- 網址 (URL): http://127.0.0.1:8000/api/register

- Headers: 設定 Accept: application/json

- Body (raw JSON):

```JSON
{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```
- 預期回應： 回傳狀態碼 201 Created，並在 JSON 中提供一串 token。請複製這串 Token。

### 2. 會員登入 (Login)
- 方法 (Method): POST

- 網址 (URL): http://127.0.0.1:8000/api/login

- Body (raw JSON):

```JSON
{
    "email": "test@example.com",
    "password": "password123"
}
```
- 預期回應： 回傳狀態碼 200 OK，同樣會拿到 token。

### 3. 新增備忘錄 (Store Memo) - 需要驗證
- 方法 (Method): POST

- 網址 (URL): http://127.0.0.1:8000/api/memos

- Headers: * Accept: application/json

- Authorization: Bearer <貼上剛剛複製的 Token> (在 Postman 中也可以直接切換到 Auth 頁籤，選擇 Bearer Token 並貼上)

- Body (raw JSON):

```JSON
{
    "title": "買牛奶",
    "content": "晚上回家記得去超商買鮮奶"
}
```
- 預期回應： 201 Created，並顯示剛建立的備忘錄資料與自動產生的 id（例如：1）。

### 4. 取得備忘錄列表 (Index Memos) - 需要驗證
- 方法 (Method): GET

- 網址 (URL): http://127.0.0.1:8000/api/memos

- Headers: 需帶有 Authorization: Bearer <Token>

- 預期回應： 200 OK，回傳該會員專屬的備忘錄陣列。

### 5. 更新備忘錄 (Update Memo) - 需要驗證
- 方法 (Method): PUT 或 PATCH

- 網址 (URL): http://127.0.0.1:8000/api/memos/1 （假設修改 id 為 1 的備忘錄）

- Headers: 需帶有 Authorization: Bearer <Token>

- Body (raw JSON):

```JSON
{
    "title": "買大瓶牛奶",
    "is_completed": true
}
```
- 預期回應： 200 OK，回傳更新後的備忘錄資料。

## 常用 Artisan 指令

```bash
# 模型及 Migration 相關
php artisan make:model Todo -m           # 建立 Model 和 Migration
php artisan make:model Todo -mc          # 建立 Model, Migration, Controller
php artisan make:controller TodoController  # 建立 Controller
php artisan migrate                      # 執行所有待執行的遷移
php artisan migrate:rollback             # 回滾上一批遷移
php artisan migrate:refresh              # 回滾並重新執行遷移

# 快取相關
php artisan config:cache                 # 配置快取
php artisan route:cache                  # 路由快取

# 其他實用指令
php artisan tinker                       # 進入互動式 Shell
php artisan make:request TodoRequest     # 建立 Request 類別
php artisan make:middleware CheckTodo    # 建立 Middleware
```
