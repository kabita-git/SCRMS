document.getElementById('registrationForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    if (password !== confirmPassword) {
        alert('Passwords do not match!');
        return;
    }
    
    // Here you would typically send the data to your server
    const formData = {
        fullName: document.getElementById('fullName').value,
        studentId: document.getElementById('studentId').value,
        email: document.getElementById('email').value,
        password: password
    };
    
    console.log('Registration Data:', formData);
    
    alert('Registration successful!');
    window.location.href = 'user-dashboard.html';
});