import { api } from '../services/api.js';

let currentUser = null;
let currentRole = 'kasir';

document.addEventListener('DOMContentLoaded', async () => {
    const token = api.getToken();
    currentUser = api.getUser();

    if (!token || !currentUser) {
        window.location.href = './index.html';
        return;
    }

    currentRole = currentUser.role ? (currentUser.role.name || currentUser.role) : 'kasir';

    const navName = document.getElementById('navUserName');
    if (navName) navName.textContent = currentUser.name;
    const navRole = document.getElementById('navUserRole');
    if (navRole) navRole.textContent = currentUser.role_label || currentRole;
    const greeting = document.getElementById('greetingText');
    if (greeting) greeting.textContent = 'Selamat Datang, ' + currentUser.name + '!';

    await fetchDashboardData();

    document.getElementById('btnLogout')?.addEventListener('click', handleLogout);
});

async function fetchDashboardData() {
    try {
        const res = await api.analytics.dashboard();
        if (res.success) {
            const shiftBadge = document.getElementById('navShiftBadge');
            if (res.data && res.data.current_shift) {
                const shiftName = document.getElementById('navShiftName');
                if (shiftName) shiftName.textContent = res.data.current_shift.name;
            } else if (shiftBadge) {
                shiftBadge.style.display = 'none';
            }

            renderKPIs(res.data, res.role || currentRole);
            renderActionMenus(res.role || currentRole);
        }
    } catch (err) {
        console.error('Gagal memuat data dashboard:', err);
        renderActionMenus(currentRole);
    }
}

function renderKPIs(data, role) {
    const grid = document.getElementById('kpiGrid');
    if (!grid) return;
    grid.innerHTML = '';

    if (role === 'kasir') {
        grid.appendChild(createKPiCard(
            'RP',
            'Omzet Shift Ini',
            'Rp ' + (data.shift_omzet || 0).toLocaleString('id-ID'),
            (data.shift_transactions || 0) + ' transaksi selesai',
            'success'
        ));

        grid.appendChild(createKPiCard(
            'TRX',
            'Penjualan Shift',
            (data.shift_transactions || 0) + ' Transaksi',
            'Penjualan shift aktif'
        ));

        const lowStock = data.low_stock_count || 0;
        grid.appendChild(createKPiCard(
            'STK',
            'Stok Menipis',
            lowStock + ' Item',
            lowStock > 0 ? 'Segera infokan ke gudang!' : 'Stok aman',
            lowStock > 0 ? 'warning' : 'success',
            lowStock > 0
        ));
    } else if (role === 'admin') {
        grid.appendChild(createKPiCard(
            'RP',
            'Total Omzet Hari Ini',
            'Rp ' + (data.today_omzet || 0).toLocaleString('id-ID'),
            (data.today_transactions || 0) + ' transaksi hari ini',
            'success'
        ));

        if (data.shift_breakdown) {
            data.shift_breakdown.forEach(sh => {
                grid.appendChild(createKPiCard(
                    'SH',
                    'Omzet ' + sh.shift_name,
                    'Rp ' + (sh.total_omzet || 0).toLocaleString('id-ID'),
                    sh.total_transactions + ' transaksi (' + sh.time_range + ')'
                ));
            });
        }

        const adminLowStock = data.low_stock_count || 0;
        grid.appendChild(createKPiCard(
            'STK',
            'Stok Menipis',
            adminLowStock + ' Item',
            adminLowStock > 0 ? 'Perlu pengadaan segera' : 'Semua stok aman',
            adminLowStock > 0 ? 'warning' : 'success',
            adminLowStock > 0
        ));
    } else if (role === 'pemilik') {
        grid.appendChild(createKPiCard(
            'RP',
            'Total Omzet Hari Ini',
            'Rp ' + (data.today_omzet || 0).toLocaleString('id-ID'),
            (data.today_transactions || 0) + ' transaksi',
            'success'
        ));

        const estProfit = data.net_profit_estimate || 0;
        grid.appendChild(createKPiCard(
            'LABA',
            'Estimasi Laba Bersih',
            'Rp ' + estProfit.toLocaleString('id-ID'),
            'Setelah belanja & beban harian',
            estProfit >= 0 ? 'success' : 'warning'
        ));

        const totalCost = data.total_cost || 0;
        grid.appendChild(createKPiCard(
            'BIAYA',
            'Total Belanja & Beban',
            'Rp ' + totalCost.toLocaleString('id-ID'),
            'Pembelian: Rp ' + (data.today_purchases || 0).toLocaleString('id-ID') + ' | Beban: Rp ' + (data.today_expenses || 0).toLocaleString('id-ID'),
            'warning'
        ));

        if (data.customer_breakdown && data.customer_breakdown.yarwe) {
            const biro = data.customer_breakdown.yarwe;
            grid.appendChild(createKPiCard(
                'BIRO',
                'Rincian Biro (Yarwe)',
                (biro.total_plates || 0) + ' Piring',
                'Fee: Rp ' + (biro.biro_fee || 0).toLocaleString('id-ID') + ' | Omzet: Rp ' + (biro.omzet || 0).toLocaleString('id-ID'),
                'success'
            ));
        }
    }
}

