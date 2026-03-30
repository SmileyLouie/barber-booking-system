<?php
require_once 'db.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? $_POST['action'] ?? $_GET['action'] ?? 'get';

try {
    if ($action === 'get') {
        $stmt = $pdo->query("
            SELECT b.*, GROUP_CONCAT(bs.service_id) as service_ids 
            FROM barbers b 
            LEFT JOIN barber_services bs ON b.barber_id = bs.barber_id 
            GROUP BY b.barber_id
        ");
        $barbers = $stmt->fetchAll();
        // Convert CSV string to arrays for frontend
        foreach ($barbers as &$b) {
            $b['services'] = $b['service_ids'] ? explode(',', $b['service_ids']) : [];
            unset($b['service_ids']);
        }
        echo json_encode($barbers);
    } else if ($action === 'create') {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }

        $name = $input['name'] ?? '';
        $specialty = $input['specialty'] ?? '';
        $image = $input['image'] ?? '';
        $email = $input['email'] ?? '';
        $password = password_hash($input['password'] ?? 'password', PASSWORD_DEFAULT);
        $services = $input['services'] ?? []; // Array of service IDs

        $pdo->beginTransaction();

        // 1. Create Barber Profile
        $stmt = $pdo->prepare("INSERT INTO barbers (name, specialty, image, availability) VALUES (?, ?, ?, 1)");
        $stmt->execute([$name, $specialty, $image]);
        $barber_id = $pdo->lastInsertId();

        // 2. Create User Account for the Barber
        $stmt2 = $pdo->prepare("INSERT INTO users (name, email, password, role, linked_barber_id) VALUES (?, ?, ?, 'barber', ?)");
        $stmt2->execute([$name, $email, $password, $barber_id]);

        // 3. Link Services
        if (!empty($services)) {
            $insertValues = [];
            $insertParams = [];
            foreach ($services as $sid) {
                $insertValues[] = "(?, ?)";
                $insertParams[] = $barber_id;
                $insertParams[] = $sid;
            }
            $sql = "INSERT INTO barber_services (barber_id, service_id) VALUES " . implode(',', $insertValues);
            $stmt3 = $pdo->prepare($sql);
            $stmt3->execute($insertParams);
        }

        $pdo->commit();
        echo json_encode(['success' => true]);
    } else if ($action === 'delete') {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }
        $id = $input['barber_id'] ?? 0;

        $pdo->beginTransaction();
        // Delete user account first due to FK constraints if any, or cascade handle it. 
        // Our constraint on users isn't strict, but we definitely want to delete the user.
        // Delete linked services first
        $stmt0 = $pdo->prepare("DELETE FROM barber_services WHERE barber_id = ?");
        $stmt0->execute([$id]);

        $stmt1 = $pdo->prepare("DELETE FROM users WHERE linked_barber_id = ?");
        $stmt1->execute([$id]);

        $stmt2 = $pdo->prepare("DELETE FROM barbers WHERE barber_id = ?");
        $stmt2->execute([$id]);

        $pdo->commit();
        echo json_encode(['success' => true]);
    } else if ($action === 'toggle_availability') {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'barber') {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }
        $barber_id = $_SESSION['barber_id'];
        $avail = $input['availability'] ? 1 : 0;

        $stmt = $pdo->prepare("UPDATE barbers SET availability = ? WHERE barber_id = ?");
        $stmt->execute([$avail, $barber_id]);
        echo json_encode(['success' => true]);
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>