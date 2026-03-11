<link rel="stylesheet" href="../Assets/Css/logout.css">
<script src="../Assets/JS/logout.js" defer></script>
<script src="../Assets/JS/alerts.js" defer></script>

<header class="top-header">
    <div class="header-left">
        <button class="hamburger-menu" id="hamburgerMenu" aria-label="Toggle Sidebar">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </button>
        <h1 class="system-title">SCRMS</h1>
    </div>
    <button class="logout-btn logout-trigger" id="logoutTrigger">Logout</button>
</header>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

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