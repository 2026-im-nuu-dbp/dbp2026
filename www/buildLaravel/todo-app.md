# Laravel 入門

## 快速入門 (以 Todo List 應用為範例)

### 步驟
1. 建立laravel 專案
2. 理解 Routing, Controller, View
3. 資料庫互動： Migration, Model, CRUD
4. 表單處理：input, display, delete, run

---

## 步驟 1: 建立 Laravel 專案

### 說明
建立一個新的 Laravel 專案，這是開發任何 Laravel 應用的第一步。使用 Composer 來管理依賴並創建專案結構。

### 指令

```bash
# 方法1: 使用 Laravel Installer (推薦)
composer global require laravel/installer
laravel new todo-app
cd todo-app

# 方法2: 使用 Composer create-project
composer create-project laravel/laravel todo-app
cd todo-app

# 啟動開發伺服器
php artisan serve
```

### 專案結構說明
```
todo-app/
├── app/                 # 應用程式邏輯 (Models, Controllers)
├── routes/              # 路由定義
│   └── web.php         # Web 路由
├── resources/
│   └── views/          # Blade 樣板檔案
├── database/           # 資料庫相關
│   ├── migrations/     # 資料庫遷移檔案
│   └── seeders/        # 資料假造
├── public/             # 公開資源 (CSS, JS, Images)
└── config/             # 設定檔
```

---

## 步驟 2: 理解 Routing, Controller, View

### 說明
Laravel 遵循 MVC 架構。路由定義 URL，Controller 處理邏輯，View 負責顯示。

### 路由定義 (routes/web.php)

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TodoController;

// 首頁路由
Route::get('/', [TodoController::class, 'index'])->name('todo.index');

// 顯示待辦詳情
Route::get('/todo/{id}', [TodoController::class, 'show'])->name('todo.show');

// 取得新增表單
Route::get('/todo/create', [TodoController::class, 'create'])->name('todo.create');

// 儲存新待辦
Route::post('/todo', [TodoController::class, 'store'])->name('todo.store');

// 取得編輯表單
Route::get('/todo/{id}/edit', [TodoController::class, 'edit'])->name('todo.edit');

// 更新待辦
Route::put('/todo/{id}', [TodoController::class, 'update'])->name('todo.update');

// 刪除待辦
Route::delete('/todo/{id}', [TodoController::class, 'destroy'])->name('todo.destroy');
```

### Controller (app/Http/Controllers/TodoController.php)

```php
<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;

class TodoController extends Controller
{
    // 顯示所有待辦清單
    public function index()
    {
        $todos = Todo::all();
        return view('todo.index', compact('todos'));
    }

    // 顯示新增表單
    public function create()
    {
        return view('todo.create');
    }

    // 儲存新待辦到資料庫
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Todo::create($validated);

        return redirect()->route('todo.index')->with('success', '待辦已建立');
    }

    // 顯示單個待辦
    public function show($id)
    {
        $todo = Todo::findOrFail($id);
        return view('todo.show', compact('todo'));
    }

    // 顯示編輯表單
    public function edit($id)
    {
        $todo = Todo::findOrFail($id);
        return view('todo.edit', compact('todo'));
    }

    // 更新待辦
    public function update(Request $request, $id)
    {
        $todo = Todo::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'completed' => 'boolean',
        ]);

        $todo->update($validated);

        return redirect()->route('todo.index')->with('success', '待辦已更新');
    }

    // 刪除待辦
    public function destroy($id)
    {
        $todo = Todo::findOrFail($id);
        $todo->delete();

        return redirect()->route('todo.index')->with('success', '待辦已刪除');
    }
}
```

### View 樣板

#### 列表視圖 (resources/views/todo/index.blade.php)

```blade
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>待辦清單</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <a href="{{ route('todo.create') }}" class="btn btn-primary mb-3">
        新增待辦
    </a>

    <table class="table">
        <thead>
            <tr>
                <th>標題</th>
                <th>描述</th>
                <th>狀態</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            @forelse($todos as $todo)
                <tr>
                    <td>{{ $todo->title }}</td>
                    <td>{{ $todo->description }}</td>
                    <td>
                        @if($todo->completed)
                            <span class="badge badge-success">已完成</span>
                        @else
                            <span class="badge badge-warning">未完成</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('todo.edit', $todo->id) }}" 
                           class="btn btn-sm btn-warning">編輯</a>
                        <form action="{{ route('todo.destroy', $todo->id) }}" 
                              method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">刪除</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">目前沒有待辦</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
```

#### 新增/編輯表單 (resources/views/todo/form.blade.php)

```blade
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ isset($todo) ? '編輯待辦' : '新增待辦' }}</h1>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ isset($todo) ? route('todo.update', $todo->id) : route('todo.store') }}" 
          method="POST">
        @csrf
        @if(isset($todo))
            @method('PUT')
        @endif

        <div class="form-group">
            <label for="title">標題</label>
            <input type="text" id="title" name="title" class="form-control" 
                   value="{{ $todo->title ?? '' }}" required>
        </div>

        <div class="form-group">
            <label for="description">描述</label>
            <textarea id="description" name="description" class="form-control" 
                      rows="4">{{ $todo->description ?? '' }}</textarea>
        </div>

        @if(isset($todo))
            <div class="form-check">
                <input type="checkbox" id="completed" name="completed" class="form-check-input" 
                       @if($todo->completed) checked @endif>
                <label class="form-check-label" for="completed">標記為已完成</label>
            </div>
        @endif

        <button type="submit" class="btn btn-primary mt-3">
            {{ isset($todo) ? '更新' : '建立' }}
        </button>
        <a href="{{ route('todo.index') }}" class="btn btn-secondary mt-3">取消</a>
    </form>
