# RESTful API 基礎架構（使用 PHP）

REST (Representational State Transfer) 是一種設計風格，用於建立可擴展、維護性高的 Web API。本文將詳細介紹 RESTful 的核心概念與 PHP 實作。

## 目錄

1. [REST 核心概念](#1-rest-核心概念)
2. [HTTP 方法與 CRUD](#2-http-方法與-crud)
3. [URL 設計原則](#3-url-設計原則)
4. [HTTP 狀態碼](#4-http-狀態碼)
5. [Request 與 Response 格式](#5-request-與-response-格式)
6. [PHP 基礎實作](#6-php-基礎實作)
7. [完整 API 範例](#7-完整-api-範例)
8. [最佳實踐](#8-最佳實踐)

---

## 1. REST 核心概念

### 1.1 什麼是 REST？

REST 是一種**架構風格**，不是協定或標準。它定義了一組約束條件，讓 API 更具可讀性、可維護性與擴展性。

### 1.2 REST 的六大原則

| 原則 | 說明 |
|------|------|
| **Client-Server** | 客戶端與伺服器分離 |
| **Stateless** | 無狀態，每個請求都包含所需資訊 |
| **Cacheable** | 回應可被快取 |
| **Uniform Interface** | 統一介面（URL、HTTP 方法） |
| **Layered System** | 分層系統架構 |
| **Code on Demand** | 可選，伺服器可傳送可執行代碼 |

### 1.3 RESTful API 的特性

- **資源導向**：URL 代表資源（名詞），不是動作（動詞）
- **使用標準 HTTP 方法**：GET、POST、PUT、DELETE 等
- **無狀態**：伺服器不儲存客戶端狀態
- **使用 JSON**：輕量級資料交換格式

---

## 2. HTTP 方法與 CRUD

### 2.1 HTTP 方法對應 CRUD 操作

| HTTP 方法 | CRUD 操作 | 說明 | 是否冪等 |
|-----------|----------|------|---------|
| **GET** | Read | 讀取資源 | ✓ |
| **POST** | Create | 新增資源 | ✗ |
| **PUT** | Update | 更新整個資源 | ✓ |
| **PATCH** | Update | 更新部分資源 | ✗ |
| **DELETE** | Delete | 刪除資源 | ✓ |

**冪等性 (Idempotent)**：多次執行相同操作的結果與執行一次相同。

### 2.2 範例：用戶管理

```
GET    /api/users          # 取得所有使用者
GET    /api/users/1        # 取得 ID 為 1 的使用者
POST   /api/users          # 新增使用者
PUT    /api/users/1        # 更新 ID 為 1 的使用者
PATCH  /api/users/1        # 部分更新 ID 為 1 的使用者
DELETE /api/users/1        # 刪除 ID 為 1 的使用者
```

---

## 3. URL 設計原則

### 3.1 基本規則

| 規則 | 正確 ✓ | 錯誤 ✗ |
|------|---------|--------|
| 使用名詞，不用動詞 | `/users` | `/getUsers` |
| 使用複數形式 | `/products` | `/product` |
| 使用小寫字母 | `/orders` | `/Orders` |
| 使用連字符，不用底線 | `/order-items` | `/order_items` |
| 避免深層嵌套 | `/users/1/orders` | `/users/1/profile/settings/orders` |

### 3.2 資源層級結構

```
/users                      # 所有使用者
/users/123                  # 特定使用者
/users/123/orders           # 該使用者的所有訂單
/users/123/orders/456       # 該使用者的特定訂單
```

### 3.3 查詢參數

```
GET /api/products?category=electronics&sort=price&order=asc&page=2&limit=20
```

- `category=electronics` - 篩選條件
- `sort=price` - 排序欄位
- `order=asc` - 排序方向
- `page=2` - 分頁
- `limit=20` - 每頁數量

---

## 4. HTTP 狀態碼

### 4.1 常用狀態碼

| 狀態碼 | 名稱 | 說明 | 使用時機 |
|--------|------|------|---------|
| **200** | OK | 成功 | GET、PUT 成功 |
| **201** | Created | 已建立 | POST 成功建立資源 |
| **204** | No Content | 無內容 | DELETE 成功，無回傳 |
| **400** | Bad Request | 錯誤請求 | 參數驗證失敗 |
| **401** | Unauthorized | 未授權 | 需要身分驗證 |
| **403** | Forbidden | 禁止存取 | 無權限 |
| **404** | Not Found | 找不到 | 資源不存在 |
| **409** | Conflict | 衝突 | 資源重複 |
| **422** | Unprocessable Entity | 無法處理 | 語義錯誤 |
| **500** | Internal Server Error | 伺服器錯誤 | 系統異常 |

### 4.2 狀態碼使用範例

```php
// 200 - 成功取得資源
http_response_code(200);
echo json_encode(['id' => 1, 'name' => 'John']);

// 201 - 成功建立資源
http_response_code(201);
echo json_encode(['id' => 10, 'message' => 'User created']);

// 400 - 參數錯誤
http_response_code(400);
echo json_encode(['error' => 'Invalid email format']);

// 404 - 資源不存在
http_response_code(404);
echo json_encode(['error' => 'User not found']);
```

---

## 5. Request 與 Response 格式

### 5.1 Request 格式

**GET 請求：**
```
GET /api/users/1 HTTP/1.1
Host: example.com
Accept: application/json
Authorization: Bearer your_token_here
```

**POST 請求：**
```
POST /api/users HTTP/1.1
Host: example.com
Content-Type: application/json
Authorization: Bearer your_token_here

{
  "name": "John Doe",
  "email": "john@example.com",
  "age": 30
}
```

### 5.2 Response 格式

**成功回應：**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  }
}
```

**錯誤回應：**
```json
{
  "status": "error",
  "message": "User not found",
  "code": 404
}
```

**列表回應（含分頁）：**
```json
{
  "status": "success",
  "data": [
    {"id": 1, "name": "John"},
    {"id": 2, "name": "Jane"}
  ],
  "meta": {
    "page": 1,
    "per_page": 10,
    "total": 50,
    "total_pages": 5
  }
}
```

---

## 6. PHP 基礎實作

### 6.1 取得 HTTP 方法

```php
<?php
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        handleGet();
        break;
    case 'POST':
        handlePost();
        break;
    case 'PUT':
        handlePut();
        break;
    case 'DELETE':
        handleDelete();
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
?>
```

### 6.2 解析 Request Body

```php
<?php
// 取得 JSON 格式的請求資料
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}
?>
```

### 6.3 解析 URL 參數

```php
<?php
// URL: /api/users/123
$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);
$segments = explode('/', trim($path, '/'));

// $segments[0] = 'api'
// $segments[1] = 'users'
// $segments[2] = '123'

$resource = $segments[1] ?? '';
$id = $segments[2] ?? null;
?>
```

### 6.4 回傳 JSON

```php
<?php
header('Content-Type: application/json; charset=utf-8');

$response = [
    'status' => 'success',
    'data' => ['id' => 1, 'name' => 'John']
];

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
```

---

## 7. 完整 API 範例

### 7.1 專案結構

```
api/
├── index.php           # 主入口點
├── config.php          # 資料庫配置
├── controllers/
│   └── UserController.php
├── models/
│   └── User.php
└── .htaccess           # URL 重寫規則
```

### 7.2 .htaccess（URL 重寫）

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

### 7.3 config.php（資料庫配置）

```php
<?php
// config.php
$host = 'localhost';
$db = 'api_demo';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}
?>
```

### 7.4 index.php（主路由）

```php
<?php
// index.php
header('Content-Type: application/json; charset=utf-8');
require 'config.php';
require 'controllers/UserController.php';

// 解析 URL
$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);
$segments = explode('/', trim($path, '/'));

// 取得資源與 ID
$resource = $segments[1] ?? '';
$id = $segments[2] ?? null;

// 取得 HTTP 方法
$method = $_SERVER['REQUEST_METHOD'];

// 路由分發
if ($resource === 'users') {
    $controller = new UserController($pdo);
    
    switch ($method) {
        case 'GET':
            if ($id) {
                $controller->getUser($id);
            } else {
                $controller->getUsers();
            }
            break;
            
        case 'POST':
            $controller->createUser();
            break;
            
        case 'PUT':
            if ($id) {
                $controller->updateUser($id);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'User ID required']);
            }
            break;
            
        case 'DELETE':
            if ($id) {
                $controller->deleteUser($id);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'User ID required']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Resource not found']);
}
?>
```

### 7.5 UserController.php（控制器）

```php
<?php
// controllers/UserController.php

class UserController
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // 取得所有使用者（支援分頁與搜尋）
    public function getUsers()
    {
        $page = $_GET['page'] ?? 1;
        $limit = $_GET['limit'] ?? 10;
        $search = $_GET['search'] ?? '';
        
        $offset = ($page - 1) * $limit;

        $sql = "SELECT id, name, email FROM users";
        $params = [];

        if ($search) {
            $sql .= " WHERE name LIKE ? OR email LIKE ?";
            $params = ["%$search%", "%$search%"];
        }

        // 計算總數
        $countStmt = $this->pdo->prepare(str_replace('SELECT id, name, email', 'SELECT COUNT(*) as total', $sql));
        $countStmt->execute($params);
        $total = $countStmt->fetch()['total'];

        // 分頁查詢
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = (int) $limit;
        $params[] = (int) $offset;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll();

        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'data' => $users,
            'meta' => [
                'page' => (int) $page,
                'per_page' => (int) $limit,
                'total' => (int) $total,
                'total_pages' => ceil($total / $limit)
            ]
        ]);
    }

    // 取得單一使用者
    public function getUser($id)
    {
        $stmt = $this->pdo->prepare("SELECT id, name, email FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if (!$user) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'User not found']);
            return;
        }

        http_response_code(200);
        echo json_encode(['status' => 'success', 'data' => $user]);
    }

    // 新增使用者
    public function createUser()
    {
        $input = json_decode(file_get_contents('php://input'), true);

        // 驗證輸入
        if (!isset($input['name']) || !isset($input['email'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Name and email are required']);
            return;
        }

        $name = trim($input['name']);
        $email = trim($input['email']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
            return;
        }

        // 檢查信箱是否已存在
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => 'Email already exists']);
            return;
        }

        // 插入資料
        $stmt = $this->pdo->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
        $stmt->execute([$name, $email]);
        $id = $this->pdo->lastInsertId();

        http_response_code(201);
        echo json_encode([
            'status' => 'success',
            'message' => 'User created',
            'data' => ['id' => $id, 'name' => $name, 'email' => $email]
        ]);
    }

    // 更新使用者
    public function updateUser($id)
    {
        $input = json_decode(file_get_contents('php://input'), true);

        // 檢查使用者是否存在
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'User not found']);
            return;
        }

        $name = $input['name'] ?? null;
        $email = $input['email'] ?? null;

        if (!$name && !$email) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'No data to update']);
            return;
        }

        // 建立動態更新語句
        $fields = [];
        $params = [];

        if ($name) {
            $fields[] = 'name = ?';
            $params[] = trim($name);
        }

        if ($email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
                return;
            }
            $fields[] = 'email = ?';
            $params[] = trim($email);
        }

        $params[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'User updated']);
    }

    // 刪除使用者
    public function deleteUser($id)
    {
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'User not found']);
            return;
        }

        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);

        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'User deleted']);
    }
}
?>
```

### 7.6 資料庫結構

```sql
CREATE DATABASE api_demo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE api_demo;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 插入測試資料
INSERT INTO users (name, email) VALUES
('John Doe', 'john@example.com'),
('Jane Smith', 'jane@example.com'),
('Bob Johnson', 'bob@example.com');
```

---

## 8. 最佳實踐

### 8.1 版本控制

在 URL 中加入版本號碼：

```
/api/v1/users
/api/v2/users
```

### 8.2 身分驗證與授權

**JWT Token 範例：**

```php
<?php
// 驗證 Token
$headers = getallheaders();
$token = $headers['Authorization'] ?? '';

