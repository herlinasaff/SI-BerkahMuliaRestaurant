import { api } from '../services/api.js';

let allProducts = [];
let cart = [];
let activeCategory = 'all';
let customerType = 'umum'; // umum, vip, yarwe

document.addEventListener('DOMContentLoaded', async () => {
    // 1. Cek sesi login
    const user = api.getUser();
    if (!user || !api.getToken()) {
        window.location.href = '/index.html';
        return;
    }

    // Tampilkan info kasir
    document.getElementById('cashierName').textContent = user.name;

    // Tombol Logout
    document.getElementById('btnLogout').addEventListener('click', async () => {
        if (confirm('Yakin ingin keluar?')) {
            await api.auth.logout();
            api.clearSession();
            window.location.href = '/index.html';
        }
    });

    // Event Modal Struk
    document.getElementById('btnCloseReceipt').addEventListener('click', closeReceiptModal);
    document.getElementById('btnNewTransaction').addEventListener('click', closeReceiptModal);
    document.getElementById('btnPrintReceipt').addEventListener('click', () => window.print());

    // 2. Ambil data menu dari Backend
    await loadMenu();

    // 3. Event Listener Tipe Pelanggan
    document.querySelectorAll('.cust-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            document.querySelectorAll('.cust-btn').forEach(b => b.classList.remove('active'));
            e.target.classList.add('active');
            customerType = e.target.dataset.type;

            const yarweInputGroup = document.getElementById('yarwePlatesGroup');
            if (customerType === 'yarwe') {
                yarweInputGroup.style.display = 'block';
            } else {
                yarweInputGroup.style.display = 'none';
            }

            renderCart();
        });
    });

    // 4. Hitung Kembalian Otomatis
    document.getElementById('cashReceived').addEventListener('input', updateChange);
    document.getElementById('totalPlatesInput')?.addEventListener('input', (e) => {
        e.target.dataset.manual = 'true';
        renderCart();
    });

    // 5. Tombol Bayar / Checkout
    document.getElementById('btnCheckout').addEventListener('click', handleCheckout);
});

// Ambil Menu dari API
async function loadMenu() {
    try {
        const res = await api.pos.getMenu();
        allProducts = res.products;
        renderCategories(res.categories);
        renderProducts(allProducts);
    } catch (error) {
        alert('Gagal mengambil menu: ' + error.message);
    }
}

// Render Kategori
function renderCategories(categories) {
    const container = document.getElementById('categoryTabs');
    container.innerHTML = `<button class="category-btn active" data-id="all">Semua Menu</button>`;

    categories.forEach(cat => {
        container.innerHTML += `<button class="category-btn" data-id="${cat.id}">${cat.name}</button>`;
    });

    container.querySelectorAll('.category-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            container.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
            e.target.classList.add('active');
            activeCategory = e.target.dataset.id;
            filterProducts();
        });
    });
}

function filterProducts() {
    if (activeCategory === 'all') {
        renderProducts(allProducts);
    } else {
        const filtered = allProducts.filter(p => p.category_id == activeCategory);
        renderProducts(filtered);
    }
}

// Render Kartu Produk
function renderProducts(products) {
    const grid = document.getElementById('productGrid');
    grid.innerHTML = '';

    if (products.length === 0) {
        grid.innerHTML = '<p style="color: #7A6A5E;">Tidak ada menu pada kategori ini.</p>';
        return;
    }

    products.forEach(p => {
        const card = document.createElement('div');
        card.className = 'product-card';
        card.innerHTML = `
      <div>
        <span class="product-code">${p.code}</span>
        <h4 class="product-name">${p.name}</h4>
      </div>
      <div class="product-footer">
        <span class="product-price">Rp ${Number(p.price).toLocaleString('id-ID')}</span>
        <span class="product-stock">Stok: ${p.stock}</span>
      </div>
    `;
        card.addEventListener('click', () => addToCart(p));
        grid.appendChild(card);
    });
}

