<?php
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if($_GET['state'] == '404') {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
        exit;
    }
    else if($_GET['state'] == '200') {
        http_response_code(200);
        echo json_encode(['id' => 1, 'name' => 'John']);
        exit;
    }
    else if($_GET['state'] == '400') {
        http_response_code(400);
        echo json_encode(['error' => 'Bad Request']);
        exit;
    }else if($_GET['state'] == '201') {
        http_response_code(201);
        echo json_encode(['id' => 1, 'name' => 'John']);
        exit;
    }
    else {
        http_response_code(500);
        echo json_encode(['error' => 'Internal Server Error']);
        exit;
    }
}