</div>
@endsection
```

---

## 步驟 3: 資料庫互動：Migration, Model, CRUD

### 說明
使用 Migration 建立資料庫表結構，Model 代表資料庫記錄，CRUD 進行資料操作。

### 建立 Migration

```bash
php artisan make:migration create_todos_table
```

### Migration 檔案 (database/migrations/xxxx_xx_xx_create_todos_table.php)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 執行遷移
     */
    public function up(): void
    {
        Schema::create('todos', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('completed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * 回滾遷移
     */
    public function down(): void
    {
        Schema::dropIfExists('todos');
    }
};
```

### 執行 Migration

```bash
php artisan migrate
```

### 建立 Model

```bash
php artisan make:model Todo
```

### Model (app/Models/Todo.php)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    // 允許批量賦值的欄位
    protected $fillable = [
        'title',
        'description',
        'completed',
    ];

    // 屬性類型轉換
    protected $casts = [
        'completed' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
```

### CRUD 例子（在 Tinker 中測試）

```bash
# 進入 Laravel Tinker 互動式shell
php artisan tinker

# Create (建立)
$todo = Todo::create([
    'title' => '學習 Laravel',
    'description' => '完成 Laravel 基礎教程',
]);

# Read (讀取)
$todo = Todo::find(1);
$allTodos = Todo::all();
$filteredTodos = Todo::where('completed', false)->get();

# Update (更新)
$todo = Todo::find(1);
$todo->update(['completed' => true]);

# Delete (刪除)
$todo = Todo::find(1);
$todo->delete();

# 或使用 truncate 刪除所有資料
Todo::truncate();
```

---

## 步驟 4: 表單處理：Input, Display, Delete, Run

### 說明
處理用戶輸入、驗證資料、顯示結果、及刪除操作都是完整應用的必要部分。

### 設定環境變數 (.env)

```env
APP_NAME=TodoApp
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=todo_db
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=log
```

### 表單輸入驗證 (在 Controller 中)

```php
public function store(Request $request)
{
    // 驗證輸入
    $validated = $request->validate([
        'title' => 'required|string|min:3|max:255',
        'description' => 'nullable|string|min:5|max:1000',
    ], [
        'title.required' => '標題為必填項',
        'title.min' => '標題至少 3 個字元',
        'description.min' => '描述至少 5 個字元',
    ]);

    Todo::create($validated);
    return redirect()->route('todo.index')->with('success', '待辦已建立');
}
```

### 完整的 View 範本帶表單驗證

```blade
<form action="{{ route('todo.store') }}" method="POST">
    @csrf

    <div class="form-group">
        <label for="title">標題 *</label>
        <input type="text" id="title" name="title" class="form-control 
               @error('title') is-invalid @enderror"
               value="{{ old('title') }}" required>
        @error('title')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="description">描述</label>
        <textarea id="description" name="description" class="form-control 
                  @error('description') is-invalid @enderror"
                  rows="4">{{ old('description') }}</textarea>
        @error('description')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <button type="submit" class="btn btn-primary">建立待辦</button>
</form>
```

### 刪除操作（帶確認）

```blade
<form action="{{ route('todo.destroy', $todo->id) }}" method="POST" 
      onsubmit="return confirm('確定要刪除嗎？');" style="display:inline;">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger btn-sm">刪除</button>
</form>
```

### 後端刪除邏輯

```php
public function destroy($id)
{
    $todo = Todo::findOrFail($id);
    $title = $todo->title;
    $todo->delete();

    return redirect()->route('todo.index')
                   ->with('success', "待辦 '{$title}' 已刪除");
}
```

### 完整運行指令

```bash
# 1. 建立資料庫
# 在 MySQL/MariaDB 中執行：
# CREATE DATABASE todo_db;

# 2. 設定 .env 檔案（修改資料庫設定）

# 3. 執行遷移建立表
php artisan migrate

# 4. （選擇性）建立假造資料
php artisan make:seeder TodoSeeder

# 5. 安裝前端依賴（如有需要）
npm install
npm run dev

# 6. 啟動開發伺服器
php artisan serve

# 7. 訪問應用
# 在瀏覽器開啟 http://127.0.0.1:8000
```

### 常用 Artisan 指令

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

---

### 總結

遵循這四個步驟，您已經學會了：
1. ✅ 建立和設定 Laravel 專案
2. ✅ 使用 MVC 架構構建應用（Routing, Controller, View）
3. ✅ 與資料庫互動（Migration, Model, CRUD）
4. ✅ 完整的表單處理流程（輸入、驗證、顯示、刪除）

現在您可以開發自己的 Laravel 應用了！