// Tambah Item ke Keranjang
function addToCart(product) {
    const existing = cart.find(item => item.product_id === product.id);
    if (existing) {
        if (existing.qty < product.stock) {
            existing.qty += 1;
        } else {
            alert(`Stok ${product.name} hanya tersisa ${product.stock}`);
            return;
        }
    } else {
        if (product.stock <= 0) {
            alert(`Maaf, stok ${product.name} sudah habis.`);
            return;
        }
        cart.push({
            product_id: product.id,
            name: product.name,
            price: Number(product.price),
            is_plate_based: product.is_plate_based,
            qty: 1
        });
    }
    renderCart();
}

// Render Keranjang
function renderCart() {
    const cartList = document.getElementById('cartItems');
    cartList.innerHTML = '';

    if (cart.length === 0) {
        cartList.innerHTML = '<p style="text-align: center; color: #7A6A5E; margin-top: 40px;">Keranjang masih kosong</p>';
        updateTotals(0, 0, 0, 0, 0);
        return;
    }

    let originalTotal = 0;
    let autoPlateCount = 0; // Hitung otomatis piring dari menu makanan

    cart.forEach((item, index) => {
        const subtotal = item.price * item.qty;
        originalTotal += subtotal;

        // Jika produk berbasis piring (makanan prasmanan/paket)
        if (item.is_plate_based) {
            autoPlateCount += item.qty;
        }

        const row = document.createElement('div');
        row.className = 'cart-item';
        row.innerHTML = `
      <div class="cart-item-info">
        <h4>${item.name}</h4>
        <span>Rp ${item.price.toLocaleString('id-ID')} x ${item.qty}</span>
      </div>
      <div class="cart-qty-ctrl">
        <button class="btn-qty btn-minus" data-idx="${index}">-</button>
        <span style="font-weight: 600; font-size: 13px;">${item.qty}</span>
        <button class="btn-qty btn-plus" data-idx="${index}">+</button>
      </div>
    `;
        cartList.appendChild(row);
    });

    // Event Qty Plus & Minus
    cartList.querySelectorAll('.btn-minus').forEach(b => {
        b.addEventListener('click', (e) => {
            const idx = e.target.dataset.idx;
            if (cart[idx].qty > 1) {
                cart[idx].qty -= 1;
            } else {
                cart.splice(idx, 1);
            }
            renderCart();
        });
    });

    cartList.querySelectorAll('.btn-plus').forEach(b => {
        b.addEventListener('click', (e) => {
            const idx = e.target.dataset.idx;
            cart[idx].qty += 1;
            renderCart();
        });
    });

    // Sinkronkan input piring biro otomatis sesuai isi keranjang
    const platesInput = document.getElementById('totalPlatesInput');
    if (platesInput && (platesInput.dataset.manual !== 'true' || Number(platesInput.value) === 0)) {
        platesInput.value = autoPlateCount;
    }

    // Hitung Potongan & Biaya Biro
    let discount = 0;
    let biroFee = 0;
    const currentPlates = platesInput ? Number(platesInput.value || autoPlateCount) : autoPlateCount;

    if (customerType === 'vip') {
        discount = originalTotal; // Gratis 100%
    } else if (customerType === 'yarwe') {
        // 🔥 RUMUS: JUMLAH PIRING × Rp 2.000
        biroFee = currentPlates * 2000;
    }

    const grandTotal = (customerType === 'vip') ? 0 : (originalTotal + biroFee);
    updateTotals(originalTotal, discount, biroFee, grandTotal, currentPlates);
}

function updateTotals(subtotal, discount, biroFee, grandTotal = 0, plateCount = 0) {
    document.getElementById('lblSubtotal').textContent = `Rp ${subtotal.toLocaleString('id-ID')}`;
    document.getElementById('lblDiscount').textContent = `-Rp ${discount.toLocaleString('id-ID')}`;

    // Tampilkan keterangan piring di label biro
    if (customerType === 'yarwe') {
        document.getElementById('lblBiro').textContent = `+Rp ${biroFee.toLocaleString('id-ID')} (${plateCount} piring × 2.000)`;
    } else {
        document.getElementById('lblBiro').textContent = `+Rp 0`;
    }

    document.getElementById('lblGrandTotal').textContent = `Rp ${grandTotal.toLocaleString('id-ID')}`;
    document.getElementById('lblGrandTotal').dataset.val = grandTotal;
    updateChange();
}

