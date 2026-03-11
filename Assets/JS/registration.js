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
    const middleNameError = document.getElementById('middleNameError');
    const lastNameError = document.getElementById('lastNameError');
    const emailError = document.getElementById('emailError');
    const contactError = document.getElementById('contactError');
    const batchError = document.getElementById('batchError');
    const programError = document.getElementById('programError');
    const passwordError = document.getElementById('passwordError');
    const confirmPasswordError = document.getElementById('confirmPasswordError');

    const roleRadios = document.querySelectorAll('input[name="userRole"]');
    const studentFields = document.getElementById('studentFields');

    // Role toggle handler
    roleRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'Faculty') {
                studentFields.style.display = 'none';
            } else {
                studentFields.style.display = 'grid';
            }
        });
    });

    // Email validation function
    function validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Clear error message on input
    firstNameInput.addEventListener('input', function () {
        firstNameError.textContent = '';
        firstNameInput.style.borderColor = '#d1d5db';
    });

    lastNameInput.addEventListener('input', function () {
        lastNameError.textContent = '';
        lastNameInput.style.borderColor = '#d1d5db';
    });

    emailInput.addEventListener('input', function () {
        emailError.textContent = '';
        emailInput.style.borderColor = '#d1d5db';
    });

    contactInput.addEventListener('input', function () {
        contactError.textContent = '';
        contactInput.style.borderColor = '#d1d5db';
    });

    passwordInput.addEventListener('input', function () {
        passwordError.textContent = '';
        passwordInput.style.borderColor = '#d1d5db';
    });

    confirmPasswordInput.addEventListener('input', function () {
        confirmPasswordError.textContent = '';
        confirmPasswordInput.style.borderColor = '#d1d5db';
    });

    // Form submission handler
    registrationForm.addEventListener('submit', function (e) {
        e.preventDefault();

        // Clear previous errors
        firstNameError.textContent = '';
        lastNameError.textContent = '';
        emailError.textContent = '';
        passwordError.textContent = '';
        confirmPasswordError.textContent = '';

        firstNameInput.style.borderColor = '#d1d5db';
        lastNameInput.style.borderColor = '#d1d5db';
        emailInput.style.borderColor = '#d1d5db';
        contactInput.style.borderColor = '#d1d5db';
        passwordInput.style.borderColor = '#d1d5db';
        confirmPasswordInput.style.borderColor = '#d1d5db';

        let isValid = true;

        // Validate names
        const firstName = firstNameInput.value.trim();
        if (firstName === '') {
            firstNameError.textContent = 'Required';
            firstNameInput.style.borderColor = '#e74c3c';
            isValid = false;
        }

        const lastName = lastNameInput.value.trim();
        if (lastName === '') {
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
            const registerBtn = registrationForm.querySelector('.register-btn');
            const originalText = registerBtn.textContent;
            registerBtn.textContent = 'Registering...';
            registerBtn.disabled = true;

            // Prepare form data
            const formData = new FormData();
            formData.append('action', 'register');
            formData.append('firstName', firstName);
            formData.append('middleName', middleNameInput.value.trim());
            formData.append('lastName', lastName);
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
