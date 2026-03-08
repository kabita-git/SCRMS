<link rel="stylesheet" href="../Assets/Css/logout.css">
<script src="../Assets/JS/logout.js" defer></script>
<script src="../Assets/JS/alerts.js" defer></script>

<header class="top-header">
    <h1 class="system-title">Student Complain Registration & Management System</h1>
    <button class="logout-btn logout-trigger" id="logoutTrigger">Logout</button>
</header>

<!-- Custom Logout Modal -->
<div id="logoutModal" class="custom-modal-overlay">
    <div class="custom-modal-box">
        <h3 id="logoutMessage">Are you sure you want to log out?</h3>
        <div class="custom-modal-actions" id="logoutActions">
            <button class="modal-btn-cancel" id="logoutCancel">Cancel</button>
            <button class="modal-btn-confirm" id="logoutConfirm">Yes, Logout</button>
        </div>
    </div>
</div>