// Proses Transaksi & Tampilkan Struk
async function handleCheckout() {
    if (cart.length === 0) {
        alert('Keranjang belanja masih kosong!');
        return;
    }

    const grandTotal = Number(document.getElementById('lblGrandTotal').dataset.val || 0);
    const cashReceived = Number(document.getElementById('cashReceived').value || 0);

    if (customerType !== 'vip' && cashReceived < grandTotal) {
        alert('Nominal uang bayar masih kurang!');
        return;
    }

    const btn = document.getElementById('btnCheckout');
    btn.disabled = true;
    btn.textContent = 'Memproses...';

    try {
        const payload = {
            customer_type: customerType,
            payment_method: 'cash',
            cash_received: customerType === 'vip' ? 0 : cashReceived,
            total_plates: customerType === 'yarwe' ? Number(document.getElementById('totalPlatesInput')?.value || 0) : 0,
            items: cart.map(i => ({ product_id: i.product_id, qty: i.qty }))
        };

        const res = await api.pos.checkout(payload);

        // Tampilkan Modal Struk Thermal
        showReceiptModal(res.data);

        // Reset keranjang
        cart = [];
        document.getElementById('cashReceived').value = '';
        renderCart();
        await loadMenu(); // Refresh stok real-time

    } catch (error) {
        alert('Gagal checkout: ' + error.message);
    } finally {
        btn.disabled = false;
        btn.textContent = 'Bayar & Selesaikan';
    }
}

// Fungsi Menampilkan Struk Thermal
function showReceiptModal(sale) {
    document.getElementById('recInvoice').textContent = sale.invoice_number;
    document.getElementById('recDate').textContent = new Date(sale.sale_date).toLocaleString('id-ID');
    document.getElementById('recCashier').textContent = sale.user?.name || 'Kasir';
    document.getElementById('recCustomer').textContent = sale.customer_type.toUpperCase();

    // Render Item di Struk
    const itemsContainer = document.getElementById('recItemsList');
    itemsContainer.innerHTML = '';
    sale.details.forEach(d => {
        itemsContainer.innerHTML += `
      <div class="receipt-item-row">
        <span>${d.qty}x ${d.product_name}</span>
        <span>Rp ${Number(d.subtotal).toLocaleString('id-ID')}</span>
      </div>
    `;
    });

    // Totals
    document.getElementById('recSubtotal').textContent = `Rp ${Number(sale.original_total).toLocaleString('id-ID')}`;

    const discountRow = document.getElementById('recRowDiscount');
    if (Number(sale.total_discount) > 0) {
        discountRow.style.display = 'flex';
        document.getElementById('recDiscount').textContent = `-Rp ${Number(sale.total_discount).toLocaleString('id-ID')}`;
    } else {
        discountRow.style.display = 'none';
    }

    const biroRow = document.getElementById('recRowBiro');
    if (Number(sale.yarwe_total) > 0) {
        biroRow.style.display = 'flex';
        document.getElementById('recBiro').textContent = `+Rp ${Number(sale.yarwe_total).toLocaleString('id-ID')}`;
    } else {
        biroRow.style.display = 'none';
    }

    document.getElementById('recGrandTotal').textContent = `Rp ${Number(sale.final_total).toLocaleString('id-ID')}`;
    document.getElementById('recCash').textContent = `Rp ${Number(sale.cash_received).toLocaleString('id-ID')}`;
    document.getElementById('recChange').textContent = `Rp ${Number(sale.cash_change).toLocaleString('id-ID')}`;
    document.getElementById('recTimeFooter').textContent = `*** ${new Date().toLocaleDateString('id-ID')} ***`;

    // Buka Modal
    document.getElementById('receiptModal').style.display = 'flex';
}

function closeReceiptModal() {
    document.getElementById('receiptModal').style.display = 'none';
}

function updateChange() {
    const grandTotal = Number(document.getElementById('lblGrandTotal').dataset.val || 0);
    const received = Number(document.getElementById('cashReceived').value || 0);
    const change = Math.max(0, received - grandTotal);
    document.getElementById('lblChange').textContent = 'Rp ' + change.toLocaleString('id-ID');
}
