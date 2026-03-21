// Login Form Handler
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const emailError = document.getElementById('emailError');
    const passwordError = document.getElementById('passwordError');

    if (!loginForm) return;

    // Email validation function
    function validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Clear error message on input
    if (emailInput) {
        emailInput.addEventListener('input', function() {
            emailError.textContent = '';
            emailInput.style.borderColor = '#d1d5db';
        });
    }

    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            passwordError.textContent = '';
            passwordInput.style.borderColor = '#d1d5db';
        });
    }

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
            // Updated to .submit-btn to match new UI
            const loginBtn = loginForm.querySelector('.submit-btn');
            const originalText = loginBtn ? loginBtn.textContent : 'Sign In';
            if (loginBtn) {
                loginBtn.textContent = 'Logging in...';
                loginBtn.disabled = true;
            }

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
                    serverError.style.cssText = 'background-color: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #fecaca; font-size: 14px; text-align: center;';
                    serverError.textContent = data.message;
                    loginForm.prepend(serverError);
                    
                    // Reset button
                    if (loginBtn) {
                        loginBtn.textContent = originalText;
                        loginBtn.disabled = false;
                    }

                    // Auto-remove error after 5 seconds
                    setTimeout(() => serverError.remove(), 5000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const serverError = document.createElement('div');
                serverError.style.cssText = 'background-color: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #fecaca; font-size: 14px; text-align: center;';
                serverError.textContent = 'An error occurred. Please try again.';
                loginForm.prepend(serverError);
                
                if (loginBtn) {
                    loginBtn.textContent = originalText;
                    loginBtn.disabled = false;
                }

                setTimeout(() => serverError.remove(), 5000);
            });
        }
    });
});

// Google Sign-In Callback
function handleCredentialResponse(response) {
    console.log("Encoded JWT ID token: " + response.credential);
    
    // Send ID token to backend
    const formData = new FormData();
    formData.append('id_token', response.credential);

    fetch('Includes/google-login.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            alert("Google Sign-In failed: " + data.message);
        }
    })
    .catch(err => {
        console.error("Error during Google Sign-In:", err);
        alert("An error occurred during Google Sign-In. Please try again.");
    });
}