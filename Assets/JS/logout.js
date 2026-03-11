document.addEventListener('DOMContentLoaded', function() {
    const logoutTriggers = document.querySelectorAll('.logout-trigger');
    const logoutModal = document.getElementById('logoutModal');
    const logoutCancel = document.getElementById('logoutCancel');
    const logoutConfirm = document.getElementById('logoutConfirm');
    const logoutMessage = document.getElementById('logoutMessage');
    const logoutActions = document.getElementById('logoutActions');

    if (logoutTriggers.length > 0) {
        logoutTriggers.forEach(trigger => {
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                if (logoutMessage) logoutMessage.textContent = 'Are you sure you want to log out?';
                if (logoutActions) logoutActions.style.display = 'flex';
                if (logoutModal) logoutModal.classList.add('active');
            });
        });
    }

    if (logoutCancel && logoutModal) {
        logoutCancel.addEventListener('click', function() {
            logoutModal.classList.remove('active');
        });
    }

    if (logoutConfirm) {
        logoutConfirm.addEventListener('click', function() {
            if (logoutMessage) logoutMessage.textContent = 'Logging out... Please wait.';
            if (logoutActions) logoutActions.style.display = 'none';

            setTimeout(() => {
                window.location.href = '../logout.php';
            }, 2000);
        });
    }

    // Sidebar Toggle Logic for Mobile
    const hamburgerMenu = document.getElementById('hamburgerMenu');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    if (hamburgerMenu && sidebar && sidebarOverlay) {
        const toggleSidebar = () => {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
            document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
        };

        const closeSidebar = () => {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            document.body.style.overflow = '';
        };

        hamburgerMenu.addEventListener('click', toggleSidebar);
        sidebarOverlay.addEventListener('click', closeSidebar);

        // Close sidebar when navigating on mobile (optional but recommended)
        const navLinks = sidebar.querySelectorAll('.nav-item, .nav-subitem');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    closeSidebar();
                }
            });
        });
    }
});
