<?php
/**
 * API 測試範例腳本
 * 使用PHP native函數測試API端點
 * 
 * 使用方式：
 * php test.php
 */

// 設定API基礎URL
define('API_BASE_URL', 'http://localhost/rest2/index.php/api');

/**
 * 發送HTTP請求的通用函數
 */
function sendRequest($method, $endpoint, $data = null) {
    $url = API_BASE_URL . $endpoint;
    
    $options = [
        'http' => [
            'method' => $method,
            'header' => 'Content-Type: application/json' . PHP_EOL,
            'timeout' => 10
        ]
    ];
    
    if ($data !== null) {
        $options['http']['content'] = json_encode($data);
    }
    
    $context = stream_context_create($options);
    
    try {
        $response = file_get_contents($url, false, $context);
        return json_decode($response, true);
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}

/**
 * 漂亮打印JSON
 */
function printResponse($title, $response) {
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "📌 " . $title . "\n";
    echo str_repeat("=", 60) . "\n";
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
}

// ============================================================
// API 測試範例
// ============================================================

echo "\n🚀 RESTful API 測試開始\n";
echo "基礎URL: " . API_BASE_URL . "\n";

// 1. 取得所有使用者
echo "\n\n【1】取得所有使用者\n";
$response = sendRequest('GET', '/users');
printResponse('GET /api/users', $response);

// 2. 取得單一使用者
echo "\n\n【2】取得單一使用者\n";
$response = sendRequest('GET', '/users/1');
printResponse('GET /api/users/1', $response);

// 3. 建立新使用者
echo "\n\n【3】建立新使用者\n";
$newUser = [
    'name' => '陳測試',
    'email' => 'chentest_' . time() . '@example.com',
    'phone' => '0966666666',
    'age' => 26,
    'city' => '台南',
    'country' => '台灣',
    'bio' => '測試使用者'
];
$response = sendRequest('POST', '/users', $newUser);
printResponse('POST /api/users', $response);

// 保存新建立的使用者ID用於後續測試
$createdUserId = null;
if (isset($response['data']['id'])) {
    $createdUserId = $response['data']['id'];
}

// 4. 更新使用者（如果成功建立了新使用者）
if ($createdUserId) {
    echo "\n\n【4】更新使用者\n";
    $updateData = [
        'name' => '陳測試（已更新）',
        'age' => 27
    ];
    $response = sendRequest('PUT', '/users/' . $createdUserId, $updateData);
    printResponse('PUT /api/users/' . $createdUserId, $response);
}

// 5. 搜尋使用者
echo "\n\n【5】搜尋使用者\n";
$response = sendRequest('GET', '/search?q=台北');
printResponse('GET /api/search?q=台北', $response);

// 6. 依狀態篩選使用者
echo "\n\n【6】依狀態篩選使用者\n";
$response = sendRequest('GET', '/users?status=active&limit=5');
printResponse('GET /api/users?status=active&limit=5', $response);

// 7. 刪除使用者（軟刪除）
if ($createdUserId) {
    echo "\n\n【7】刪除使用者（軟刪除）\n";
    $response = sendRequest('DELETE', '/users/' . $createdUserId);
    printResponse('DELETE /api/users/' . $createdUserId, $response);
}

echo "\n\n✅ API 測試完成\n\n";

// 顯示使用統計
echo str_repeat("=", 60) . "\n";
echo "📊 測試統計\n";
echo str_repeat("=", 60) . "\n";
echo "已執行測試: 7 個\n";
echo "API基礎URL: " . API_BASE_URL . "\n";
echo "網頁客戶端: http://localhost/rest2/client.html\n";
echo "\n";
