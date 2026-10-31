import { api } from '../services/api.js';


document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const alertBox = document.getElementById('alertBox');
    const btnSubmit = document.getElementById('btnSubmit');


    const currentUser = api.getUser();
    if (currentUser && api.getToken()) {
        window.location.href = './dashboard.html';
        return;
    }

    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        hideAlert();


        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value;

        if (!username || !password) {
            showAlert('Silakan masukkan username dan password.');
            return;
        }

        try {
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memverifikasi...';


            const response = await api.auth.login(username, password);


            api.setSession(response.data.token, response.data.user);


            showAlert(response.message, 'success');


            setTimeout(() => {
                window.location.href = './dashboard.html';
            }, 500);

        } catch (error) {
            showAlert(error.message || 'Username tau password salah.');
        } finally {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="fa-solid fa-right-to-bracket"></i> Masuk ke Sistem';
        }
    });

    function showAlert(message, type = 'danger') {
        alertBox.textContent = message;
        alertBox.className = `bm-alert bm-alert-${type}`;
        alertBox.style.display = 'block';
    }

    function hideAlert() {
        alertBox.style.display = 'none';
    }
});
