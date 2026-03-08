<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit;
}

require_once '../Database/db-config.php';

$userId      = $_SESSION['user_id'];
$message     = '';
$messageType = '';

// ── Cooldown config (seconds) ──────────────────────────────────────────────
define('PROFILE_COOLDOWN', 300);   // 5 minutes
define('PASSWORD_COOLDOWN', 600);  // 10 minutes

// ── Cooldown remaining (seconds) ───────────────────────────────────────────
$profileCooldown  = max(0, (int)(($_SESSION['last_profile_update']  ?? 0) + PROFILE_COOLDOWN  - time()));
$passwordCooldown = max(0, (int)(($_SESSION['last_password_change'] ?? 0) + PASSWORD_COOLDOWN - time()));

// ── Fetch user data ────────────────────────────────────────────────────────
$user = [];
$stmt = $conn->prepare("SELECT first_name, middle_name, last_name, email, contact, role, status, created_at FROM users WHERE user_id = ?");
if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// ── Handle POST ────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'update_profile') {
        if ($profileCooldown > 0) {
            $message     = "Please wait {$profileCooldown}s before updating your profile again.";
            $messageType = "error";
        } else {
            $first   = trim($_POST['first_name']  ?? '');
            $middle  = trim($_POST['middle_name'] ?? '');
            $last    = trim($_POST['last_name']   ?? '');
            $contact = trim($_POST['contact']     ?? '');

            $stmt = $conn->prepare("UPDATE users SET first_name=?, middle_name=?, last_name=?, contact=? WHERE user_id=?");
            if ($stmt) {
                $stmt->bind_param("ssssi", $first, $middle, $last, $contact, $userId);
                if ($stmt->execute()) {
                    $_SESSION['first_name']  = $first;
                    $_SESSION['middle_name'] = $middle;
                    $_SESSION['last_name']   = $last;
                    $_SESSION['last_profile_update'] = time();
                    $profileCooldown = PROFILE_COOLDOWN;
                    // Refresh local user data
                    $user = array_merge($user, [
                        'first_name'  => $first,
                        'middle_name' => $middle,
                        'last_name'   => $last,
                        'contact'     => $contact,
                    ]);
                    $message     = "Profile updated successfully!";
                    $messageType = "success";
                } else {
                    $message     = "Error updating profile.";
                    $messageType = "error";
                }
                $stmt->close();
            }
        }
    }

    elseif ($_POST['action'] === 'change_password') {
        if ($passwordCooldown > 0) {
            $message     = "Please wait {$passwordCooldown}s before changing your password again.";
            $messageType = "error";
        } else {
            $current = $_POST['current_password'] ?? '';
            $new     = $_POST['new_password']     ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            $pStmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
            $pStmt->bind_param("i", $userId);
            $pStmt->execute();
            $pRow = $pStmt->get_result()->fetch_assoc();
            $pStmt->close();

            if (!password_verify($current, $pRow['password'])) {
                $message     = "Current password is incorrect.";
                $messageType = "error";
            } elseif ($new !== $confirm) {
                $message     = "New passwords do not match.";
                $messageType = "error";
            } elseif (strlen($new) < 6) {
                $message     = "Password must be at least 6 characters.";
                $messageType = "error";
            } else {
                $hash  = password_hash($new, PASSWORD_DEFAULT);
                $uStmt = $conn->prepare("UPDATE users SET password=? WHERE user_id=?");
                $uStmt->bind_param("si", $hash, $userId);
                if ($uStmt->execute()) {
                    $_SESSION['last_password_change'] = time();
                    $passwordCooldown = PASSWORD_COOLDOWN;
                    $message     = "Password changed successfully!";
                    $messageType = "success";
                } else {
                    $message     = "Error changing password.";
                    $messageType = "error";
                }
                $uStmt->close();
            }
        }
    }
}

