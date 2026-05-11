# RESTful API 教學範例

一個完整的RESTful API教學專案，使用PHP和MySQL來示範CRUD操作（建立、讀取、更新、刪除）使用者資料。

## 📋 專案結構

```
rest2/
├── rusers.sql          # 資料庫結構和初始資料
├── config.php          # 資料庫連接和通用函數
├── index.php           # RESTful API主要邏輯
├── client.html         # 網頁測試客戶端
└── README.md           # 本文件
```

## 🗄️ 資料庫設計

### 資料表: `users`

| 欄位 | 類型 | 說明 |
|------|------|------|
| `id` | INT | 主鍵，自動遞增 |
| `name` | VARCHAR(100) | 使用者名稱（必填） |
| `email` | VARCHAR(120) | 電子郵件（必填，唯一） |
| `phone` | VARCHAR(20) | 電話號碼 |
| `bio` | TEXT | 個人簡介 |
| `age` | INT | 年齡 |
| `city` | VARCHAR(50) | 城市 |
| `country` | VARCHAR(50) | 國家 |
| `status` | ENUM | 狀態：active, inactive, deleted |
| `created_at` | TIMESTAMP | 建立時間（自動設置） |
| `updated_at` | TIMESTAMP | 更新時間（自動更新） |

### 索引設計

為了優化查詢效能，表中包含以下索引：

- `email` - 加速電子郵件查詢和驗證唯一性
- `status` - 加速狀態篩選
- `created_at` - 加速排序和時間範圍查詢

## 🚀 快速開始

### 1. 初始化資料庫

```sql
-- 使用MySQL命令行或phpMyAdmin執行以下命令
mysql -u root -p < rusers.sql

-- 或在phpMyAdmin中複製rusers.sql的內容並執行
```

### 2. 驗證設定

編輯 `config.php` 確認資料庫連接參數：

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'restful_demo');
```

### 3. 訪問測試客戶端

在瀏覽器中打開：

```
http://localhost/rest2/client.html
```

## 📚 API 端點文件

### 1. 讀取所有使用者

```http
GET /rest2/index.php/api/users
```

**查詢參數:**
- `status` - 篩選狀態 (active, inactive, deleted) - 可選
- `page` - 分頁號碼 (預設: 1) - 可選
- `limit` - 每頁數量 (預設: 10) - 可選

**範例:**
```bash
curl "http://localhost/rest2/index.php/api/users?status=active&page=1&limit=10"
```

**成功回應 (200):**
```json
{
  "status": "success",
  "code": 200,
  "message": "取得使用者列表成功",
  "data": {
    "users": [
      {
        "id": 1,
        "name": "張小明",
        "email": "ming@example.com",
        "phone": "0912345678",
        "bio": "軟體開發工程師",
        "age": 28,
        "city": "台北",
        "country": "台灣",
        "status": "active",
        "created_at": "2026-05-09 12:00:00",
        "updated_at": "2026-05-09 12:00:00"
      }
    ],
    "pagination": {
      "total": 5,
      "page": 1,
      "limit": 10,
      "pages": 1
    }
  },
  "timestamp": "2026-05-09 12:00:00"
}
```

### 2. 讀取單一使用者

```http
GET /rest2/index.php/api/users/{id}
```

**參數:**
- `id` - 使用者ID

**範例:**
```bash
curl "http://localhost/rest2/index.php/api/users/1"
```

**成功回應 (200):**
```json
{
  "status": "success",
  "code": 200,
  "message": "取得使用者資訊成功",
  "data": {
    "id": 1,
    "name": "張小明",
    "email": "ming@example.com",
    "phone": "0912345678",
    "bio": "軟體開發工程師",
    "age": 28,
    "city": "台北",
    "country": "台灣",
    "status": "active",
    "created_at": "2026-05-09 12:00:00",
    "updated_at": "2026-05-09 12:00:00"
  },
  "timestamp": "2026-05-09 12:00:00"
}
```

### 3. 建立新使用者

```http
POST /rest2/index.php/api/users
Content-Type: application/json
```

**必需欄位:**
- `name` - 使用者名稱
- `email` - 電子郵件

**選用欄位:**
- `phone` - 電話號碼
- `bio` - 個人簡介
- `age` - 年齡
- `city` - 城市
- `country` - 國家

**範例:**
```bash
curl -X POST "http://localhost/rest2/index.php/api/users" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "王新用戶",
    "email": "newuser@example.com",
    "phone": "0987654321",
    "bio": "新使用者",
    "age": 25,
    "city": "新竹",
    "country": "台灣"
  }'
