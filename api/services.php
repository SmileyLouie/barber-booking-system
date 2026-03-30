<?php
require_once 'db.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? $_POST['action'] ?? $_GET['action'] ?? 'get';

try {
    if ($action === 'get') {
        $stmt = $pdo->query("SELECT * FROM services");
        echo json_encode($stmt->fetchAll());
    } else if ($action === 'create') {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }
        $name = $input['service_name'] ?? '';
        $price = $input['price'] ?? 0;
        $duration = $input['duration'] ?? '';
        $image = $input['image'] ?? '';
        $desc = $input['description'] ?? '';

        $stmt = $pdo->prepare("INSERT INTO services (service_name, description, price, duration, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $desc, $price, $duration, $image]);
        echo json_encode(['success' => true]);
    } else if ($action === 'delete') {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }
        $id = $input['service_id'] ?? 0;
        $stmt = $pdo->prepare("DELETE FROM services WHERE service_id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>