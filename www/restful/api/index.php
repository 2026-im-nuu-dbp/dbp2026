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
$resource = $segments[2] ?? '';
$id = $segments[3] ?? null;

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