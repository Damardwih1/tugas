<?php
// app.php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Load user data
function loadUsers() {
    $data = file_get_contents('users.json');
    return json_decode($data, true) ?: [];
}

// Get current user
$users = loadUsers();
$currentUser = $_SESSION['user'];
$userCreatedAt = isset($users[$currentUser]['created_at']) ? $users[$currentUser]['created_at'] : 'Unknown';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
    <title>Caffè Fiorentino</title>
    <style>
      * {
        box-sizing: border-box;
      }
      body, html {
        margin: 0; padding: 0; height: 100vh; width: 100vw;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #f7f1e1;
        color: #3e2723;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        overflow: hidden;
      }
      #app {
        display: flex;
        flex-direction: column;
        height: 100vh;
        width: 100vw;
        position: relative;
        background: linear-gradient(135deg, #d7ccc8 0%, #8d6e63 100%);
        background-image:
          url('https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=800&q=80');
        background-size: cover;
        background-position: center;
      }
      header {
        background: rgba(62, 39, 35, 0.85);
        text-align: center;
        padding: 1rem 0;
        font-size: 1.7rem;
        color: #f9fbe7;
        font-weight: 700;
        letter-spacing: 2px;
        user-select: none;
        -webkit-user-select: none;
        box-shadow: 0 3px 10px rgba(0,0,0,0.5);
        position: relative;
      }
      /* Back button for menu page */
      #btn-back {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        background: transparent;
        border: none;
        font-size: 1.7rem;
        color: #f9fbe7;
        cursor: pointer;
        user-select: none;
        display: none;
      }
      #btn-back:focus {
        outline: 2px solid #bf360c;
      }

      main {
        flex-grow: 1;
        overflow-y: auto;
        padding: 1rem 1rem 1rem 1rem;
        background: rgba(247, 243, 242, 0.87);
        backdrop-filter: saturate(180%) blur(6px);
        display: flex;
        flex-direction: column;
      }

      /* Category selection page */
      #category-page {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        align-items: center;
        gap: 2.5rem;
        height: auto;
      }
      .category-card {
        background: rgba(255, 248, 236, 0.95);
        border-radius: 18px;
        box-shadow: 0 4px 20px rgba(62,39,35,0.4);
        width: 180px;
        height: 180px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        transition: transform 0.3s ease;
      }
      .category-card:hover, .category-card:focus {
        transform: translateY(-8px);
        box-shadow: 0 8px 30px rgba(62,39,35,0.6);
        outline: none;
      }
      .category-card img {
        width: 100px;
        height: 100px;
        margin-bottom: 1rem;
        object-fit: contain;
      }
      .category-card span {
        font-weight: 700;
        font-size: 1.45rem;
        color: #4e342e;
        user-select: none;
      }

      /* Container wrapping menu + history */
      #content-wrapper {
        flex-grow: 1;
        display: flex;
        gap: 20px;
        height: 100%;
      }

      /* Menu + history layout adjustments for desktop */
      #menu-page {
        display: none;
        flex-direction: column;
        flex: 4;
      }
      .menu-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px,1fr));
        gap: 20px 18px;
      }
      /* History vertical panel */
      #history-panel {
        flex: 1;
        background: rgba(255,248,236,0.9);
        border-radius: 14px;
        padding: 1rem 1rem 0.5rem 1rem;
        color: #4e342e;
        font-weight: 600;
        font-size: 1.1rem;
        box-shadow: 0 3px 12px rgba(62,39,35,0.3);
        overflow-y: auto;
        max-height: 100%;
        display: flex;
        flex-direction: column;
      }
      #history-panel h3 {
        margin-top: 0;
        margin-bottom: 0.7rem;
        font-weight: 800;
        text-align: center;
        border-bottom: 1px solid #bf360c;
        padding-bottom: 6px;
        user-select: none;
      }
      #history-list {
        list-style: none;
        padding: 0;
        margin: 0;
        flex-grow: 1;
        overflow-y: auto;
      }
      #history-list li {
        padding: 6px 4px;
        border-bottom: 1px solid #ccc1b0;
        font-size: 0.95rem;
        line-height: 1.3;
      }
      #history-list li:last-child {
        border-bottom: none;
      }
      #btn-clear-history {
        background: #bf360c;
        color: #f9fbe7;
        border: none;
        border-radius: 8px;
        padding: 0.45rem 0.8rem;
        font-weight: 700;
        font-size: 0.95rem;
        margin-top: 8px;
        cursor: pointer;
        transition: background-color 0.25s ease;
        user-select: none;
      }
      #btn-clear-history:hover, #btn-clear-history:focus {
        background-color: #6d4c41;
        outline: none;
      }

      .menu-item {
        background: #fff8f1;
        border-radius: 14px;
        box-shadow: 0 4px 18px rgba(62,39,35,0.32);
        overflow: hidden;
        cursor: default;
        display: flex;
        flex-direction: row;
        padding: 10px;
      }
      .menu-item:hover {
        box-shadow: 0 9px 28px rgba(62,39,35,0.5);
      }
      .menu-image {
        width: 140px;
        height: 140px;
        object-fit: cover;
        border-radius: 14px;
        flex-shrink: 0;
      }
      .menu-content {
        padding-left: 16px;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        flex-grow: 1;
      }
      .menu-title {
        font-weight: 700;
        font-size: 1.3rem;
        margin: 0 0 6px 0;
        color: #4e342e;
        line-height: 1.2;
      }
      .menu-desc {
        font-size: 0.85rem;
        color: #6d4c41;
        margin-top: 0;
        margin-bottom: 12px;
        line-height: 1.4;
        overflow: hidden;
      }
      .menu-price {
        font-weight: 700;
        font-size: 1.2rem;
        color: #bf360c;
        margin-bottom: 12px;
      }
      .btn-add-cart {
        background-color: #6d4c41;
        color: #f9fbe7;
        border: none;
        border-radius: 10px;
        padding: 0.45rem 0;
        font-weight: 700;
        font-size: 0; /* hide text */
        cursor: pointer;
        transition: background-color 0.25s ease;
        width: 38px;
        height: 38px;
        align-self: flex-start;
        position: relative;
      }
      .btn-add-cart:hover, .btn-add-cart:focus {
        background-color: #bf360c;
        outline: none;
      }
      .btn-add-cart span.icon {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 22px;
        height: 18px;
        background-image: url('https://cdn-icons-png.flaticon.com/512/263/263142.png');
        background-size: contain;
        background-repeat: no-repeat;
        background-position: center;
        transform: translate(-50%, -50%);
        display: block;
      }

      /* Keranjang button fixed bottom right */
      #btn-cart {
        position: fixed;
        bottom: 70px;
        right: 20px;
        background: #bf360c;
        border-radius: 50%;
        width: 54px;
        height: 54px;
        color: #f9fbe7;
        font-size: 2rem;
        cursor: pointer;
        box-shadow: 0 5px 10px rgba(0,0,0,0.35);
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        user-select: none;
        z-index: 110;
      }
      #btn-cart:hover {
        background: #6d4c41;
      }
      #cart-badge {
        position: absolute;
        top: 6px;
        right: 6px;
        background: #f9a825;
        color: #3e2723;
        font-weight: 700;
        font-size: 0.78rem;
        width: 19px;
        height: 19px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        user-select: none;
      }

      /* Side panel for cart and payment */
      #side-panel {
        position: fixed;
        top: 0;
        right: -100vw;
        width: 340px;
        max-width: 85vw;
        height: 100vh;
        background: #f8f4f1;
        box-shadow: -4px 0 18px rgba(0,0,0,0.45);
        transition: right 0.35s ease;
        z-index: 120;
        display: flex;
        flex-direction: column;
      }
      #side-panel.open {
        right: 0;
      }
      #side-panel header {
        padding: 1rem;
        font-weight: 700;
        font-size: 1.37rem;
        color: #4e342e;
        border-bottom: 1px solid #d7ccc8;
        display: flex;
        justify-content: space-between;
        align-items: center;
      }
      #side-panel .close-panel {
        background: transparent;
        border: none;
        color: #4e342e;
        font-size: 1.8rem;
        cursor: pointer;
        padding: 0 8px;
        line-height: 1;
      }
      #side-panel .content {
        flex-grow: 1;
        overflow-y: auto;
        padding: 0.8rem 1.2rem;
      }
      #side-panel .content::-webkit-scrollbar {
        width: 6px;
      }
      #side-panel .content::-webkit-scrollbar-thumb {
        background-color: #bf360c;
        border-radius: 3px;
      }
      #side-panel ul.cart-list {
        list-style: none;
        padding: 0;
        margin: 0;
      }
      #side-panel ul.cart-list li {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        border-bottom: 1px solid #d7ccc8;
        padding-bottom: 0.4rem;
      }
      #side-panel ul.cart-list li .item-info {
        flex-grow: 1;
      }
      #side-panel ul.cart-list li .item-name {
        font-weight: 700;
        color: #4e342e;
      }
      #side-panel ul.cart-list li .item-qty {
        margin-left: 5px;
        font-size: 0.85rem;
        color: #6d4c41;
      }
      #side-panel ul.cart-list li .item-price {
        margin-left: 8px;
        font-weight: 700;
        color: #bf360c;
      }
      #side-panel ul.cart-list li button.btn-remove {
        background: transparent;
        border: none;
        font-size: 1.15rem;
        color: #bf360c;
        cursor: pointer;
        align-self: flex-start;
        padding: 0 7px;
        transition: color 0.25s ease;
      }
      #side-panel ul.cart-list li button.btn-remove:hover {
        color: #6d4c41;
      }
      #side-panel .total {
        font-weight: 700;
        font-size: 1.3rem;
        color: #bf360c;
        margin-top: 1rem;
        text-align: right;
      }
      #side-panel button.btn-checkout {
        width: 100%;
        background: #6d4c41;
        color: #f9fbe7;
        border: none;
        border-radius: 8px;
        padding: 0.75rem 0;
        font-weight: 700;
        font-size: 1.15rem;
        cursor: pointer;
        transition: background-color 0.25s ease;
      }
      #side-panel button.btn-checkout:disabled {
        background: #d7ccc8;
        cursor: not-allowed;
        color: #8d6e63;
      }
      #side-panel button.btn-checkout:hover:not(:disabled) {
        background: #bf360c;
      }

      /* Payment options */
      #payment-options {
        margin-top: 0.7rem;
      }
      #payment-options label {
        display: block;
        margin-bottom: 0.57rem;
        cursor: pointer;
        font-weight: 600;
        color: #4e342e;
      }
      #payment-options input[type="radio"] {
        margin-right: 9px;
        accent-color: #bf360c;
      }

      #ewallet-options {
        margin-left: 1.1rem;
        margin-top: 0.45rem;
        margin-bottom: 0.7rem;
        display: none;
      }
      #ewallet-options label {
        font-weight: normal;
        color: #6d4c41;
      }

      /* Modal for receipt */
      #receipt-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(62,39,35,0.85);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 150;
        padding: 1.4rem;
      }
      #receipt-modal.open {
        display: flex;
      }
      #receipt-content {
        background: #f9fbe7;
        max-width: 380px;
        width: 100%;
        border-radius: 14px;
        padding: 2.2rem 2.4rem;
        color: #3e2723;
        box-shadow: 0 5px 14px rgba(0,0,0,0.35);
        text-align: center;
        position: relative;
        overflow: hidden;
      }
      #receipt-content h2 {
        margin-top: 0;
        margin-bottom: 1rem;
        font-weight: 900;
        color: #bf360c;
        font-size: 1.9rem;
      }
      #receipt-content .order-number {
        font-size: 2rem;
        margin: 1rem 0 0.8rem 0;
        font-weight: 900;
        letter-spacing: 5px;
        font-family: monospace, monospace;
        user-select: all;
      }
      #receipt-content .order-date {
        font-size: 1rem;
        font-weight: 600;
        color: #4e342e;
        margin-bottom: 1.5rem;
      }
      #receipt-content .barcode {
        margin-bottom: 1.6rem;
      }
      #receipt-content ul {
        list-style: none;
        padding: 0;
        margin: 0 0 1.6rem 0;
        text-align: left;
        max-height: 180px;
        overflow-y: auto;
      }
      #receipt-content ul li {
        font-size: 1.05rem;
        margin-bottom: 0.36rem;
        border-bottom: 1px solid #d7ccc8;
        padding-bottom: 0.4rem;
      }
      #receipt-content button {
        background: #6d4c41;
        color: #f9fbe7;
        border: none;
        border-radius: 8px;
        padding: 0.55rem 1.2rem;
        font-weight: 700;
        cursor: pointer;
        font-size: 1.05rem;
        transition: background-color 0.28s ease;
      }
      #receipt-content button:hover {
        background: #bf360c;
      }

      /* Unique flickering candle flame effect bottom-left as surprise feature */
      #flame-container {
        position: absolute;
        bottom: 10px;
        left: 10px;
        pointer-events: none;
        width: 40px;
        height: 60px;
        user-select: none;
      }
      #flame {
        width: 40px;
        height: 60px;
        background: radial-gradient(circle at 50% 70%, #fce88d 40%, #f9d71c 70%, transparent 80%);
        border-radius: 50% 50% 50% 50% / 60% 60% 40% 40%;
        filter: drop-shadow(0 0 4px #f9d71c);
        animation: flicker 1.5s infinite alternate;
      }
      @keyframes flicker {
        0% {
          transform: translateY(0) scale(1);
          filter: drop-shadow(0 0 6px #f9d71c);
        }
        50% {
          transform: translateY(-3px) scale(1.05);
          filter: drop-shadow(0 0 9px #fac90f);
        }
        100% {
          transform: translateY(0) scale(1);
          filter: drop-shadow(0 0 6px #f9d71c);
        }
      }

      /* Responsive adjustments */
      @media (max-width: 960px) {
        #content-wrapper {
          flex-direction: column;
          gap: 20px;
        }
        #history-panel {
          max-height: 120px;
          overflow-y: auto;
          margin-bottom: 12px;
          border-radius: 14px;
          box-shadow: 0 3px 12px rgba(62,39,35,0.3);
        }
      }
      @media (max-width: 600px) and (max-height: 600px) {
        #side-panel {
          width: 100vw;
        }
        #btn-back {
          font-size: 1.6rem;
          left: 12px;
        }
        .category-card {
          width: 150px;
          height: 150px;
        }
        .menu-grid {
          grid-template-columns: repeat(auto-fit, minmax(100%,1fr));
          gap: 14px 12px;
        }
        .menu-item {
          flex-direction: column;
          padding: 10px;
        }
        .menu-image {
          width: 100%;
          height: 220px;
          border-radius: 14px;
          margin-bottom: 10px;
        }
        .menu-content {
          padding-left: 0;
          height: auto;
        }
        .btn-add-cart {
          width: 100%;
          font-size: 1rem;
          padding: 0.5rem 0;
        }
        #btn-cart {
          bottom: 90px;
          right: 15px;
        }
        #history-panel {
          max-height: 120px;
          overflow-y: auto;
          margin-bottom: 15px;
        }
      }

      /* User info and logout button */
      .user-info {
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        display: flex;
        align-items: center;
        gap: 10px;
      }
      .user-info span {
        font-size: 0.9rem;
        color: #f9fbe7;
      }
      .logout-btn {
        background: transparent;
        border: 1px solid #f9fbe7;
        color: #f9fbe7;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.8rem;
        cursor: pointer;
        transition: all 0.3s;
      }
      .logout-btn:hover {
        background: #f9fbe7;
        color: #3e2723;
      }
    </style>
