document.addEventListener('DOMContentLoaded', function () {
    // Find all alert elements (both static PHP ones and potential dynamic ones)
    function setupAlertDismissal() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            // Check if it already has a timer to avoid duplicate timeouts
            if (alert.dataset.timerSet) return;

            alert.dataset.timerSet = "true";

            // Set 3 second timeout for dismissal
            setTimeout(() => {
                alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease, margin 0.5s ease, padding 0.5s ease, height 0.5s ease';
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';

                // Completely remove from DOM after fade out
                setTimeout(() => {
                    alert.style.height = '0';
                    alert.style.padding = '0';
                    alert.style.margin = '0';
                    setTimeout(() => {
                        alert.remove();
                    }, 300);
                }, 300);
            }, 3000);
        });
    }

    // Initial run
    setupAlertDismissal();

    // Occasional check for dynamic alerts (if any are added via JS without page reload)
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.addedNodes.length) {
                setupAlertDismissal();
            }
        });
    });

    observer.observe(document.body, { childList: true, subtree: true });
});