$fullName    = trim(($user['first_name'] ?? '') . ' ' . ($user['middle_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
$avatarUrl   = "https://ui-avatars.com/api/?name=" . urlencode($fullName ?: 'User') . "&background=2D1B69&color=fff&size=100";

// Which tab was active on submit?
$activeTab = (isset($_POST['action']) && $_POST['action'] === 'change_password') ? 'password' : 'info';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - SCRMS</title>
    <link rel="stylesheet" href="../Assets/Css/admin-dashboard.css">
    <link rel="stylesheet" href="../Assets/Css/user-management.css">
    <style>
        .profile-header-minimal {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .profile-header-minimal img {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            border: 2px solid #2D1B69;
        }
        .profile-header-info h2 {
            margin: 0;
            font-size: 22px;
            color: #1a1a1a;
        }
        .profile-header-info p {
            margin: 5px 0 0;
            font-size: 14px;
            color: #666;
            font-weight: 500;
        }
        .profile-content-wrap {
            max-width: 900px;
        }
        .tab-btn {
            padding: 10px 20px;
            border: none;
            background: none;
            color: #666;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
        }
        .tab-btn.active {
            color: #2D1B69;
            border-bottom-color: #2D1B69;
        }
        .tab-panel { display: none; margin-top: 20px; }
        .tab-panel.active { display: block; }

        .cooldown-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #fff8e1;
            color: #f57c00;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            border: 1px solid #ffe082;
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/header.php'; ?>

<div class="main-container">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
        <div class="profile-content-wrap">
            <h2 class="page-title">Profile Settings</h2>

            <?php if (!empty($message)): ?>
                <div class="page-alert <?php echo $messageType; ?>" style="margin-bottom: 20px; padding: 15px; border-radius: 8px; font-weight: 500; border: 1px solid <?php echo $messageType === 'success' ? '#c3e6cb' : '#f5c6cb'; ?>; background-color: <?php echo $messageType === 'success' ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $messageType === 'success' ? '#155724' : '#721c24'; ?>;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="profile-header-minimal">
                <img src="<?php echo $avatarUrl; ?>" alt="Profile">
                <div class="profile-header-info">
                    <h2><?php echo htmlspecialchars($fullName ?: 'User'); ?></h2>
                    <p><?php echo htmlspecialchars(ucfirst($user['role'] ?? '')); ?> • <?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                </div>
            </div>

            <div class="profile-tabs" style="border-bottom: 1px solid #eee; margin-bottom: 10px;">
                <button class="tab-btn <?php echo $activeTab === 'info' ? 'active' : ''; ?>" data-tab="info">Personal Details</button>
                <button class="tab-btn <?php echo $activeTab === 'password' ? 'active' : ''; ?>" data-tab="password">Security</button>
            </div>

            <!-- Personal Info Tab -->
            <div class="tab-panel <?php echo $activeTab === 'info' ? 'active' : ''; ?>" id="tab-info">
                <div class="form-section" style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_profile">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                            <div class="form-group">
                                <label>First Name</label>
                                <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" <?php echo $profileCooldown > 0 ? 'readonly' : 'required'; ?>>
                            </div>
                            <div class="form-group">
                                <label>Middle Name</label>
                                <input type="text" name="middle_name" value="<?php echo htmlspecialchars($user['middle_name'] ?? ''); ?>" <?php echo $profileCooldown > 0 ? 'readonly' : ''; ?>>
                            </div>
                            <div class="form-group">
                                <label>Last Name</label>
                                <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" <?php echo $profileCooldown > 0 ? 'readonly' : 'required'; ?>>
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px;">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" readonly style="background: #f9f9f9;">
                            </div>
                            <div class="form-group">
                                <label>Contact Number</label>
                                <input type="text" name="contact" value="<?php echo htmlspecialchars($user['contact'] ?? ''); ?>" <?php echo $profileCooldown > 0 ? 'readonly' : ''; ?>>
                            </div>
                        </div>
                        
                        <div style="margin-top: 30px; display: flex; align-items: center; gap: 15px; justify-content: flex-end;">
                            <?php if ($profileCooldown > 0): ?>
                                <div class="cooldown-badge">
                                    Refresh in <span class="cooldown-timer" data-seconds="<?php echo $profileCooldown; ?>"></span>
                                </div>
                                <button type="submit" class="btn-primary" disabled style="opacity: 0.6; cursor: not-allowed;">Save Changes</button>
                            <?php else: ?>
                                <button type="submit" class="btn-primary">Save Changes</button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Password Tab -->
            <div class="tab-panel <?php echo $activeTab === 'password' ? 'active' : ''; ?>" id="tab-password">
                <div class="form-section" style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                    <form method="POST">
                        <input type="hidden" name="action" value="change_password">
                        <div class="form-group">
                            <label>Current Password</label>
                            <input type="password" name="current_password" <?php echo $passwordCooldown > 0 ? 'readonly' : 'required'; ?> placeholder="Enter current password">
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
                            <div class="form-group">
                                <label>New Password</label>
                                <input type="password" name="new_password" <?php echo $passwordCooldown > 0 ? 'readonly' : 'required'; ?> placeholder="Min. 6 characters">
                            </div>
                            <div class="form-group">
                                <label>Confirm New Password</label>
                                <input type="password" name="confirm_password" <?php echo $passwordCooldown > 0 ? 'readonly' : 'required'; ?> placeholder="Repeat new password">
                            </div>
                        </div>
                        
                        <div style="margin-top: 30px; display: flex; align-items: center; gap: 15px; justify-content: flex-end;">
                            <?php if ($passwordCooldown > 0): ?>
                                <div class="cooldown-badge">
                                    Refresh in <span class="cooldown-timer" data-seconds="<?php echo $passwordCooldown; ?>"></span>
                                </div>
                                <button type="submit" class="btn-primary" disabled style="opacity: 0.6; cursor: not-allowed;">Update Password</button>
                            <?php else: ?>
                                <button type="submit" class="btn-primary">Update Password</button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="../Assets/JS/admin-dashboard.js"></script>
<script>
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        this.classList.add('active');
        document.getElementById('tab-' + this.dataset.tab).classList.add('active');
    });
});

function formatTime(sec) {
    const m = Math.floor(sec / 60);
    const s = sec % 60;
    return m > 0 ? `${m}m ${s}s` : `${s}s`;
}

document.querySelectorAll('.cooldown-timer').forEach(el => {
    let remaining = parseInt(el.dataset.seconds, 10);
    el.textContent = formatTime(remaining);
    const interval = setInterval(() => {
        remaining--;
        if (remaining <= 0) {
            clearInterval(interval);
            location.reload();
        } else {
            el.textContent = formatTime(remaining);
        }
    }, 1000);
});
</script>
</body>
</html>
