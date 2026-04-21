// ============================================
// LearnovaX – Auth JS
// assets/js/auth.js
// ============================================

// ── Password Toggle ──────────────────────────
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    field.type = field.type === 'password' ? 'text' : 'password';
}

// ── Client-side Validation ───────────────────
document.addEventListener('DOMContentLoaded', function () {

    // Register Form
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function (e) {
            let valid = true;

            const name     = document.getElementById('name');
            const email    = document.getElementById('email');
            const password = document.getElementById('password');
            const confirm  = document.getElementById('confirm_password');
            const role     = document.getElementById('role');

            clearErrors();

            // Name
            if (!name.value.trim()) {
                showError('nameErr', 'Full name is required.');
                name.classList.add('input-error');
                valid = false;
            } else if (!/^[a-zA-Z\s]+$/.test(name.value.trim())) {
                showError('nameErr', 'Name must contain letters only.');
                name.classList.add('input-error');
                valid = false;
            }

            // Email
            if (!email.value.trim()) {
                showError('emailErr', 'Email is required.');
                email.classList.add('input-error');
                valid = false;
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
                showError('emailErr', 'Invalid email format.');
                email.classList.add('input-error');
                valid = false;
            }

            // Password
            if (!password.value) {
                showError('passwordErr', 'Password is required.');
                password.classList.add('input-error');
                valid = false;
            } else if (password.value.length < 6) {
                showError('passwordErr', 'Password must be at least 6 characters.');
                password.classList.add('input-error');
                valid = false;
            }

            // Confirm Password
            if (confirm && confirm.value !== password.value) {
                showError('confirmErr', 'Passwords do not match.');
                confirm.classList.add('input-error');
                valid = false;
            }

            // Role
            if (role && !role.value) {
                showError('roleErr', 'Please select a role.');
                role.classList.add('input-error');
                valid = false;
            }

            if (!valid) e.preventDefault();
        });
    }

    // Login Form
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            let valid = true;

            const email    = document.getElementById('email');
            const password = document.getElementById('password');

            clearErrors();

            if (!email.value.trim()) {
                showError('emailErr', 'Email is required.');
                email.classList.add('input-error');
                valid = false;
            }

            if (!password.value) {
                showError('passwordErr', 'Password is required.');
                password.classList.add('input-error');
                valid = false;
            }

            if (!valid) e.preventDefault();
        });
    }

    // Clear errors on input focus
    document.querySelectorAll('input, select').forEach(function (el) {
        el.addEventListener('focus', function () {
            this.classList.remove('input-error');
            const errId = this.id + 'Err';
            const errEl = document.getElementById(errId);
            if (errEl) errEl.textContent = '';
        });
    });
});

function showError(id, message) {
    const el = document.getElementById(id);
    if (el) el.textContent = message;
}

function clearErrors() {
    document.querySelectorAll('.field-error').forEach(el => el.textContent = '');
    document.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));
}
