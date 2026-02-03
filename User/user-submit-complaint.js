// Menu toggle functionality
const menuToggle = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');

menuToggle.addEventListener('click', function() {
    sidebar.classList.toggle('active');
});

// Logout functionality
document.querySelector('.logout-btn').addEventListener('click', function() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = 'login.html';
    }
});

// File upload display
document.getElementById('evidence').addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name || 'Upload Files';
    document.getElementById('fileNameDisplay').textContent = fileName;
});

// Form submission
document.getElementById('complaintForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Gather form data
    const formData = {
        name: document.getElementById('name').value,
        email: document.getElementById('email').value,
        batch: document.getElementById('batch').value,
        category: document.getElementById('category').value,
        description: document.getElementById('description').value,
        date: document.getElementById('date').value,
        evidence: document.getElementById('evidence').files[0]?.name || 'No file'
    };
    
    console.log('Complaint Data:', formData);
    
    // Here you would typically send the data to your server
    alert('Complaint submitted successfully!');
    window.location.href = 'user-complaints.html';
});

// Set today's date as default
document.getElementById('date').valueAsDate = new Date();