# 安裝指南

這份文件提供詳細的安裝和設定步驟。

## 📋 系統要求

- **PHP 版本**: ≥ 7.4
- **MySQL/MariaDB 版本**: ≥ 5.7
- **Web 伺服器**: Apache（啟用 mod_rewrite）或其他支持URL重寫的伺服器
- **必需的 PHP 擴展**:
  - PDO
  - PDO MySQL

## 🔧 安裝步驟

### 步驟 1：檢查環境要求

在伺服器上建立一個 PHP 文件（例如 `phpinfo.php`），內容如下：

```php
<?php phpinfo(); ?>
```

訪問此文件並檢查：
- PHP 版本是否 ≥ 7.4
- `pdo` 和 `pdo_mysql` 擴展是否已啟用
- 伺服器是否為 Apache 且 `mod_rewrite` 已啟用

### 步驟 2：複製項目文件

將 `rest2` 文件夾複製到您的 Web 根目錄（通常是 `htdocs` 或 `www`）。

使用 Laragon 時，複製到：`c:\laragon\www\rest2\`

### 步驟 3：建立資料庫

#### 方法 A：使用 MySQL 命令行

```bash
mysql -u root -p
```

然後執行以下命令：

```sql
source c:\laragon\www\rest2\rusers.sql;
```

或者直接執行 SQL 文件：

```bash
mysql -u root -p < rusers.sql
```

#### 方法 B：使用 phpMyAdmin

1. 打開 phpMyAdmin（通常在 `http://localhost/phpmyadmin`）
2. 點擊「新建」建立新資料庫，名稱為 `restful_demo`
3. 選擇新資料庫
4. 點擊「匯入」標籤
5. 選擇 `rusers.sql` 文件並執行

#### 方法 C：使用 Laragon 建資料庫工具

1. 在 Laragon 中點擊「資料庫」
2. 建立新資料庫 `restful_demo`
3. 執行 `rusers.sql` SQL 語句

### 步驟 4：驗證資料庫連接

編輯 `config.php` 確認以下設定符合您的環境：

```php
define('DB_HOST', 'localhost');        // 資料庫主機
define('DB_USER', 'root');              // 資料庫使用者名稱
define('DB_PASSWORD', '');              // 資料庫密碼（Laragon 預設為空）
define('DB_NAME', 'restful_demo');      // 資料庫名稱
```

### 步驟 5：驗證 Apache 配置

確保 `.htaccess` 文件在項目根目錄中。檢查 Apache 配置是否允許 `.htaccess` 覆蓋。

在 Apache 配置文件中（`httpd.conf` 或虛擬主機配置）確認：

```apache
<Directory /path/to/rest2>
    AllowOverride All
</Directory>
```

### 步驟 6：測試 API

#### 方法 A：使用網頁客戶端

在瀏覽器中打開：

```
http://localhost/rest2/client.html
```

您應該看到漂亮的網頁界面，可以執行 CRUD 操作。

#### 方法 B：使用 PHP 測試腳本

在 PowerShell 或命令提示符中執行：

```bash
php test.php
```

這將執行一系列 API 測試並顯示結果。

#### 方法 C：使用 cURL 測試

```bash
# 取得所有使用者
curl "http://localhost/rest2/index.php/api/users"

# 建立新使用者
curl -X POST "http://localhost/rest2/index.php/api/users" ^
  -H "Content-Type: application/json" ^
  -d "{\"name\":\"測試\",\"email\":\"test@example.com\"}"

# 取得特定使用者
curl "http://localhost/rest2/index.php/api/users/1"

# 更新使用者
curl -X PUT "http://localhost/rest2/index.php/api/users/1" ^
  -H "Content-Type: application/json" ^
  -d "{\"name\":\"新名稱\"}"

# 刪除使用者
curl -X DELETE "http://localhost/rest2/index.php/api/users/1"
```

#### 方法 D：使用 Postman

1. 下載並安裝 [Postman](https://www.postman.com/downloads/)
2. 打開 Postman
3. 建立新請求
4. 選擇 HTTP 方法（GET、POST、PUT、DELETE）
5. 輸入 URL：`http://localhost/rest2/index.php/api/users`
6. 添加必要的頭部和請求體
7. 點擊「Send」執行請求

## 🐛 常見問題及解決方案

### 問題 1：`404 Not Found` 錯誤

**原因**：URL 重寫未工作

**解決方案**：
1. 檢查 `.htaccess` 文件是否存在且內容正確
2. 檢查 Apache `mod_rewrite` 是否啟用（在 Laragon 中應已啟用）
3. 在 Apache 配置中確保 `AllowOverride All` 已設置

### 問題 2：`500 Internal Server Error`

**原因**：資料庫連接失敗或 PHP 錯誤

**解決方案**：
1. 檢查 `config.php` 中的資料庫連接參數
2. 確保 MySQL 服務已啟動
3. 驗證資料庫 `restful_demo` 是否存在
4. 檢查 PHP 錯誤日誌（通常在 `logs` 文件夾中）

### 問題 3：PDO MySQL 擴展未找到

**原因**：`php_pdo_mysql` 擴展未啟用

**解決方案**：
1. 打開 PHP 配置文件 `php.ini`
2. 取消註釋此行：`;extension=pdo_mysql`（移除開頭的分號）
3. 重啟 Web 伺服器
4. 在 Laragon 中，可以在「PHP」菜單中啟用擴展

### 問題 4：電子郵件重複錯誤

**原因**：嘗試建立使用相同電子郵件的使用者

**解決方案**：
1. 使用唯一的電子郵件地址
2. 檢查資料庫中是否已存在該電子郵件
3. 如果需要重新初始化，可以執行 `rusers.sql` 重新建立表

### 問題 5：中文字符亂碼

**原因**：字符集設置不一致

**解決方案**：
1. 確保 HTML 文件包含：`<meta charset="UTF-8">`
2. 確保 MySQL 表和連接使用 `utf8mb4` 字符集
3. 檢查 Apache 配置中是否設置了正確的字符集

## 📖 驗證安裝

執行以下步驟驗證安裝是否正確：

1. **訪問 API 端點**：
   ```
   http://localhost/rest2/index.php/api/users
   ```
   應該返回 JSON 格式的使用者列表

2. **訪問網頁客戶端**：
   ```
   http://localhost/rest2/client.html
   ```
   應該看到完整的網頁界面

3. **測試建立使用者**：
   使用網頁客戶端或 cURL 建立新使用者

4. **查看資料庫**：
   在 phpMyAdmin 中查看新建立的使用者是否存在

## 🔒 生產環境設定

在將此應用部署到生產環境前，請進行以下安全設置：

1. **修改資料庫認證**：
   ```php
   define('DB_USER', 'restricted_user');
   define('DB_PASSWORD', 'strong_password');
   ```

2. **啟用 HTTPS**：
   確保所有 API 調用都通過 HTTPS

3. **新增 CORS 支持**（如需要）：
   ```php
   header('Access-Control-Allow-Origin: https://yourdomain.com');
   header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
   header('Access-Control-Allow-Headers: Content-Type');
   ```

4. **實施速率限制**：
   防止 API 濫用

5. **新增認證**：
   使用 API 密鑰或 JWT 令牌

6. **記錄和監控**：
   記錄所有 API 請求以便審計

## 📞 技術支持

如遇到問題，請檢查：

1. PHP 錯誤日誌
2. MySQL 錯誤日誌
3. Apache 存取日誌和錯誤日誌
4. 確保所有設定文件都具有正確的權限

---

**安裝指南版本**: 1.0  
**最後更新**: 2026年5月9日
