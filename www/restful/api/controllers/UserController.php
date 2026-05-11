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
        $stmt = $this->pdo->prepare($sql);

        // 逐一綁定，LIMIT/OFFSET 必須明確指定整數型態
        $paramIndex = 1;
        foreach ($params as $param) {
            $stmt->bindValue($paramIndex++, $param);
        }
        $stmt->bindValue($paramIndex++, (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue($paramIndex,   (int) $offset, PDO::PARAM_INT);
        $stmt->execute();
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