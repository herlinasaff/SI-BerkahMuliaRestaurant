const API_BASE_URL = 'http://127.0.0.1:8000/api';

export const api = {
    // Ambil token dari penyimpanan browser
    getToken() {
        return localStorage.getItem('bm_token');
    },

    // Simpan data sesi
    setSession(token, user) {
        localStorage.setItem('bm_token', token);
        localStorage.setItem('bm_user', JSON.stringify(user));
    },

    // Hapus data sesi (Logout)
    clearSession() {
        localStorage.removeItem('bm_token');
        localStorage.removeItem('bm_user');
    },

    // Ambil data user yang sedang login
    getUser() {
        const user = localStorage.getItem('bm_user');
        return user ? JSON.parse(user) : null;
    },

    // Fungsi dasar pengirim request HTTP
    async request(endpoint, options = {}) {
        const token = this.getToken();
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            ...options.headers,
        };

        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }

        try {
            const response = await fetch(`${API_BASE_URL}${endpoint}`, {
                ...options,
                headers,
            });

            const data = await response.json();

            // Jika token expired / ditolak (401), kembalikan ke login
            if (response.status === 401 && !endpoint.includes('/login')) {
                this.clearSession();
                window.location.href = '/index.html';
                throw new Error('Sesi Anda telah berakhir. Silakan login kembali.');
            }

            if (!response.ok) {
                throw new Error(data.message || 'Terjadi kesalahan pada sistem.');
            }

            return data;
        } catch (error) {
            throw error;
        }
    },

    // Endpoints Autentikasi
    auth: {
        login(username, password) {
            return api.request('/login', {
                method: 'POST',
                body: JSON.stringify({ username, password }),
            });
        },
        me() {
            return api.request('/me');
        },
        logout() {
            return api.request('/logout', { method: 'POST' });
        },
    },

    // Endpoints POS Kasir
    pos: {
        getMenu() {
            return api.request('/pos/menu');
        },
        checkout(payload) {
            return api.request('/pos/checkout', {
                method: 'POST',
                body: JSON.stringify(payload),
            });
        },
        getInvoice(id) {
            return api.request(`/pos/invoice/${id}`);
        },
        getHistory(params = '') {
            return api.request(`/pos/history${params}`);
        },
    },

    // Endpoints Produk & Inventaris
    products: {
        list(params = '') {
            return api.request(`/products${params}`);
        },
        categories() {
            return api.request('/products/categories');
        },
        create(product) {
            return api.request('/products', {
                method: 'POST',
                body: JSON.stringify(product),
            });
        },
        update(id, product) {
            return api.request(`/products/${id}`, {
                method: 'PUT',
                body: JSON.stringify(product),
            });
        },
        adjustStock(id, new_stock, notes) {
            return api.request(`/products/${id}/stock-adjustment`, {
                method: 'POST',
                body: JSON.stringify({ new_stock, notes }),
            });
        },
    },

    // Endpoints Analitik
    analytics: {
        dashboard() {
            return api.request('/analytics/dashboard');
        },
        profitLoss(period = 'monthly') {
            return api.request(`/analytics/profit-loss?period=${period}`);
        },
    },
};