</head>
<body>
<div id="app" role="main" aria-label="Caffè Fiorentino Application">
  <header>
    <button id="btn-back" aria-label="Kembali ke Pilihan Kategori">&larr;</button>
    Caffè Fiorentino
    <div class="user-info">
      <span>Welcome, <?php echo htmlspecialchars($currentUser); ?></span>
      <form action="logout.php" method="post" style="display:inline;">
        <button type="submit" class="logout-btn">Logout</button>
      </form>
    </div>
  </header>
  <main>
    <section id="category-page" aria-label="Pilih kategori menu">
      <div tabindex="0" role="button" class="category-card" data-category="minuman" aria-pressed="false" aria-label="Pilih kategori Minuman">
        <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Ilustrasi cangkir kopi" />
        <span>Minuman</span>
      </div>
      <div tabindex="0" role="button" class="category-card" data-category="makanan" aria-pressed="false" aria-label="Pilih kategori Makanan">
        <img src="https://cdn-icons-png.flaticon.com/512/1046/1046784.png" alt="Ikon makanan" />
        <span>Makanan</span>
      </div>
      <div tabindex="0" role="button" class="category-card" data-category="paket" aria-pressed="false" aria-label="Pilih kategori Paket">
        <img src="https://cdn-icons-png.flaticon.com/512/3143/3143460.png" alt="Ilustrasi paket makanan dan minuman" />
        <span>Paket</span>
      </div>
    </section>

    <div id="content-wrapper" style="display:none;">
      <section id="menu-page" aria-label="Daftar menu">
        <div class="menu-grid" id="menu-container" tabindex="0" aria-live="polite" aria-atomic="true" aria-relevant="additions removals">
          <!-- Menu akan dimuat di sini -->
        </div>
      </section>

      <section id="history-panel" aria-label="Riwayat Pesanan Anda">
        <h3>Riwayat Pesanan</h3>
        <ul id="history-list" aria-live="polite" aria-relevant="additions"></ul>
        <button id="btn-clear-history" aria-label="Hapus semua riwayat pesanan">Hapus Riwayat</button>
      </section>
    </div>
  </main>

  <button id="btn-cart" aria-label="Buka Keranjang Belanja">
    🛒
    <div id="cart-badge" aria-live="polite" aria-atomic="true" style="display:none;">0</div>
  </button>

  <div id="side-panel" aria-modal="true" role="dialog" aria-labelledby="side-panel-title" aria-hidden="true">
    <header id="side-panel-title">Keranjang Belanja
      <button class="close-panel" id="close-panel-btn" aria-label="Tutup Panel Keranjang & Pembayaran">&times;</button>
    </header>
    <div class="content" id="cart-content">
      <ul class="cart-list" aria-live="polite" aria-relevant="additions removals"></ul>
      <div class="total" id="cart-total">Total: Rp 0</div>

      <div id="payment-options" role="radiogroup" aria-labelledby="payment-label">
        <span id="payment-label" style="font-weight:700; margin-bottom: 0.3rem; display: inline-block;">Pilih Metode Pembayaran:</span>
        <label><input type="radio" name="payment-method" value="Cashier" checked> Bayar di Kasir</label>
        <label><input type="radio" name="payment-method" value="EWallet"> E-Wallet</label>
        <div id="ewallet-options" aria-label="Opsi E-Wallet">
          <label><input type="radio" name="ewallet" value="Dana" checked> Dana</label>
          <label><input type="radio" name="ewallet" value="Gopay"> Gopay</label>
          <label><input type="radio" name="ewallet" value="ShopeePay"> Shopee Pay</label>
          <label><input type="radio" name="ewallet" value="PayPal"> PayPal</label>
        </div>
      </div>

      <button id="btn-checkout" class="btn-checkout" disabled>Checkout</button>
    </div>
  </div>

  <div id="receipt-modal" role="alertdialog" aria-modal="true" aria-labelledby="receipt-title" aria-describedby="receipt-desc">
    <div id="receipt-content">
      <h2 id="receipt-title">Bukti Pembelian</h2>
      <div class="order-number" id="order-number"></div>
      <div class="order-date" id="order-date"></div>
      <canvas id="barcode" class="barcode"></canvas>
      <ul id="receipt-items"></ul>
      <button id="btn-close-receipt" aria-label="Tutup bukti pembelian">Tutup</button>
      <div id="flame-container">
        <div id="flame"></div>
      </div>
    </div>
  </div>
