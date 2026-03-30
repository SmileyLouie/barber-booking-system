<?php
require_once 'db.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? $_POST['action'] ?? $_GET['action'] ?? 'get';

try {
    if ($action === 'get') {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }
        $stmt = $pdo->query("SELECT user_id, name, email, role FROM users ORDER BY user_id DESC");
        echo json_encode($stmt->fetchAll());
    } else if ($action === 'delete') {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }
        $id = $input['user_id'] ?? 0;
        // Basic protection: prevent deleting the main setup admin account
        if ($id == 1) {
            echo json_encode(['success' => false, 'error' => 'Cannot delete primary admin account.']);
            exit;
        }
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>