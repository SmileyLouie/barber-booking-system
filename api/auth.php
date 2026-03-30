<?php
require_once 'db.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'register':
        $name = $input['name'] ?? $_POST['name'] ?? '';
        $email = $input['email'] ?? $_POST['email'] ?? '';
        $password = password_hash($input['password'] ?? $_POST['password'] ?? '', PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'customer')");
            $stmt->execute([$name, $email, $password]);

            // Auto login
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['role'] = 'customer';
            $_SESSION['name'] = $name;

            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
        }
        break;

    case 'login':
        $email = trim($input['email'] ?? $_POST['email'] ?? '');
        $password = trim($input['password'] ?? $_POST['password'] ?? '');

        // All logins must now validate against the database for maximum security.


        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            if ($user['role'] === 'barber') {
                $_SESSION['barber_id'] = $user['linked_barber_id'];
            }
            echo json_encode(['success' => true, 'role' => $user['role'], 'barber_id' => $user['linked_barber_id'] ?? null]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        }
        break;

    case 'logout':
        session_destroy();
        echo json_encode(['success' => true]);
        break;

    case 'check':
        if (isset($_SESSION['user_id'])) {
            $resp = [
                'logged_in' => true,
                'user' => [
                    'id' => $_SESSION['user_id'],
                    'name' => $_SESSION['name'],
                    'role' => $_SESSION['role']
                ]
            ];
            if (isset($_SESSION['barber_id'])) {
                $resp['user']['barber_id'] = $_SESSION['barber_id'];
            }
            echo json_encode($resp);
        } else {
            echo json_encode(['logged_in' => false]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>