```

**成功回應 (201):**
```json
{
  "status": "success",
  "code": 201,
  "message": "使用者建立成功",
  "data": {
    "id": 6,
    "name": "王新用戶",
    "email": "newuser@example.com",
    "phone": "0987654321",
    "bio": "新使用者",
    "age": 25,
    "city": "新竹",
    "country": "台灣",
    "status": "active",
    "created_at": "2026-05-09 12:30:00",
    "updated_at": "2026-05-09 12:30:00"
  },
  "timestamp": "2026-05-09 12:30:00"
}
```

**失敗回應 (400):**
```json
{
  "status": "error",
  "code": 400,
  "message": "該電子郵件已被使用",
  "timestamp": "2026-05-09 12:30:00"
}
```

### 4. 更新使用者

```http
PUT /rest2/index.php/api/users/{id}
Content-Type: application/json
```

**參數:**
- `id` - 使用者ID

**可更新欄位:**
- `name` - 名稱
- `email` - 電子郵件
- `phone` - 電話
- `bio` - 簡介
- `age` - 年齡
- `city` - 城市
- `country` - 國家
- `status` - 狀態 (active, inactive, deleted)

**範例:**
```bash
curl -X PUT "http://localhost/rest2/index.php/api/users/1" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "張小明（已更新）",
    "age": 29,
    "city": "台北市"
  }'
```

**成功回應 (200):**
```json
{
  "status": "success",
  "code": 200,
  "message": "使用者更新成功",
  "data": {
    "id": 1,
    "name": "張小明（已更新）",
    "email": "ming@example.com",
    "phone": "0912345678",
    "bio": "軟體開發工程師",
    "age": 29,
    "city": "台北市",
    "country": "台灣",
    "status": "active",
    "created_at": "2026-05-09 12:00:00",
    "updated_at": "2026-05-09 12:45:00"
  },
  "timestamp": "2026-05-09 12:45:00"
}
```

### 5. 刪除使用者

```http
DELETE /rest2/index.php/api/users/{id}
```

**參數:**
- `id` - 使用者ID

**查詢參數:**
- `hard=true` - 進行硬刪除（完全從資料庫移除，預設為軟刪除）

**軟刪除範例 (預設):**
```bash
curl -X DELETE "http://localhost/rest2/index.php/api/users/1"
```

**硬刪除範例:**
```bash
curl -X DELETE "http://localhost/rest2/index.php/api/users/1?hard=true"
```

**軟刪除成功回應 (200):**
```json
{
  "status": "success",
  "code": 200,
  "message": "使用者已標記為已刪除",
  "data": {
    "id": 1,
    "name": "張小明",
    "email": "ming@example.com",
    "phone": "0912345678",
    "bio": "軟體開發工程師",
    "age": 28,
    "city": "台北",
    "country": "台灣",
    "status": "deleted",
    "created_at": "2026-05-09 12:00:00",
    "updated_at": "2026-05-09 13:00:00"
  },
  "timestamp": "2026-05-09 13:00:00"
}
```

### 6. 搜尋使用者

```http
GET /rest2/index.php/api/search
```

**查詢參數:**
- `q` - 搜尋關鍵字（必需）
- `field` - 指定搜尋欄位 (name, email, phone, city, country) - 可選

**範例:**
```bash
curl "http://localhost/rest2/index.php/api/search?q=台北"

