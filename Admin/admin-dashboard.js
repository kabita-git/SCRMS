// Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    
    // Mobile Menu Toggle
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    
    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        }
    });

    // Complaints Dropdown Toggle
    const complaintsToggle = document.getElementById('complaintsToggle');
    const complaintsDropdown = document.getElementById('complaintsDropdown');
    
    if (complaintsToggle) {
        complaintsToggle.addEventListener('click', function(e) {
            e.preventDefault();
            complaintsDropdown.classList.toggle('active');
            complaintsToggle.classList.toggle('active');
        });
    }

    // Logout Button Handler
    const logoutBtn = document.querySelector('.logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function() {
            const confirmLogout = confirm('Are you sure you want to logout?');
            if (confirmLogout) {
                // Redirect to login page
                window.location.href = 'login.html';
            }
        });
    }

    // More Info Button Handlers
    const moreInfoButtons = document.querySelectorAll('.stat-more-btn');
    moreInfoButtons.forEach((btn, index) => {
        btn.addEventListener('click', function() {
            const cardTypes = ['complaints', 'users', 'solved'];
            const pageUrls = {
                'complaints': 'complaint-management.html',
                'users': 'user-management.html',
                'solved': 'complaint-management.html'
            };
            
            // Navigate to the respective page
            if (pageUrls[cardTypes[index]]) {
                window.location.href = pageUrls[cardTypes[index]];
            }
        });
    });

    // Add animation to stats on page load
    animateStats();
});

// Animate stat numbers on page load
function animateStats() {
    const statNumbers = document.querySelectorAll('.stat-number');
    
    statNumbers.forEach(stat => {
        const finalValue = parseInt(stat.textContent);
        let currentValue = 0;
        const increment = Math.ceil(finalValue / 50);
        const duration = 1000; // 1 second
        const stepTime = duration / 50;
        
        const timer = setInterval(() => {
            currentValue += increment;
            if (currentValue >= finalValue) {
                currentValue = finalValue;
                clearInterval(timer);
            }
            stat.textContent = currentValue;
        }, stepTime);
    });
}