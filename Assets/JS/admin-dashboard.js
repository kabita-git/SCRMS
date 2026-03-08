// Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    
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



    // More Info Button Handlers
    const moreInfoButtons = document.querySelectorAll('.stat-more-btn');
    moreInfoButtons.forEach((btn, index) => {
        btn.addEventListener('click', function() {
            const cardTypes = ['complaints', 'users', 'solved'];
            const pageUrls = {
                'complaints': 'complaint-management.php',
                'users': 'user-management.php',
                'solved': 'complaint-management.php'
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