function createKPiCard(icon, label, value, sub, statusClass = '', isAlert = false) {
    const card = document.createElement('div');
    card.className = 'kpi-card';
    card.innerHTML = `
        <div class="kpi-icon-wrap ${statusClass}">${icon}</div>
        <div class="kpi-info">
            <span class="kpi-label">${label}</span>
            <span class="kpi-value">${value}</span>
            <span class="kpi-sub ${isAlert ? 'alert' : ''}">${sub}</span>
        </div>
    `;
    return card;
}

function renderActionMenus(role) {
    const grid = document.getElementById('actionGrid');
    if (!grid) return;
    grid.innerHTML = '';

    if (role === 'kasir') {
        grid.appendChild(createMenuCard('POS', 'POS Kasir Penjualan', 'Buka layar transaksi, catat piring biro, dan cetak struk thermal.', './kasir.html', 'Buka Kasir'));
        grid.appendChild(createMenuCard('STOK', 'Inventaris & Stok (Lihat)', 'Lihat informasi sisa stok makanan, minuman, dan bahan baku.', './inventaris.html', 'Lihat Stok'));
        grid.appendChild(createMenuCard('HISTORI', 'Riwayat Shift Ini', 'Cek daftar transaksi yang sudah berhasil diproses pada shift ini.', './kasir.html#history', 'Lihat Riwayat'));
    } else if (role === 'admin') {
        grid.appendChild(createMenuCard('STOK', 'Kelola Inventaris & Stok', 'Tambah/edit produk, kategori, sesuaikan stok opname, dan atur piring.', './inventaris.html', 'Buka Inventaris'));
        grid.appendChild(createMenuCard('BELANJA', 'Pengadaan Bahan (Pembelian)', 'Catat pembelian bahan dari supplier untuk memadai stok gudang.', './pembelian.html', 'Catat Pembelian'));
        grid.appendChild(createMenuCard('BEBAN', 'Biaya Operasional', 'Catat pengeluaran harian (gas, listrik, gaji, perawatan).', './operasional.html', 'Catat Beban'));
        grid.appendChild(createMenuCard('HISTORI', 'Riwayat Transaksi Luas', 'Lihat dan cetak ulang struk semua transaksi penjualan dari semua kasir.', './riwayat.html', 'Lihat Semua'));
    } else if (role === 'pemilik') {
        grid.appendChild(createMenuCard('LABA', 'Laporan Laba Rugi & Analitik', 'Analisis omzet, biaya bahan, operasional, dan profit restoran berdasarkan periode.', './analitik.html', 'Buka Laporan'));
        grid.appendChild(createMenuCard('REKAP', 'Monitoring Omzet & Penjualan', 'Pantau rekap transaksi harian, shift, rincian umum, VIP, serta biro piring.', './riwayat.html', 'Pantau Riwayat'));
        grid.appendChild(createMenuCard('STOK', 'Monitoring Stok Gudang', 'Lihat rekap stok barang dan nilai aset bahan makanan secara real-time.', './inventaris.html', 'Cek Stok'));
    }
}

function createMenuCard(icon, title, desc, linkUrl, linkText) {
    const card = document.createElement('div');
    card.className = 'interactive-card';
    card.innerHTML = `
        <div class="action-icon">${icon}</div>
        <h4 class="action-title">${title}</h4>
        <p class="action-desc">${desc}</p>
        <div class="action-footer">
            <span>${linkText}</span>
            <span>&rarr;</span>
        </div>
    `;

    card.addEventListener('click', () => {
        window.location.href = linkUrl;
    });

    return card;
}

async function handleLogout() {
    if (!confirm('Yakin ingin keluar dari sistem?')) return;
    try {
        await api.auth.logout();
    } catch (e) {
        // Ignore
    } finally {
        api.clearSession();
        window.location.href = './index.html';
    }
}
