<?php
session_start();
require_once '../Database/db-config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];
$action = $_GET['action'] ?? 'fetch';

if ($action === 'fetch') {
    // Fetch recent 10 notifications
    $stmt = $conn->prepare("SELECT id, message, status, created_at, complaint_id, category_id FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    $stmt->close();

    // Fetch unread count
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND status = 'unread'");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $count = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();

    echo json_encode(['success' => true, 'notifications' => $notifications, 'unreadCount' => $count]);
} elseif ($action === 'mark_read') {
    $notifId = $_GET['id'] ?? null;
    if ($notifId) {
        $stmt = $conn->prepare("UPDATE notifications SET status = 'read' WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $notifId, $userId);
    } else {
        $stmt = $conn->prepare("UPDATE notifications SET status = 'read' WHERE user_id = ? AND status = 'unread'");
        $stmt->bind_param("i", $userId);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    $stmt->close();
}
