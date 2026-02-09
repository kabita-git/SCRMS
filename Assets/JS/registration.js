// Registration Form Handler
document.addEventListener('DOMContentLoaded', function() {
    const registrationForm = document.getElementById('registrationForm');
    const fullNameInput = document.getElementById('fullName');
    const studentIdInput = document.getElementById('studentId');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    
    const fullNameError = document.getElementById('fullNameError');
    const studentIdError = document.getElementById('studentIdError');
    const emailError = document.getElementById('emailError');
    const passwordError = document.getElementById('passwordError');
    const confirmPasswordError = document.getElementById('confirmPasswordError');

    // Email validation function
    function validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Clear error message on input
    fullNameInput.addEventListener('input', function() {
        fullNameError.textContent = '';
        fullNameInput.style.borderColor = '#d1d5db';
    });

    studentIdInput.addEventListener('input', function() {
        studentIdError.textContent = '';
        studentIdInput.style.borderColor = '#d1d5db';
    });

    emailInput.addEventListener('input', function() {
        emailError.textContent = '';
        emailInput.style.borderColor = '#d1d5db';
    });

    passwordInput.addEventListener('input', function() {
        passwordError.textContent = '';
        passwordInput.style.borderColor = '#d1d5db';
    });

    confirmPasswordInput.addEventListener('input', function() {
        confirmPasswordError.textContent = '';
        confirmPasswordInput.style.borderColor = '#d1d5db';
    });

    // Form submission handler
    registrationForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Clear previous errors
        fullNameError.textContent = '';
        studentIdError.textContent = '';
        emailError.textContent = '';
        passwordError.textContent = '';
        confirmPasswordError.textContent = '';
        
        fullNameInput.style.borderColor = '#d1d5db';
        studentIdInput.style.borderColor = '#d1d5db';
        emailInput.style.borderColor = '#d1d5db';
        passwordInput.style.borderColor = '#d1d5db';
        confirmPasswordInput.style.borderColor = '#d1d5db';

        let isValid = true;

        // Validate full name
        const fullName = fullNameInput.value.trim();
        if (fullName === '') {
            fullNameError.textContent = 'Full name is required';
            fullNameInput.style.borderColor = '#e74c3c';
            isValid = false;
        } else if (fullName.split(' ').length < 2) {
            fullNameError.textContent = 'Please enter first and last name';
            fullNameInput.style.borderColor = '#e74c3c';
            isValid = false;
        }

        // Validate student ID
        const studentId = studentIdInput.value.trim();
        if (studentId === '') {
            studentIdError.textContent = 'Student ID is required';
            studentIdInput.style.borderColor = '#e74c3c';
            isValid = false;
        }

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

        // Validate confirm password
        const confirmPassword = confirmPasswordInput.value.trim();
        if (confirmPassword === '') {
            confirmPasswordError.textContent = 'Please confirm your password';
            confirmPasswordInput.style.borderColor = '#e74c3c';
            isValid = false;
        } else if (password !== confirmPassword) {
            confirmPasswordError.textContent = 'Passwords do not match';
            confirmPasswordInput.style.borderColor = '#e74c3c';
            isValid = false;
        }

        // If validation passes, send to server
        if (isValid) {
            const registerBtn = registrationForm.querySelector('.register-btn');
            const originalText = registerBtn.textContent;
            registerBtn.textContent = 'Registering...';
            registerBtn.disabled = true;

            // Prepare form data
            const formData = new FormData();
            formData.append('action', 'register');
            formData.append('fullName', fullName);
            formData.append('studentId', studentId);
            formData.append('email', email);
            formData.append('password', password);
            formData.append('confirmPassword', confirmPassword);

            // Send to server
            fetch('registration.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    const successMessage = document.createElement('div');
                    successMessage.style.cssText = 'background-color: #d4edda; color: #155724; padding: 12px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #c3e6cb;';
                    successMessage.textContent = data.message;
                    registrationForm.parentElement.insertBefore(successMessage, registrationForm);
                    
                    // Reset form
                    registrationForm.reset();
                    
                    // Redirect to login after 2 seconds
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 2000);
                } else {
                    // Show server error message
                    const serverError = document.createElement('div');
                    serverError.style.cssText = 'background-color: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #f5c6cb;';
                    serverError.textContent = data.message;
                    registrationForm.parentElement.insertBefore(serverError, registrationForm);
                    
                    // Reset button
                    registerBtn.textContent = originalText;
                    registerBtn.disabled = false;

                    // Auto-remove error after 5 seconds
                    setTimeout(() => serverError.remove(), 5000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const serverError = document.createElement('div');
                serverError.style.cssText = 'background-color: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #f5c6cb;';
                serverError.textContent = 'An error occurred during registration. Please try again.';
                registrationForm.parentElement.insertBefore(serverError, registrationForm);
                
                registerBtn.textContent = originalText;
                registerBtn.disabled = false;

                setTimeout(() => serverError.remove(), 5000);
            });
        }
    });
});
