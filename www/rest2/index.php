<?php
/**
 * 使用者管理 - API控制器
 * 提供CRUD操作：建立、讀取、更新、刪除
 */

require_once 'config.php';

class UserController {
    private $db;
    
    /**
     * 建構函式 - 初始化資料庫連接
     */
    public function __construct() {
        $this->db = getDatabase();
    }
    
    /**
     * 讀取所有使用者 (GET /api/users)
     * 支援查詢參數:
     * - status: 篩選狀態 (active, inactive, deleted)
     * - page: 分頁號碼 (預設: 1)
     * - limit: 每頁數量 (預設: 10)
     */
    public function getAll() {
        try {
            $status = $_GET['status'] ?? null;
            $page = max(1, intval($_GET['page'] ?? 1));
            $limit = min(100, max(1, intval($_GET['limit'] ?? 10)));
            $offset = ($page - 1) * $limit;
            
            // 基礎SQL查詢
            $sql = 'SELECT id, name, email, phone, bio, age, city, country, status, created_at, updated_at FROM rusers WHERE 1=1';
            $params = [];
            
            // 狀態篩選
            if ($status && in_array($status, ['active', 'inactive', 'deleted'])) {
                $sql .= ' AND status = ?';
                $params[] = $status;
            }
            
            // 統計總數
            $countSql = 'SELECT COUNT(*) as total FROM rusers WHERE 1=1';
            if (!empty($params)) {
                $countSql .= ' AND status = ?';
            }
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetch()['total'];
            
            // 取得分頁資料
            $sql .= ' ORDER BY created_at DESC LIMIT ? OFFSET ?';
            $stmt = $this->db->prepare($sql);
            $params[] = $limit;
            $params[] = $offset;
            $stmt->execute($params);
            $users = $stmt->fetchAll();
            
            $response = [
                'users' => $users,
                'pagination' => [
                    'total' => (int)$total,
                    'page' => $page,
                    'limit' => $limit,
                    'pages' => ceil($total / $limit)
                ]
            ];
            
            responseSuccess($response, API_STATUS_SUCCESS, '取得使用者列表成功');
        } catch (Exception $e) {
            responseError('取得使用者列表失敗: ' . $e->getMessage(), API_STATUS_SERVER_ERROR);
        }
    }
    
    /**
     * 讀取單一使用者 (GET /api/users/{id})
     * @param int $id 使用者ID
     */
    public function getById($id) {
        try {
            if (!is_numeric($id) || $id <= 0) {
                responseError('無效的使用者ID', API_STATUS_BAD_REQUEST);
            }
            
            $sql = 'SELECT id, name, email, phone, bio, age, city, country, status, created_at, updated_at FROM rusers WHERE id = ?';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $user = $stmt->fetch();
            
            if (!$user) {
                responseError('找不到該使用者', API_STATUS_NOT_FOUND);
            }
            
            responseSuccess($user, API_STATUS_SUCCESS, '取得使用者資訊成功');
        } catch (Exception $e) {
            responseError('取得使用者資訊失敗: ' . $e->getMessage(), API_STATUS_SERVER_ERROR);
        }
    }
    
