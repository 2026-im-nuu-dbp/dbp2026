# API 快速參考

快速查詢表格和常見操作。

## 📊 API 端點速查表

| 操作 | 方法 | 端點 | 描述 |
|------|------|------|------|
| 列出所有使用者 | GET | `/api/users` | 取得使用者列表 |
| 取得單一使用者 | GET | `/api/users/{id}` | 根據ID取得使用者 |
| 建立使用者 | POST | `/api/users` | 新增使用者 |
| 更新使用者 | PUT | `/api/users/{id}` | 修改使用者資訊 |
| 刪除使用者 | DELETE | `/api/users/{id}` | 刪除使用者 |
| 搜尋使用者 | GET | `/api/search` | 搜尋使用者 |

## 🔧 常見操作示例

### 1️⃣ 建立使用者

**請求：**
```bash
curl -X POST "http://localhost/rest2/index.php/api/users" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "王小明",
    "email": "wangxiaoming@example.com",
    "phone": "0912345678",
    "age": 28,
    "city": "台北",
    "country": "台灣",
    "bio": "軟體工程師"
  }'
```

**最小必需內容：**
```json
{
  "name": "使用者名稱",
  "email": "email@example.com"
}
```

### 2️⃣ 查詢所有使用者（分頁）

**基礎查詢：**
```bash
curl "http://localhost/rest2/index.php/api/users"
```

**帶分頁：**
```bash
curl "http://localhost/rest2/index.php/api/users?page=1&limit=10"
```

**帶狀態篩選：**
```bash
curl "http://localhost/rest2/index.php/api/users?status=active&limit=20"
```

### 3️⃣ 查詢特定使用者

```bash
curl "http://localhost/rest2/index.php/api/users/1"
```

### 4️⃣ 更新使用者

**更新單個欄位：**
```bash
curl -X PUT "http://localhost/rest2/index.php/api/users/1" \
  -H "Content-Type: application/json" \
  -d '{"name": "新名稱"}'
```

**更新多個欄位：**
```bash
curl -X PUT "http://localhost/rest2/index.php/api/users/1" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "新名稱",
    "age": 30,
    "city": "台中",
    "bio": "更新後的簡介"
  }'
```

**更新狀態：**
```bash
curl -X PUT "http://localhost/rest2/index.php/api/users/1" \
  -H "Content-Type: application/json" \
  -d '{"status": "inactive"}'
```

### 5️⃣ 刪除使用者

**軟刪除（推薦，保留資料）：**
```bash
curl -X DELETE "http://localhost/rest2/index.php/api/users/1"
```

**硬刪除（完全移除資料）：**
```bash
curl -X DELETE "http://localhost/rest2/index.php/api/users/1?hard=true"
```

### 6️⃣ 搜尋使用者

**基礎搜尋（搜尋名稱和電子郵件）：**
```bash
curl "http://localhost/rest2/index.php/api/search?q=王"
```

**在特定欄位搜尋：**
```bash
curl "http://localhost/rest2/index.php/api/search?q=台北&field=city"
```

**支持的搜尋欄位：**
- `name` - 名稱
- `email` - 電子郵件
- `phone` - 電話
- `city` - 城市
- `country` - 國家

## 📋 查詢參數參考

### 用戶列表查詢參數

| 參數 | 類型 | 預設值 | 說明 |
|------|------|--------|------|
| `status` | string | - | 篩選狀態：active, inactive, deleted |
| `page` | integer | 1 | 分頁號碼 |
| `limit` | integer | 10 | 每頁結果數量（最多100） |

### 搜尋查詢參數

| 參數 | 類型 | 必需 | 說明 |
|------|------|------|------|
| `q` | string | ✓ | 搜尋關鍵字 |
| `field` | string | - | 指定搜尋欄位 |

## 📊 回應格式

### 成功回應（200）

```json
{
  "status": "success",
  "code": 200,
  "message": "操作訊息",
  "data": {},
  "timestamp": "2026-05-09 12:00:00"
}
```

### 建立成功回應（201）

```json
{
  "status": "success",
  "code": 201,
  "message": "使用者建立成功",
  "data": {
    "id": 6,
    "name": "新使用者",
    "email": "new@example.com",
    "status": "active",
    ...
  },
  "timestamp": "2026-05-09 12:00:00"
}
```

### 錯誤回應（400/404/500）

```json
{
  "status": "error",
  "code": 400,
  "message": "錯誤訊息",
  "timestamp": "2026-05-09 12:00:00"
}
```

## 🔐 使用者狀態值

| 狀態 | 說明 |
|------|------|
| `active` | 啟用中（新建使用者預設狀態） |
| `inactive` | 停用中 |
| `deleted` | 已刪除（軟刪除） |

## 💾 使用者欄位