</div>

<script>
  (() => {
    'use strict';
    const menuItems = [
      {id:1, category:'minuman', name:'Espresso', desc:'Kopi pekat dengan crema tebal', price:15000, img:'https://images.unsplash.com/photo-1511920170033-f8396924c348?auto=format&fit=crop&w=400&q=80'},
      {id:2, category:'minuman', name:'Americano', desc:'Espresso pekat, dipadukan air panas, hasilkan rasa kopi yang kuat dan smooth.', price:18000, img:'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=400&q=80'},
      {id:3, category:'minuman', name:'Cappuccino', desc:'Espresso, susu panas, dan busa lembut—perpaduan creamy yang nggak bisa ditolak', price:22000, img:'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=400&q=80'},
      {id:4, category:'minuman', name:'Cafe Latte', desc:'Espresso yang halus, dipadu susu panas, menciptakan rasa lembut dan creamy.', price:22000, img:'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=400&q=80'},
      {id:5, category:'minuman', name:'Flat White', desc:'Espresso dengan susu panas dan sedikit busa, menghasilkan rasa yang kaya dan velvety.', price:23000, img:'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=400&q=80'},
      {id:6, category:'minuman', name:'Mocha', desc:'Perpaduan espresso, susu panas, dan cokelat, memberikan sensasi manis dan kaya.', price:25000, img:'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=400&q=80'},
      {id:7, category:'minuman', name:'Chocolate Frappe', desc:'Es krim cokelat, espresso, dan es, blend jadi minuman manis, dingin, dan segar.', price:28000, img:'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=400&q=80'},
      {id:8, category:'minuman', name:'Vanilla Latte', desc:'Espresso, susu panas, dan sirup vanilla, lembut dengan sentuhan manis.', price:26000, img:'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=400&q=80'},
      {id:9, category:'minuman', name:'Caramel Macchiato', desc:'Espresso, susu panas, dan sirup karamel, dipadu sempurna untuk rasa manis dan kaya.', price:27000, img:'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=400&q=80'},
      {id:10, category:'minuman', name:'Iced Coffee', desc:'Kopi dingin yang segar, sempurna untuk menyegarkan hari.', price:21000, img:'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=400&q=80'},
      {id:11, category:'minuman', name:'Black Tea', desc:'Teh hitam klasik, kaya rasa dan penuh aroma.', price:14000, img:'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=400&q=80'},
      {id:12, category:'minuman', name:'Green Tea', desc:'Teh hijau yang ringan dan menyegarkan, penuh manfaat.', price:16000, img:'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=400&q=80'},
      {id:13, category:'minuman', name:'Herbal Tea', desc:'Teh herbal alami dengan rasa ringan, menenangkan dan menyegarkan.', price:17000, img:'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=400&q=80'},
      {id:26, category:'minuman', name:'Banana Smoothie', desc:'Smoothie pisang creamy, manis, dan segar.', price:23000, img:'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=400&q=80'},
      {id:27, category:'minuman', name:'Strawberry Smoothie', desc:'Smoothie stroberi segar, manis dan nikmat.', price:23000, img:'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=400&q=80'},
      {id:28, category:'minuman', name:'Iced Matcha Latte', desc:'Matcha dingin dengan susu, lembut dan penuh rasa.', price:28000, img:'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=400&q=80'},
      {id:14, category:'makanan', name:'Cheesecake', desc:'Kue keju lembut dan creamy', price:35000, img:'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=400&q=80'},
      {id:15, category:'makanan', name:'Chocolate Brownie', desc:'Brownie coklat padat dan lezat', price:30000, img:'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=400&q=80'},
      {id:16, category:'makanan', name:'Croissant', desc:'Roti lapis mentega klasik', price:28000, img:'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=400&q=80'},
      {id:17, category:'makanan', name:'Pancakes', desc:'Pancake dengan sirup maple', price:33000, img:'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=400&q=80'},
      {id:18, category:'makanan', name:'Sandwich', desc:'Roti lapis isi daging dan sayur', price:35000, img:'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=400&q=80'},
      {id:19, category:'makanan', name:'Salad Bowl', desc:'Sayur segar dengan dressing', price:32000, img:'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=400&q=80'},
      {id:20, category:'makanan', name:'Soup of the Day', desc:'Sup hangat dengan roti', price:30000, img:'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=400&q=80'},
      {id:21, category:'makanan', name:'Macarons', desc:'Kue kecil warna-warni', price:25000, img:'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=400&q=80'},
      {id:22, category:'makanan', name:'Tiramisu', desc:'Kue kopi classic Italia', price:37000, img:'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=400&q=80'},
      {id:23, category:'makanan', name:'Lemon Tart', desc:'Kue tart lemon segar', price:34000, img:'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=400&q=80'},
      {id:24, category:'makanan', name:'Muffin Pisang', desc:'Muffin lembut rasa pisang', price:26000, img:'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=400&q=80'},
      {id:25, category:'makanan', name:'Cinnamon Roll', desc:'Roti gulung kayu manis', price:28000, img:'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=400&q=80'},
      {id:29, category:'makanan', name:'Choco Muffin', desc:'Muffin coklat lezat', price:28000, img:'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=400&q=80'},
      {id:30, category:'makanan', name:'Honey Toast', desc:'Roti panggang dengan madu manis', price:33000, img:'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=400&q=80'},
      {id:31, category:'paket', name:'Paket Hemat Kopi & Croissant', desc:'Kopi pilihan plus croissant lezat', price:44000, img:'https://images.unsplash.com/photo-1576224209405-ef560b78b0f2?auto=format&fit=crop&w=400&q=80'},
      {id:32, category:'paket', name:'Paket Snack & Minuman', desc:'Brownie + teh herbal dalam satu paket', price:40000, img:'https://images.unsplash.com/photo-1603062095397-3d688ceae5aa?auto=format&fit=crop&w=400&q=80'},
      {id:33, category:'paket', name:'Paket Sarapan Lengkap', desc:'Sandwich, kopi, dan jus segar', price:60000, img:'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=400&q=80'}
    ];

    let cart = [];
    let orderHistory = JSON.parse(localStorage.getItem('orderHistory')) || [];

    const categoryPage = document.getElementById('category-page');
    const menuPage = document.getElementById('menu-page');
    const menuContainer = document.getElementById('menu-container');
    const btnBack = document.getElementById('btn-back');

    const btnCart = document.getElementById('btn-cart');
    const cartBadge = document.getElementById('cart-badge');

    const sidePanel = document.getElementById('side-panel');
    const closePanelBtn = document.getElementById('close-panel-btn');
    const cartList = sidePanel.querySelector('ul.cart-list');
    const cartTotal = document.getElementById('cart-total');
    const btnCheckout = document.getElementById('btn-checkout');

    const paymentRadios = sidePanel.querySelectorAll('input[name="payment-method"]');
    const ewalletOptions = document.getElementById('ewallet-options');
    const ewalletRadios = ewalletOptions.querySelectorAll('input[name="ewallet"]');

    const receiptModal = document.getElementById('receipt-modal');
    const orderNumberElem = document.getElementById('order-number');
    const orderDateElem = document.getElementById('order-date');
    const receiptItemsList = document.getElementById('receipt-items');
    const btnCloseReceipt = document.getElementById('btn-close-receipt');
    const barcodeCanvas = document.getElementById('barcode');

    const historyPanel = document.getElementById('history-panel');
    const historyList = document.getElementById('history-list');
    const btnClearHistory = document.getElementById('btn-clear-history');
    const contentWrapper = document.getElementById('content-wrapper') || (() => {
      const wrapper = document.createElement('div');
      wrapper.id = 'content-wrapper';
      wrapper.style.flexGrow = '1';
      wrapper.style.display = 'none';
      wrapper.style.gap = '20px';
      wrapper.style.height = '100%';
      wrapper.style.flexDirection = 'row';
      document.querySelector('main').appendChild(wrapper);
      wrapper.appendChild(menuPage);
      wrapper.appendChild(historyPanel);
      return wrapper;
    })();

    let currentCategory = null;

    function formatIDR(n) {
      return 'Rp ' + n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
    function generateOrderNumber() {
      const now = new Date();
      return now.getTime().toString().slice(-6).padStart(6, '0');
    }

    function renderMenu(category) {
      menuContainer.innerHTML = '';
      const filtered = menuItems.filter(i => i.category === category);
      if(filtered.length === 0) {
        menuContainer.textContent = 'Menu tidak ditemukan.';
        return;
      }
      filtered.forEach(item => {
        const card = document.createElement('article');
        card.className = 'menu-item';
        card.setAttribute('tabindex','0');
        card.setAttribute('aria-label', `${item.name}, ${item.desc}, harga ${formatIDR(item.price)}`);

        const img = document.createElement('img');
        img.className = 'menu-image';
        img.src = item.img;
        img.alt = item.name;

        const content = document.createElement('div');
        content.className = 'menu-content';

        const title = document.createElement('h3');
        title.className = 'menu-title';
        title.textContent = item.name;

        const desc = document.createElement('p');
        desc.className = 'menu-desc';
        desc.textContent = item.desc;

        const price = document.createElement('div');
        price.className = 'menu-price';
        price.textContent = formatIDR(item.price);

        const btn = document.createElement('button');
        btn.className = 'btn-add-cart';
        btn.setAttribute('aria-label', `Tambahkan ${item.name} seharga ${formatIDR(item.price)} ke keranjang`);
        btn.title = `Tambahkan ${item.name} ke keranjang`;
        btn.textContent = '';
        btn.style.fontSize = '0'; 
        btn.style.padding = '0.4rem 0';
        btn.style.width = '38px';
        btn.style.height = '38px';
        btn.style.position = 'relative';

        const iconSpan = document.createElement('span');
        iconSpan.setAttribute('aria-hidden', 'true');
        iconSpan.className = 'icon';
        iconSpan.style.position = 'absolute';
        iconSpan.style.top = '50%';
        iconSpan.style.left = '50%';
        iconSpan.style.width = '22px';
        iconSpan.style.height = '18px';
        iconSpan.style.backgroundImage = 'url("https://cdn-icons-png.flaticon.com/512/263/263142.png")';
        iconSpan.style.backgroundSize = 'contain';
        iconSpan.style.backgroundRepeat = 'no-repeat';
        iconSpan.style.backgroundPosition = 'center';
        iconSpan.style.transform = 'translate(-50%, -50%)';
        btn.appendChild(iconSpan);

        btn.addEventListener('click', () => addToCart(item.id));
        btn.addEventListener('mouseover', () => btn.style.backgroundColor = '#bf360c');
        btn.addEventListener('mouseout', () => btn.style.backgroundColor = '#6d4c41');
        btn.addEventListener('focus', () => btn.style.backgroundColor = '#bf360c');
        btn.addEventListener('blur', () => btn.style.backgroundColor = '#6d4c41');

        content.appendChild(title);
        content.appendChild(desc);
        content.appendChild(price);
        content.appendChild(btn);

        card.appendChild(img);
        card.appendChild(content);

        menuContainer.appendChild(card);
      });
    }

    function openCategoryPage() {
      currentCategory = null;
      categoryPage.style.display = 'flex';
      menuPage.style.display = 'none';
      btnBack.style.display = 'none';
      contentWrapper.style.display = 'none';
      document.activeElement.blur();
    }
    function openMenuPage(category) {
      currentCategory = category;
      categoryPage.style.display = 'none';
      menuPage.style.display = 'flex';
      btnBack.style.display = 'inline-block';
      contentWrapper.style.display = 'flex';
      renderMenu(category);
      menuContainer.focus();
      renderOrderHistory();
    }

    function addToCart(itemId) {
      let findIndex = cart.findIndex(c => c.id === itemId);
      if(findIndex > -1){
        cart[findIndex].qty++;
      } else {
        let menuItem = menuItems.find(m => m.id === itemId);
        if(menuItem){
          cart.push({
            id: menuItem.id,
            name: menuItem.name,
            price: menuItem.price,
            qty:1
          });
        }
      }
      updateCartUI();
      openSidePanel();
    }
    function removeFromCart(itemId) {
      cart = cart.filter(item => item.id !== itemId);
      updateCartUI();
    }
    function changeQty(itemId, qty) {
      if(qty < 1) return;
      let item = cart.find(c => c.id === itemId);
      if(item){
        item.qty = qty;
        updateCartUI();
      }
    }
    function calculateTotal() {
      return cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
    }
    function updateCartUI() {
      let totalItems = cart.reduce((sum, item) => sum + item.qty, 0);
      if(totalItems > 0){
        cartBadge.style.display = 'flex';
        cartBadge.textContent = totalItems;
      } else {
        cartBadge.style.display = 'none';
      }

      cartList.innerHTML = '';
      if(cart.length === 0){
        let liEmpty = document.createElement('li');
        liEmpty.textContent = 'Keranjang kosong';
        liEmpty.style.textAlign = 'center';
        liEmpty.style.color = '#6d4c41';
        liEmpty.style.fontStyle = 'italic';
        cartList.appendChild(liEmpty);
        btnCheckout.disabled = true;
      } else {
        cart.forEach(item => {
          let li = document.createElement('li');
          li.setAttribute('aria-label', `${item.name} sebanyak ${item.qty}, harga ${formatIDR(item.price * item.qty)}`);

          let infoDiv = document.createElement('div');
          infoDiv.className = 'item-info';

          let nameSpan = document.createElement('span');
          nameSpan.className = 'item-name';
          nameSpan.textContent = item.name;

          let qtyInput = document.createElement('input');
          qtyInput.type = 'number';
          qtyInput.min = '1';
          qtyInput.value = item.qty;
          qtyInput.style.width = '45px';
          qtyInput.style.marginLeft = '10px';
          qtyInput.setAttribute('aria-label', `Ubah jumlah ${item.name} di keranjang`);
          qtyInput.addEventListener('input', e => {
            let val = parseInt(e.target.value);
            if(isNaN(val) || val < 1) {
              val = 1;
              qtyInput.value = val;
            }
            changeQty(item.id, val);
          });

          infoDiv.appendChild(nameSpan);
          infoDiv.appendChild(qtyInput);

          let priceSpan = document.createElement('span');
          priceSpan.className = 'item-price';
          priceSpan.textContent = formatIDR(item.price * item.qty);

          let btnRemove = document.createElement('button');
          btnRemove.className = 'btn-remove';
          btnRemove.setAttribute('aria-label', `Hapus ${item.name} dari keranjang`);
          btnRemove.textContent = '✕';
          btnRemove.addEventListener('click', () => removeFromCart(item.id));

          li.appendChild(infoDiv);
          li.appendChild(priceSpan);
          li.appendChild(btnRemove);

          cartList.appendChild(li);
        });
        btnCheckout.disabled = false;
      }

      cartTotal.textContent = 'Total: ' + formatIDR(calculateTotal());
    }

    function openSidePanel() {
      sidePanel.classList.add('open');
      sidePanel.setAttribute('aria-hidden','false');
      sidePanel.querySelector('.content').focus();
    }
    function closeSidePanel() {
      sidePanel.classList.remove('open');
      sidePanel.setAttribute('aria-hidden','true');
      btnCart.focus();
    }

    function onPaymentMethodChange() {
      let selectedPayment = [...paymentRadios].find(r => r.checked).value;
      if(selectedPayment === 'EWallet') {
        ewalletOptions.style.display = 'block';
      } else {
        ewalletOptions.style.display = 'none';
      }
    }

    function generateBarcode(code) {
      if(window.JsBarcode) {
        JsBarcode(barcodeCanvas, code, {
          format: "CODE128",
          lineColor: "#3e2723",
          width: 2,
          height: 60,
          displayValue: true,
          fontSize: 18,
          margin: 10,
          background: "#f9fbe7"
        });
      }
    }

    // Unique flickering candle flame effect bottom-left as surprise feature
    function animateReceiptBackground() {
      const receipt = document.getElementById('receipt-content');
      let hue = 20;
      if(receipt._animInterval) clearInterval(receipt._animInterval);
      receipt._animInterval = setInterval(() => {
        hue = (hue + 1) % 360;
        receipt.style.background = `hsl(${hue}, 50%, 95%)`;
      }, 60);
    }
    function stopAnimateReceiptBackground() {
      const receipt = document.getElementById('receipt-content');
      if(receipt._animInterval) {
        clearInterval(receipt._animInterval);
        receipt._animInterval = null;
      }
      receipt.style.background = '#f9fbe7';
    }

    function showReceipt(orderNumber, items) {
      orderNumberElem.textContent = orderNumber;
      const nowDate = new Date();
      orderDateElem.textContent = "Tanggal: " + nowDate.toLocaleDateString('id-ID', {year:'numeric', month:'long', day:'numeric'}) + " - " + nowDate.toLocaleTimeString('id-ID');
      receiptItemsList.innerHTML = '';
      items.forEach(item => {
        let li = document.createElement('li');
        li.textContent = `${item.qty} x ${item.name} - ${formatIDR(item.price * item.qty)}`;
        receiptItemsList.appendChild(li);
      });
      generateBarcode(orderNumber);
      receiptModal.classList.add('open');
      receiptModal.setAttribute('aria-hidden','false');
      receiptModal.querySelector('button').focus();
      animateReceiptBackground();
      // Save order to history
      saveOrderHistory(items, orderNumber, nowDate);
    }

    function closeReceipt() {
      stopAnimateReceiptBackground();
      receiptModal.classList.remove('open');
      receiptModal.setAttribute('aria-hidden','true');
      cart.length = 0;
      updateCartUI();
      closeSidePanel();
      if(currentCategory){
        openMenuPage(currentCategory);
      } else {
        openCategoryPage();
      }
    }

    function saveOrderHistory(items, orderNumber, date) {
      const orderItems = items.map(i=>({id:i.id, name:i.name, qty:i.qty}));
      const newOrder = {
        orderNumber,
        date: date.toISOString(),
        items: orderItems
      };
      orderHistory.unshift(newOrder);
      if(orderHistory.length > 20) orderHistory.pop();
      localStorage.setItem('orderHistory', JSON.stringify(orderHistory));
      renderOrderHistory();
    }

    function renderOrderHistory() {
      historyList.innerHTML = '';
      if(orderHistory.length === 0){
        const li = document.createElement('li');
        li.textContent = 'Belum ada riwayat pesanan.';
        historyList.appendChild(li);
        return;
      }
      orderHistory.forEach(order => {
        const li = document.createElement('li');
        const d = new Date(order.date);
        const dateStr = d.toLocaleDateString('id-ID', {year:'numeric',month:'short',day:'numeric'});
        const timeStr = d.toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'});
        let itemsText = order.items.map(i => `${i.qty}x ${i.name}`).join(', ');
        li.textContent = `#${order.orderNumber} - ${dateStr} ${timeStr} - ${itemsText}`;
        historyList.appendChild(li);
      });
    }

    function clearHistory() {
      if(confirm('Apakah Anda yakin ingin menghapus semua riwayat pesanan?')) {
        orderHistory = [];
        localStorage.removeItem('orderHistory');
        renderOrderHistory();
      }
    }

    btnCheckout.addEventListener('click', () => {
      if(cart.length === 0) return;
      let orderNum = generateOrderNumber();
      showReceipt(orderNum, cart);
    });

    btnCart.addEventListener('click', openSidePanel);

    const closeBtn = document.querySelector('#side-panel .close-panel');
    if (closeBtn) {
      closeBtn.addEventListener('click', closeSidePanel);
      closeBtn.addEventListener('keypress', e => {
        if(e.key==='Enter' || e.key===' ') {
          e.preventDefault();
          closeSidePanel();
        }
      });
    }

    btnCloseReceipt.addEventListener('click', closeReceipt);
    paymentRadios.forEach(radio => radio.addEventListener('change', onPaymentMethodChange));

    btnBack.addEventListener('click', () => {
      openCategoryPage();
    });

    document.querySelectorAll('.category-card').forEach(card => {
      card.addEventListener('click', () => {
        openMenuPage(card.dataset.category);
      });
      card.addEventListener('keypress', e => {
        if(e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          openMenuPage(card.dataset.category);
        }
      });
    });

    btnClearHistory.addEventListener('click', clearHistory);

    window.addEventListener('keydown', e => {
      if(e.key === 'Escape'){
        if(receiptModal.classList.contains('open')) closeReceipt();
        else if(sidePanel.classList.contains('open')) closeSidePanel();
      }
    });

    function openCategoryPage() {
      currentCategory = null;
      categoryPage.style.display = 'flex';
      menuPage.style.display = 'none';
      btnBack.style.display = 'none';
      contentWrapper.style.display = 'none';
      document.activeElement.blur();
    }
    function openMenuPage(category) {
      currentCategory = category;
      categoryPage.style.display = 'none';
      menuPage.style.display = 'flex';
      btnBack.style.display = 'inline-block';
      contentWrapper.style.display = 'flex';
      renderMenu(category);
      menuContainer.focus();
      renderOrderHistory();
    }

    function init() {
      openCategoryPage();
      updateCartUI();
      onPaymentMethodChange();
      const script = document.createElement('script');
      script.src = 'https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js';
      document.body.appendChild(script);
    }
    init();
  })();
</script>
</body>
</html>