    /**
     * 建立新使用者 (POST /api/users)
     * 必需欄位: name, email
     * 選用欄位: phone, bio, age, city, country
     */
    public function create() {
        try {
            $input = getJsonInput();
            
            // 驗證必需欄位
            if (empty($input['name']) || trim($input['name']) === '') {
                responseError('使用者名稱為必填項', API_STATUS_BAD_REQUEST);
            }
            
            if (empty($input['email']) || trim($input['email']) === '') {
                responseError('電子郵件為必填項', API_STATUS_BAD_REQUEST);
            }
            
            // 驗證電子郵件格式
            if (!isValidEmail($input['email'])) {
                responseError('電子郵件格式無效', API_STATUS_BAD_REQUEST);
            }
            
            // 檢查電子郵件是否已存在
            $checkSql = 'SELECT id FROM rusers WHERE email = ?';
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute([$input['email']]);
            if ($checkStmt->fetch()) {
                responseError('該電子郵件已被使用', API_STATUS_BAD_REQUEST);
            }
            
            // 準備插入資料
            $name = sanitizeInput($input['name']);
            $email = sanitizeInput($input['email']);
            $phone = sanitizeInput($input['phone'] ?? '');
            $bio = sanitizeInput($input['bio'] ?? '');
            $age = isset($input['age']) ? intval($input['age']) : null;
            $city = sanitizeInput($input['city'] ?? '');
            $country = sanitizeInput($input['country'] ?? '');
            
            $sql = 'INSERT INTO rusers (name, email, phone, bio, age, city, country, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$name, $email, $phone, $bio, $age, $city, $country, 'active']);
            
            $userId = $this->db->lastInsertId();
            
            // 取得新建的使用者資訊
            $newUser = [
                'id' => (int)$userId,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'bio' => $bio,
                'age' => $age,
                'city' => $city,
                'country' => $country,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            responseSuccess($newUser, API_STATUS_CREATED, '使用者建立成功');
        } catch (Exception $e) {
            responseError('建立使用者失敗: ' . $e->getMessage(), API_STATUS_SERVER_ERROR);
        }
    }
    
    /**
     * 更新使用者 (PUT /api/users/{id})
     * 可更新欄位: name, email, phone, bio, age, city, country, status
     * @param int $id 使用者ID
     */
    public function update($id) {
        try {
            if (!is_numeric($id) || $id <= 0) {
                responseError('無效的使用者ID', API_STATUS_BAD_REQUEST);
            }
            
            // 檢查使用者是否存在
            $checkSql = 'SELECT id FROM rusers WHERE id = ?';
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute([$id]);
            if (!$checkStmt->fetch()) {
                responseError('找不到該使用者', API_STATUS_NOT_FOUND);
            }
            
            $input = getJsonInput();
            if (empty($input)) {
                responseError('沒有提供更新資料', API_STATUS_BAD_REQUEST);
            }
            
            // 允許更新的欄位
            $allowedFields = ['name', 'email', 'phone', 'bio', 'age', 'city', 'country', 'status'];
            $updateFields = [];
            $params = [];
            
            foreach ($allowedFields as $field) {
                if (isset($input[$field])) {
                    if ($field === 'email') {
                        // 驗證電子郵件格式
                        if (!isValidEmail($input[$field])) {
                            responseError('電子郵件格式無效', API_STATUS_BAD_REQUEST);
                        }
                        
                        // 檢查新電子郵件是否已被其他使用者使用
                        $emailCheckSql = 'SELECT id FROM rusers WHERE email = ? AND id != ?';
                        $emailCheckStmt = $this->db->prepare($emailCheckSql);
                        $emailCheckStmt->execute([$input[$field], $id]);
                        if ($emailCheckStmt->fetch()) {
                            responseError('該電子郵件已被其他使用者使用', API_STATUS_BAD_REQUEST);
                        }
                    }
                    
                    if ($field === 'status') {
                        if (!in_array($input[$field], ['active', 'inactive', 'deleted'])) {
                            responseError('無效的狀態值', API_STATUS_BAD_REQUEST);
                        }
                    }
                    
                    $updateFields[] = "$field = ?";
                    $params[] = ($field === 'age') ? intval($input[$field]) : sanitizeInput($input[$field]);
                }
            }
            
            if (empty($updateFields)) {
                responseError('沒有提供有效的更新欄位', API_STATUS_BAD_REQUEST);
            }
            
            $params[] = $id;
            $sql = 'UPDATE rusers SET ' . implode(', ', $updateFields) . ', updated_at = CURRENT_TIMESTAMP WHERE id = ?';
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            // 取得更新後的使用者資訊
            $getUserSql = 'SELECT id, name, email, phone, bio, age, city, country, status, created_at, updated_at FROM rusers WHERE id = ?';
            $getUserStmt = $this->db->prepare($getUserSql);
            $getUserStmt->execute([$id]);
            $updatedUser = $getUserStmt->fetch();
            
            responseSuccess($updatedUser, API_STATUS_SUCCESS, '使用者更新成功');
        } catch (Exception $e) {
            responseError('更新使用者失敗: ' . $e->getMessage(), API_STATUS_SERVER_ERROR);
        }
    }
    
    /**
     * 刪除使用者 (DELETE /api/users/{id})
     * 支援軟刪除（標記為已刪除）和硬刪除（完全移除）
     * 查詢參數:
     * - hard=true 進行硬刪除（預設為軟刪除）
     * @param int $id 使用者ID
     */
    public function delete($id) {
        try {
            if (!is_numeric($id) || $id <= 0) {
                responseError('無效的使用者ID', API_STATUS_BAD_REQUEST);
            }
            
            // 檢查使用者是否存在
            $checkSql = 'SELECT id FROM rusers WHERE id = ?';
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute([$id]);
            if (!$checkStmt->fetch()) {
                responseError('找不到該使用者', API_STATUS_NOT_FOUND);
            }
            
            $hardDelete = isset($_GET['hard']) && $_GET['hard'] === 'true';
            
            if ($hardDelete) {
                // 硬刪除：完全從資料庫移除
                $sql = 'DELETE FROM rusers WHERE id = ?';
                $message = '使用者已完全刪除';
            } else {
                // 軟刪除：只標記狀態為 deleted
                $sql = 'UPDATE rusers SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?';
                $this->db->prepare($sql)->execute(['deleted', $id]);
                $message = '使用者已標記為已刪除';
            }
            
            if (!$hardDelete) {
                $stmt = $this->db->prepare('SELECT id, name, email, phone, bio, age, city, country, status, created_at, updated_at FROM rusers WHERE id = ?');
                $stmt->execute([$id]);
                $user = $stmt->fetch();
                responseSuccess($user, API_STATUS_SUCCESS, $message);
            } else {
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$id]);
                responseSuccess(null, API_STATUS_SUCCESS, $message);
            }
        } catch (Exception $e) {
            responseError('刪除使用者失敗: ' . $e->getMessage(), API_STATUS_SERVER_ERROR);
        }
    }
    
    /**
     * 搜尋使用者 (GET /api/users/search)
     * 查詢參數:
     * - q: 搜尋關鍵字（搜尋name和email）
     * - field: 指定搜尋欄位 (name, email, phone, city)
     */
    public function search() {
        try {
            $query = $_GET['q'] ?? '';
            $field = $_GET['field'] ?? null;
            
            if (empty($query)) {
                responseError('請提供搜尋關鍵字', API_STATUS_BAD_REQUEST);
            }
            
            $query = sanitizeInput($query);
            $searchTerm = '%' . $query . '%';
            
            if ($field && in_array($field, ['name', 'email', 'phone', 'city', 'country'])) {
                // 搜尋指定欄位
                $sql = "SELECT id, name, email, phone, bio, age, city, country, status, created_at, updated_at FROM rusers WHERE $field LIKE ? ORDER BY created_at DESC";
                $params = [$searchTerm];
            } else {
                // 預設搜尋名稱和電子郵件
                $sql = "SELECT id, name, email, phone, bio, age, city, country, status, created_at, updated_at FROM rusers WHERE name LIKE ? OR email LIKE ? ORDER BY created_at DESC";
                $params = [$searchTerm, $searchTerm];
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $users = $stmt->fetchAll();
            
            responseSuccess($users, API_STATUS_SUCCESS, '搜尋完成');
        } catch (Exception $e) {
            responseError('搜尋失敗: ' . $e->getMessage(), API_STATUS_SERVER_ERROR);
        }
    }
}