curl "http://localhost/rest2/index.php/api/search?q=ming&field=name"
```

**成功回應 (200):**
```json
{
  "status": "success",
  "code": 200,
  "message": "搜尋完成",
  "data": [
    {
      "id": 1,
      "name": "張小明",
      "email": "ming@example.com",
      "phone": "0912345678",
      "bio": "軟體開發工程師",
      "age": 28,
      "city": "台北",
      "country": "台灣",
      "status": "active",
      "created_at": "2026-05-09 12:00:00",
      "updated_at": "2026-05-09 12:00:00"
    }
  ],
  "timestamp": "2026-05-09 12:00:00"
}
```

## 🧪 使用cURL進行測試

### 建立使用者
```bash
curl -X POST "http://localhost/rest2/index.php/api/users" \
  -H "Content-Type: application/json" \
  -d '{"name":"李小王","email":"liwang@example.com","age":30}'
```

### 查詢所有使用者
```bash
curl "http://localhost/rest2/index.php/api/users"
```

### 查詢特定使用者
```bash
curl "http://localhost/rest2/index.php/api/users/1"
```

### 更新使用者
```bash
curl -X PUT "http://localhost/rest2/index.php/api/users/1" \
  -H "Content-Type: application/json" \
  -d '{"name":"李小王（已更新）"}'
```

### 刪除使用者
```bash
curl -X DELETE "http://localhost/rest2/index.php/api/users/1"
```

### 搜尋使用者
```bash
curl "http://localhost/rest2/index.php/api/search?q=台灣"
```

## 🔒 安全特性

1. **輸入驗證** - 所有輸入都經過驗證
2. **輸入清理** - 使用 `sanitizeInput()` 防止XSS攻擊
3. **參數化查詢** - 使用PDO預備陳述式防止SQL注入
4. **郵件驗證** - 驗證電子郵件格式和唯一性
5. **狀態管理** - 軟刪除保留資料審計追蹤
6. **錯誤處理** - 適當的HTTP狀態碼和錯誤訊息

## 📊 HTTP 狀態碼

| 狀態碼 | 含義 |
|-------|------|
| 200 | 成功 |
| 201 | 資源已建立 |
| 400 | 請求錯誤（驗證失敗、缺少必需欄位等） |
| 404 | 資源未找到 |
| 500 | 伺服器內部錯誤 |

## 🎓 教學重點

這個專案展示了以下RESTful API概念：

### 1. **HTTP方法對應**
- `GET` - 讀取資源
- `POST` - 建立新資源
- `PUT` - 更新現有資源
- `DELETE` - 刪除資源

### 2. **RESTful 命名約定**
- 使用複數名詞表示集合：`/api/users`
- 使用ID訪問特定資源：`/api/users/{id}`
- 使用查詢參數進行篩選和分頁：`?status=active&page=1`

### 3. **標準JSON回應格式**
```json
{
  "status": "success|error",
  "code": 200,
  "message": "操作訊息",
  "data": {},
  "timestamp": "ISO 8601 格式"
}
```

### 4. **資料庫最佳實踐**
- 主鍵設計
- 時間戳記追蹤
- 索引優化
- 軟刪除實現
- 資料驗證

### 5. **PHP安全實踐**
- PDO預備陳述式
- 輸入清理
- 錯誤例外處理
- 適當的HTTP標頭

## 🔧 自訂和擴展

### 新增欄位

1. 修改 `rusers.sql` 在users表中新增欄位
2. 在 `index.php` 的 `create()` 和 `update()` 方法中新增欄位驗證

### 新增驗證規則

在 `config.php` 中新增驗證函數，然後在控制器中調用

### 新增API端點

在 `index.php` 的路由部分新增新的條件分支

## 📝 注意事項

- 確保PHP版本 ≥ 7.4
- 確保啟用MySQL/MariaDB支援
- 確保 `php_pdo` 和 `php_pdo_mysql` 擴展已啟用
- 在生產環境中使用強密碼和SSL

## 💡 進一步學習

- 新增使用者認證（JWT、OAuth）
- 新增API速率限制
- 新增詳細的請求日誌
- 實現版本控制 (`/api/v1/users`)
- 新增更多複雜的查詢選項

---

**版本:** 1.0  
**最後更新:** 2026年5月9日  
**作者:** RESTful API 教學專案
