/**
 * login.js
 * Handles login interactions
 */

document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');
    const btnText = document.getElementById('btnText');
    const btnLoader = document.getElementById('btnLoader');

    loginForm.addEventListener('submit', (e) => {
        e.preventDefault();

        // UI Loading State
        toggleLoading(true);

        const formData = new FormData(loginForm);
        const username = formData.get('username');



        fetch('api/login.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                toggleLoading(false);

                if (data.success) {
                    showToast('Login Successful!', 'success');
                    setTimeout(() => window.location.href = data.redirect, 1000);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                toggleLoading(false);
                showToast('System Error: ' + error.message, 'error');
            });
    });

    function toggleLoading(isLoading) {
        if (isLoading) {
            loginBtn.disabled = true;
            btnText.textContent = 'Authenticating';
            btnLoader.style.display = 'inline';
        } else {
            loginBtn.disabled = false;
            btnText.textContent = 'Login Access';
            btnLoader.style.display = 'none';
        }
    }

    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        toast.textContent = message;
        toast.className = `toast ${type} show`;

        setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
    }
});
