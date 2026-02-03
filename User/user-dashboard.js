// Menu toggle functionality
const menuToggle = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');

menuToggle.addEventListener('click', function() {
    sidebar.classList.toggle('active');
});

// Submit complaint button
document.getElementById('submitComplaintBtn').addEventListener('click', function() {
    window.location.href = 'user-submit-complaint.html';
});

// Logout functionality
document.querySelector('.logout-btn').addEventListener('click', function() {
    if (confirm('Are you sure you want to logout?')) {
        // Here you would typically clear session/token
        window.location.href = 'login.html';
    }
});