// 路由處理
try {
    $method = getRequestMethod();
    $pathInfo = $_SERVER['PATH_INFO'] ?? '';

    // Fallback to REQUEST_URI when PATH_INFO is not available.
    if ($pathInfo === '' || $pathInfo === '/') {
        $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $baseDir = str_replace('\\', '/', dirname($scriptName));

        if ($baseDir === '.' || $baseDir === '\\') {
            $baseDir = '';
        }

        if ($baseDir !== '' && strpos($requestPath, $baseDir) === 0) {
            $requestPath = substr($requestPath, strlen($baseDir));
        }

        if (strpos($requestPath, '/index.php') === 0) {
            $requestPath = substr($requestPath, strlen('/index.php'));
        }

        $pathInfo = $requestPath !== '' ? $requestPath : '/';
    }

    $pathParts = array_values(array_filter(explode('/', trim($pathInfo, '/')), 'strlen'));

    if (!empty($pathParts) && $pathParts[0] === 'index.php') {
        array_shift($pathParts);
    }
    
    $controller = new UserController();
    
    // 特殊路由：搜尋
    if (count($pathParts) === 2 && $pathParts[0] === 'api' && $pathParts[1] === 'search') {
        if ($method !== 'GET') {
            responseError('搜尋API僅支援GET方法', API_STATUS_BAD_REQUEST);
        }
        $controller->search();
    }
    // 一般路由：/api/users
    elseif (count($pathParts) === 2 && $pathParts[0] === 'api' && $pathParts[1] === 'users') {
        if ($method === 'GET') {
            $controller->getAll();
        } elseif ($method === 'POST') {
            $controller->create();
        } else {
            responseError('不支援的HTTP方法', API_STATUS_BAD_REQUEST);
        }
    }
    // 特定使用者路由：/api/users/{id}
    elseif (count($pathParts) === 3 && $pathParts[0] === 'api' && $pathParts[1] === 'users') {
        $userId = $pathParts[2];
        if ($method === 'GET') {
            $controller->getById($userId);
        } elseif ($method === 'PUT') {
            $controller->update($userId);
        } elseif ($method === 'DELETE') {
            $controller->delete($userId);
        } else {
            responseError('不支援的HTTP方法', API_STATUS_BAD_REQUEST);
        }
    }
    // 未定義的路由
    else {
        responseError('找不到該API端點', API_STATUS_NOT_FOUND);
    }
} catch (Exception $e) {
    responseError('發生錯誤: ' . $e->getMessage(), API_STATUS_SERVER_ERROR);
}
