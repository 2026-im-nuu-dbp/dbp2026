<?php
/**
 * 資料庫連接設定
 * RESTful API 教學範例
 */

// 資料庫連接設定
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'test_db');
define('DB_CHARSET', 'utf8mb4');

// API 回應標準設定
define('API_VERSION', '1.0');
define('API_STATUS_SUCCESS', 200);
define('API_STATUS_CREATED', 201);
define('API_STATUS_BAD_REQUEST', 400);
define('API_STATUS_NOT_FOUND', 404);
define('API_STATUS_SERVER_ERROR', 500);

/**
 * 建立資料庫連接
 * @return PDO 資料庫連接物件
 */
function getDatabase() {
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
        return $pdo;
    } catch (PDOException $e) {
        responseError('資料庫連接失敗: ' . $e->getMessage(), API_STATUS_SERVER_ERROR);
    }
}

/**
 * 成功的API回應
 * @param mixed $data 回應資料
 * @param int $statusCode HTTP狀態碼
 * @param string $message 訊息
 */
function responseSuccess($data = null, $statusCode = API_STATUS_SUCCESS, $message = '成功') {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    
    $response = [
        'status' => 'success',
        'code' => $statusCode,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * 錯誤的API回應
 * @param string $message 錯誤訊息
 * @param int $statusCode HTTP狀態碼
 */
function responseError($message = '錯誤', $statusCode = API_STATUS_BAD_REQUEST) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    
    $response = [
        'status' => 'error',
        'code' => $statusCode,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * 驗證郵件格式
 * @param string $email 電子郵件
 * @return bool
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * 清理輸入資料
 * @param mixed $data 待清理資料
 * @return mixed
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(strip_tags($data), ENT_QUOTES, 'UTF-8');
}

/**
 * 取得請求的HTTP方法
 * @return string
 */
function getRequestMethod() {
    return strtoupper($_SERVER['REQUEST_METHOD']);
}

/**
 * 取得JSON請求體資料
 * @return array
 */
function getJsonInput() {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    return $data ?? [];
}
