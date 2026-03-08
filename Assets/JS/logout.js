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
});
