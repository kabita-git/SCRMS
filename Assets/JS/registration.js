// Registration Form Handler
document.addEventListener('DOMContentLoaded', function () {
    const registrationForm = document.getElementById('registrationForm');
    const firstNameInput = document.getElementById('firstName');
    const middleNameInput = document.getElementById('middleName');
    const lastNameInput = document.getElementById('lastName');
    const emailInput = document.getElementById('email');
    const contactInput = document.getElementById('contact');
    const batchInput = document.getElementById('batch');
    const programInput = document.getElementById('program');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');

    const firstNameError = document.getElementById('firstNameError');
    const lastNameError = document.getElementById('lastNameError');
    const emailError = document.getElementById('emailError');
    const contactError = document.getElementById('contactError');
    const batchError = document.getElementById('batchError');
    const programError = document.getElementById('programError');
    const passwordError = document.getElementById('passwordError');
    const confirmPasswordError = document.getElementById('confirmPasswordError');

    const roleRadios = document.querySelectorAll('input[name="userRole"]');
    const studentFields = document.getElementById('studentFields');

    if (!registrationForm) return;

    // Role toggle handler
    roleRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'Faculty') {
                studentFields.style.display = 'none';
            } else {
                studentFields.style.display = 'flex'; // Uses flex for the new split layout
            }
        });
    });

    // Email validation function
    function validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Set common visual styles on input
    const inputs = registrationForm.querySelectorAll('input, select');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            this.style.borderColor = '#e2e8f0';
            const errId = this.id + 'Error';
            const errEl = document.getElementById(errId);
            if (errEl) errEl.textContent = '';
        });
    });

    // Form submission handler
    registrationForm.addEventListener('submit', function (e) {
        e.preventDefault();

        // Clear previous state
        const errorTexts = registrationForm.querySelectorAll('.error-text');
        errorTexts.forEach(t => t.textContent = '');
        inputs.forEach(i => i.style.borderColor = '#e2e8f0');

        let isValid = true;

        // Validate names
        if (firstNameInput.value.trim() === '') {
            firstNameError.textContent = 'Required';
            firstNameInput.style.borderColor = '#e74c3c';
            isValid = false;
        }

        if (lastNameInput.value.trim() === '') {
            lastNameError.textContent = 'Required';
            lastNameInput.style.borderColor = '#e74c3c';
            isValid = false;
        }

        // Validate batch and program only if student
        const userRole = document.querySelector('input[name="userRole"]:checked').value;
        if (userRole === 'Student') {
            if (batchInput.value === '') {
                batchError.textContent = 'Required';
                batchInput.style.borderColor = '#e74c3c';
                isValid = false;
            }
            if (programInput.value === '') {
                programError.textContent = 'Required';
                programInput.style.borderColor = '#e74c3c';
                isValid = false;
            }
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

        // Validate contact
        const contact = contactInput.value.trim();
        if (contact === '') {
            contactError.textContent = 'Contact number is required';
            contactInput.style.borderColor = '#e74c3c';
            isValid = false;
        } else if (contact.length < 10) {
            contactError.textContent = 'Please enter a valid contact number';
            contactInput.style.borderColor = '#e74c3c';
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
            // Updated to .submit-btn
            const registerBtn = registrationForm.querySelector('.submit-btn');
            const originalText = registerBtn ? registerBtn.textContent : 'Create Account';
            if (registerBtn) {
                registerBtn.textContent = 'Registering...';
                registerBtn.disabled = true;
            }

            // Prepare form data
            const formData = new FormData();
            formData.append('action', 'register');
            formData.append('firstName', firstNameInput.value.trim());
            formData.append('middleName', middleNameInput.value.trim());
            formData.append('lastName', lastNameInput.value.trim());
            formData.append('email', email);
            formData.append('contact', contact);
            formData.append('userRole', userRole);
            formData.append('batch', userRole === 'Student' ? batchInput.value : '');
            formData.append('program', userRole === 'Student' ? programInput.value : '');
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
                    const successMessage = document.createElement('div');
                    successMessage.style.cssText = 'background-color: #dcfce7; color: #166534; padding: 12px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #bbf7d0; font-size: 14px; text-align: center;';
                    successMessage.textContent = data.message;
                    registrationForm.prepend(successMessage);

                    registrationForm.reset();

                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 2000);
                } else {
                    const serverError = document.createElement('div');
                    serverError.style.cssText = 'background-color: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #fecaca; font-size: 14px; text-align: center;';
                    serverError.textContent = data.message;
                    registrationForm.prepend(serverError);

                    if (registerBtn) {
                        registerBtn.textContent = originalText;
                        registerBtn.disabled = false;
                    }

                    setTimeout(() => serverError.remove(), 5000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const serverError = document.createElement('div');
                serverError.style.cssText = 'background-color: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #fecaca; font-size: 14px; text-align: center;';
                serverError.textContent = 'An error occurred during registration. Please try again.';
                registrationForm.prepend(serverError);

                if (registerBtn) {
                    registerBtn.textContent = originalText;
                    registerBtn.disabled = false;
                }

                setTimeout(() => serverError.remove(), 5000);
            });
        }
    });
});
