<?php
session_start();
// Require logged-in user
if (!isset($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit;
}
$first = htmlspecialchars($_SESSION['first_name'] ?? '');
$middle = htmlspecialchars($_SESSION['middle_name'] ?? '');
$last = htmlspecialchars($_SESSION['last_name'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="../User/user-dashboard.css">
</head>
<body>
    <div style="max-width:800px;margin:40px auto;padding:20px;">
        <h1>User Dashboard (Placeholder)</h1>
        <p>Welcome, <?php echo trim("$first $middle $last"); ?>.</p>
        <p>This is a placeholder user dashboard. Build features here later.</p>
        <p><a href="/logout.php">Logout</a></p>
    </div>
</body>
</html>
