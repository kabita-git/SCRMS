<?php
session_start();
require_once '../Database/db-config.php';
require_once '../Includes/AutoCategorizer.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$title = isset($_POST['title']) ? trim($_POST['title']) : '';

if (empty($description) && empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Empty input']);
    exit;
}

// Fetch categories
$all_cats = [];
$cat_query = $conn->query("SELECT category_id, category_name, description FROM complaint_categories");
while ($row = $cat_query->fetch_assoc()) {
    $all_cats[] = $row;
}

$categorizer = new AutoCategorizer();
$suggestedId = $categorizer->suggestCategory($description . " " . $title, $all_cats);

echo json_encode([
    'success' => true,
    'suggested_id' => $suggestedId
]);
