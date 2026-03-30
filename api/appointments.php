<?php
require_once 'db.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

// Handle GET: Fetch appointments
if ($method === 'GET') {
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'barber'])) {
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    try {
        $sql = "
            SELECT a.*, u.name as customer_name, s.service_name, b.name as barber_name 
            FROM appointments a 
            LEFT JOIN users u ON a.user_id = u.user_id
            LEFT JOIN services s ON a.service_id = s.service_id
            LEFT JOIN barbers b ON a.barber_id = b.barber_id
        ";
        $params = [];

        // If barber, only show their appointments
        if ($_SESSION['role'] === 'barber') {
            $sql .= " WHERE a.barber_id = ?";
            $params[] = $_SESSION['barber_id'];
        }

        $sql .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo json_encode($stmt->fetchAll());
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// Handle POST: Create new appointment
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    // Fallback if not logged in (allow guest booking with details)
    $user_id = $_SESSION['user_id'] ?? null;

    $customer_name = $input['name'] ?? 'Guest';
    $customer_email = $input['email'] ?? '';
    $customer_phone = $input['phone'] ?? '';

    // If guest, capture details as guest columns instead of creating dummy accounts
    $guest_name = null;
    $guest_email = null;
    $guest_phone = null;

    if (!$user_id) {
        $guest_name = $customer_name;
        $guest_email = $customer_email;
        $guest_phone = $customer_phone;
    }

    $service_id = $input['service_id'] ?? '';
    $barber_id = $input['barber_id'] ?? '';
    $date = $input['date'] ?? '';
    $time = $input['time'] ?? '';
    $notes = $input['notes'] ?? '';

    // Validate required fields
    if (empty($service_id) || empty($barber_id) || empty($date) || empty($time)) {
        echo json_encode(['success' => false, 'error' => 'Please select a service, barber, date, and time.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO appointments (user_id, service_id, barber_id, appointment_date, appointment_time, notes, guest_name, guest_email, guest_phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $service_id, $barber_id, $date, $time, $notes, $guest_name, $guest_email, $guest_phone]);
        echo json_encode(['success' => true, 'message' => 'Appointment booked successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

// Handle PUT: Update status (Admin or Assigned Barber)
if ($method === 'PUT') {
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'barber'])) {
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $apt_id = $input['appointment_id'] ?? 0;
    $status = $input['status'] ?? 'Pending';

    try {
        // Build query based on role
        if ($_SESSION['role'] === 'barber') {
            // Barber can only update their own
            $stmt = $pdo->prepare("UPDATE appointments SET status = ? WHERE appointment_id = ? AND barber_id = ?");
            $stmt->execute([$status, $apt_id, $_SESSION['barber_id']]);
        } else {
            // Admin can update any
            $stmt = $pdo->prepare("UPDATE appointments SET status = ? WHERE appointment_id = ?");
            $stmt->execute([$status, $apt_id]);
        }
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

// Handle DELETE: Cancel appointment (Admin only)
if ($method === 'DELETE') {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $apt_id = $input['appointment_id'] ?? 0;

    try {
        $stmt = $pdo->prepare("DELETE FROM appointments WHERE appointment_id = ?");
        $stmt->execute([$apt_id]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>