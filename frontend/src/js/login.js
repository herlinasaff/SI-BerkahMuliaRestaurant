import { api } from '../services/api.js';

document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const alertBox = document.getElementById('alertBox');
    const btnSubmit = document.getElementById('btnSubmit');

    // Jika sudah ada sesi aktif, langsung arahkan
    const currentUser = api.getUser();
    if (currentUser && api.getToken()) {
        redirectByRole(currentUser.role);
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

            // Simpan Token & User
            api.setSession(response.data.token, response.data.user);

            // Tampilkan notifikasi sukses singkat
            showAlert(response.message, 'success');

            // Arahkan ke halaman sesuai peran
            setTimeout(() => {
                redirectByRole(response.data.user.role);
            }, 700);

        } catch (error) {
            showAlert(error.message || 'Username atau password salah.');
        } finally {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="fa-solid fa-right-to-bracket"></i> Masuk ke Sistem';
        }
    });

    function redirectByRole(role) {
        if (role === 'kasir') {
            window.location.href = '/kasir.html';
        } else {
            // Admin & Pemilik diarahkan ke POS atau Dashboard
            window.location.href = '/kasir.html';
        }
    }

    function showAlert(message, type = 'danger') {
        alertBox.textContent = message;
        alertBox.className = `bm-alert bm-alert-${type}`;
        alertBox.style.display = 'block';
    }

    function hideAlert() {
        alertBox.style.display = 'none';
    }
});