if (strpos($token, 'Bearer ') === 0) {
    $token = substr($token, 7);
    // 驗證 JWT token
    // ...
} else {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
?>
```

### 8.3 錯誤處理

```php
<?php
try {
    // 執行操作
} catch (PDOException $e) {
    error_log($e->getMessage()); // 記錄錯誤
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Internal server error']);
}
?>
```

### 8.4 CORS 設定

```php
<?php
// 允許跨域請求
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// 處理 OPTIONS 請求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
?>
```

### 8.5 速率限制

```php
<?php
// 簡單的速率限制（使用 session）
session_start();

$limit = 100; // 每小時 100 次
$key = 'api_calls_' . $_SERVER['REMOTE_ADDR'];

if (!isset($_SESSION[$key])) {
    $_SESSION[$key] = ['count' => 0, 'reset' => time() + 3600];
}

if (time() > $_SESSION[$key]['reset']) {
    $_SESSION[$key] = ['count' => 0, 'reset' => time() + 3600];
}

$_SESSION[$key]['count']++;

if ($_SESSION[$key]['count'] > $limit) {
    http_response_code(429);
    echo json_encode(['error' => 'Rate limit exceeded']);
    exit;
}
?>
```

### 8.6 API 文件

使用 Swagger/OpenAPI 規格：

```yaml
openapi: 3.0.0
info:
  title: User API
  version: 1.0.0
paths:
  /api/users:
    get:
      summary: Get all users
      responses:
        '200':
          description: Successful response
```

---

## 9. 測試 API

### 9.1 使用 cURL

提醒： windows CMD (命令提示字元) 跳行需使用 ^
```bash
# GET - 取得所有使用者
curl http://localhost/restful/api/users

# GET - 取得特定使用者
curl http://localhost/restful/api/users/1

# POST - 新增使用者
curl -X POST http://localhost/restful/api/users ^
  -H "Content-Type: application/json" ^
  -d '{"name":"Alice","email":"alice@example.com"}'

# PUT - 更新使用者
curl -X PUT http://localhost/restful/api/users/1 ^
  -H "Content-Type: application/json" ^
  -d '{"name":"Alice Updated"}'

# DELETE - 刪除使用者
curl -X DELETE http://localhost/restful/api/users/1
```


### 9.2 使用 Postman

1. 建立新的 Collection
2. 新增 Request
3. 設定 HTTP Method 與 URL
4. 在 Body 中輸入 JSON 資料
5. 點擊 Send

---

## 10. 小結

### RESTful API 設計檢查清單

- [ ] 使用名詞表示資源
- [ ] 使用標準 HTTP 方法（GET、POST、PUT、DELETE）
- [ ] 使用正確的 HTTP 狀態碼
- [ ] 使用 JSON 格式
- [ ] 提供分頁與篩選功能
- [ ] 實作錯誤處理
- [ ] 考慮身分驗證與授權
- [ ] 設定 CORS
- [ ] 實作速率限制
- [ ] 撰寫 API 文件

### 延伸學習

- **框架**：考慮使用 Laravel、Slim、Lumen 等 PHP 框架
- **資料庫**：學習 ORM（如 Eloquent）
- **快取**：使用 Redis 或 Memcached
- **容器化**：使用 Docker 部署 API
- **持續整合**：使用 GitHub Actions 或 GitLab CI

RESTful API 是現代 Web 開發的基石，掌握它將大幅提升你的後端開發能力！
