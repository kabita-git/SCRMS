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

        // If validation passes, send to server
        if (isValid) {
            // Show loading state
            const loginBtn = loginForm.querySelector('.login-btn');
            const originalText = loginBtn.textContent;
            loginBtn.textContent = 'Logging in...';
            loginBtn.disabled = true;

            // Prepare form data
            const formData = new FormData();
            formData.append('action', 'validate');
            formData.append('email', email);
            formData.append('password', password);

            // Send to server for validation
            fetch('index.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Login successful!');
                    // Redirect to appropriate dashboard
                    window.location.href = data.redirect;
                } else {
                    // Show server error message
                    const serverError = document.createElement('div');
                    serverError.style.cssText = 'background-color: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #f5c6cb;';
                    serverError.textContent = data.message;
                    loginForm.parentElement.insertBefore(serverError, loginForm);
                    
                    // Reset button
                    loginBtn.textContent = originalText;
                    loginBtn.disabled = false;

                    // Auto-remove error after 5 seconds
                    setTimeout(() => serverError.remove(), 5000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const serverError = document.createElement('div');
                serverError.style.cssText = 'background-color: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #f5c6cb;';
                serverError.textContent = 'An error occurred. Please try again.';
                loginForm.parentElement.insertBefore(serverError, loginForm);
                
                loginBtn.textContent = originalText;
                loginBtn.disabled = false;

                setTimeout(() => serverError.remove(), 5000);
            });
        }
    });

    // Forgot password handler
    const forgotPasswordLink = document.querySelector('.forgot-password');
    forgotPasswordLink.addEventListener('click', function(e) {
        e.preventDefault();
        alert('Password reset functionality will be implemented here.');
    });

    // Register link handler — navigate to registration page
    const registerLink = document.querySelector('.register-link');
    if (registerLink) {
        registerLink.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = 'registration.php';
        });
    }
});