| 欄位 | 類型 | 建立時必需 | 更新時可用 | 說明 |
|------|------|----------|----------|------|
| `id` | int | - | - | 使用者ID（自動生成） |
| `name` | string | ✓ | ✓ | 使用者名稱 |
| `email` | string | ✓ | ✓ | 電子郵件（唯一） |
| `phone` | string | - | ✓ | 電話號碼 |
| `bio` | string | - | ✓ | 個人簡介 |
| `age` | integer | - | ✓ | 年齡 |
| `city` | string | - | ✓ | 城市 |
| `country` | string | - | ✓ | 國家 |
| `status` | enum | - | ✓ | 狀態 |
| `created_at` | timestamp | - | - | 建立時間（自動） |
| `updated_at` | timestamp | - | - | 更新時間（自動） |

## ⚡ 批量操作示例

### 建立 3 個使用者

```bash
# 使用者 1
curl -X POST "http://localhost/rest2/index.php/api/users" \
  -H "Content-Type: application/json" \
  -d '{"name":"使用者1","email":"user1@example.com"}'

# 使用者 2
curl -X POST "http://localhost/rest2/index.php/api/users" \
  -H "Content-Type: application/json" \
  -d '{"name":"使用者2","email":"user2@example.com"}'

# 使用者 3
curl -X POST "http://localhost/rest2/index.php/api/users" \
  -H "Content-Type: application/json" \
  -d '{"name":"使用者3","email":"user3@example.com"}'
```

### 獲取所有啟用使用者並轉移到 CSV

```bash
curl "http://localhost/rest2/index.php/api/users?status=active&limit=1000" | \
  jq '.data.users[] | [.id, .name, .email, .city, .status] | @csv' \
  > users.csv
```

## 🐍 Python 客戶端範例

```python
import requests
import json

BASE_URL = "http://localhost/rest2/index.php/api"

# 建立使用者
def create_user(name, email):
    response = requests.post(
        f"{BASE_URL}/users",
        headers={"Content-Type": "application/json"},
        json={"name": name, "email": email}
    )
    return response.json()

# 取得所有使用者
def get_all_users():
    response = requests.get(f"{BASE_URL}/users")
    return response.json()

# 取得特定使用者
def get_user(user_id):
    response = requests.get(f"{BASE_URL}/users/{user_id}")
    return response.json()

# 更新使用者
def update_user(user_id, data):
    response = requests.put(
        f"{BASE_URL}/users/{user_id}",
        headers={"Content-Type": "application/json"},
        json=data
    )
    return response.json()

# 刪除使用者
def delete_user(user_id):
    response = requests.delete(f"{BASE_URL}/users/{user_id}")
    return response.json()

# 搜尋使用者
def search_users(keyword):
    response = requests.get(f"{BASE_URL}/search?q={keyword}")
    return response.json()

# 使用範例
if __name__ == "__main__":
    # 建立使用者
    result = create_user("李小王", "liwang@example.com")
    print(json.dumps(result, indent=2, ensure_ascii=False))
    
    # 取得所有使用者
    result = get_all_users()
    print(json.dumps(result['data']['users'], indent=2, ensure_ascii=False))
```

## 🔗 JavaScript/Node.js 客戶端範例

```javascript
const API_BASE_URL = "http://localhost/rest2/index.php/api";

// 建立使用者
async function createUser(name, email) {
  const response = await fetch(`${API_BASE_URL}/users`, {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({name, email})
  });
  return response.json();
}

// 取得所有使用者
async function getAllUsers() {
  const response = await fetch(`${API_BASE_URL}/users`);
  return response.json();
}

// 取得特定使用者
async function getUser(userId) {
  const response = await fetch(`${API_BASE_URL}/users/${userId}`);
  return response.json();
}

// 更新使用者
async function updateUser(userId, data) {
  const response = await fetch(`${API_BASE_URL}/users/${userId}`, {
    method: 'PUT',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify(data)
  });
  return response.json();
}

// 刪除使用者
async function deleteUser(userId) {
  const response = await fetch(`${API_BASE_URL}/users/${userId}`, {
    method: 'DELETE'
  });
  return response.json();
}

// 搜尋使用者
async function searchUsers(keyword) {
  const response = await fetch(
    `${API_BASE_URL}/search?q=${encodeURIComponent(keyword)}`
  );
  return response.json();
}

// 使用範例
(async () => {
  // 建立使用者
  const createResult = await createUser("李小王", "liwang@example.com");
  console.log(createResult);
  
  // 取得所有使用者
  const allUsers = await getAllUsers();
  console.log(allUsers.data.users);
})();
```

## 📱 使用 Postman 的提示

1. **設置環境變數**：
   - 設置 `base_url = http://localhost/rest2/index.php`

2. **建立集合**：
   - 在 Postman 中建立「Users API」集合
   - 為每個端點建立請求

3. **測試腳本**：
   ```javascript
   // 驗證回應狀態
   pm.test("Status code is 200", function () {
       pm.response.to.have.status(200);
   });
   
   // 驗證回應格式
   pm.test("Response has success status", function () {
       var jsonData = pm.response.json();
       pm.expect(jsonData.status).to.equal("success");
   });
   ```

---

**快速參考版本**: 1.0  
**最後更新**: 2026年5月9日
