// Login Form Handler
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const emailError = document.getElementById('emailError');
    const passwordError = document.getElementById('passwordError');

    // Email validation function
    function validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Clear error message on input
    emailInput.addEventListener('input', function() {
        emailError.textContent = '';
        emailInput.style.borderColor = '#d1d5db';
    });

    passwordInput.addEventListener('input', function() {
        passwordError.textContent = '';
        passwordInput.style.borderColor = '#d1d5db';
    });

    // Form submission handler
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Clear previous errors
        emailError.textContent = '';
        passwordError.textContent = '';
        emailInput.style.borderColor = '#d1d5db';
        passwordInput.style.borderColor = '#d1d5db';

        let isValid = true;

        // Validate email
        const email = emailInput.value.trim();
        if (email === '') {
            emailError.textContent = 'Email address is required';
            emailInput.style.borderColor = '#e74c3c';
            isValid = false;
        } else if (!validateEmail(email)) {
            emailError.textContent = 'Please enter a valid email address';
            emailInput.style.borderColor = '#e74c3c';
            isValid = false;
        }

        // Validate password
        const password = passwordInput.value.trim();
        if (password === '') {
            passwordError.textContent = 'Password is required';
            passwordInput.style.borderColor = '#e74c3c';
            isValid = false;
        } else if (password.length < 6) {
            passwordError.textContent = 'Password must be at least 6 characters';
            passwordInput.style.borderColor = '#e74c3c';
            isValid = false;
        }

        // If validation passes, simulate login
        if (isValid) {
            // Show loading state
            const loginBtn = loginForm.querySelector('.login-btn');
            const originalText = loginBtn.textContent;
            loginBtn.textContent = 'Logging in...';
            loginBtn.disabled = true;

            // Simulate API call delay
            setTimeout(function() {
                // In a real application, you would make an API call here
                // For now, we'll just redirect to dashboard
                console.log('Login successful!');
                console.log('Email:', email);
                
                // Redirect to dashboard
                window.location.href = 'dashboard.html';
            }, 1000);
        }
    });

    // Forgot password handler
    const forgotPasswordLink = document.querySelector('.forgot-password');
    forgotPasswordLink.addEventListener('click', function(e) {
        e.preventDefault();
        alert('Password reset functionality will be implemented here.');
    });

    // Register link handler
    const registerLink = document.querySelector('.register-link');
    registerLink.addEventListener('click', function(e) {
        e.preventDefault();
        alert('Registration page will be implemented here.');
    });
});