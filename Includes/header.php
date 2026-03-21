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
    
    <div class="header-right">
        <div class="notification-wrapper">
            <button class="notification-btn" id="notificationBtn" aria-label="Notifications">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>
                </svg>
                <span class="notification-badge" id="notificationCount">0</span>
            </button>
            <div class="notification-dropdown" id="notificationDropdown">
                <div class="dropdown-header">
                    <h3>Notifications</h3>
                    <button class="mark-all-read" id="markAllRead">Mark all as read</button>
                </div>
                <ul class="notification-list" id="notificationList">
                    <li class="no-notifications">No new notifications</li>
                </ul>
            </div>
        </div>

        <button class="logout-btn logout-trigger" id="logoutTrigger">Logout</button>
    </div>
</header>
<link rel="stylesheet" href="../Assets/Css/notifications.css">
<script src="../Assets/JS/notifications.js" defer></script>

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