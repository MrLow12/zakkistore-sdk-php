# 🐘 Zakkistore SDK for PHP

**Official B2B Client Library for Zakki Store API Gateway**

Pustaka PHP resmi untuk memudahkan integrasi layanan Host-to-Host (H2H) prabayar/pascabayar, payment gateway QRIS otomatis, perbankan Virtual Account (VA), Noktel OTP virtual, mining reward, dan gacha koin Zakki Store ke dalam proyek PHP Anda (Laravel, Symfony, CodeIgniter, Yii, native PHP, bot WhatsApp/Telegram, dll).

---

## 🚀 Instalasi & Inisialisasi

Instal pustaka dari Composer:

```bash
composer require mrlow12/zakkistore-sdk
```

### Inisialisasi Klien

```php
require_once 'vendor/autoload.php';

use MrLow12\ZakkiStore\ZakkiStore;

// Inisialisasi klien SDK (Bisa tanpa base_url karena official otomatis ter-set!)
$zakki = new ZakkiStore([
    'token' => 'API_TOKEN_ANDA',
    'iduser' => 'IBO99',
    'pin' => '123456',              // PIN transaksi (Wajib untuk tabung & tarik)
    'autoWithdraw' => true          // Aktifkan penarikan saldo VA otomatis ke aplikasi!
]);
```

---

## 🛠️ Fitur Unggulan

### 🔄 Auto-Withdraw Saldo VA
Jika opsi `'autoWithdraw' => true` diaktifkan, SDK akan memicu penarikan dana VA bank otomatis secara *real-time* menjadi saldo utama aplikasi (BukaOlshop) ketika fungsi `$zakki->checkbank()` dipanggil.

### 💡 Dual-Flow Pascabayar & Bebas Nominal
*   **Pascabayar (PLN/BPJS/PDAM):** Inquiry tagihan terlebih dahulu, lalu bayar dengan format tujuan `[ID_Pelanggan].[Nominal_Tagihan]` (Contoh: `122345678901.150000`).
*   **E-Wallet Bebas Nominal:** Kirim transfer E-Wallet nominal kustom dengan format tujuan `[No_HP].[Nominal]` (Contoh: `08123456789.25000`).

---

## 📑 Daftar Referensi Metode Lengkap

SDK PHP ini mendukung secara penuh seluruh **25 fungsi resmi** dengan nama dan perilaku yang konsisten dengan SDK versi Node.js (NPM) dan Python (PyPI):

### 1. Payment Gateway (QRIS Top Up)
*   `$zakki->topup($nominal)` — Membuat QRIS dinamis instan dengan nominal kode unik.
*   `$zakki->cektopup($idtopup)` — Cek status pembayaran QRIS.
*   `$zakki->cancel($id_transaksi, $all_pending)` — Batalkan transaksi pending (Daftar pending, batal satu, atau batal massal).

### 2. Transaksi H2H
*   `$zakki->listkode($jenis, $product_type)` — Katalog kode produk aktif, deskripsi, dan harga.
*   `$zakki->h2h($kode, $tujuan, $refID)` — Mengirim order transaksi H2H. Mendukung penulisan array asosiatif: `h2h(['kode' => 'S5', 'tujuan' => '08...', 'refID' => '...'])`.
*   `$zakki->cekh2h($id_trx)` — Cek detail status pengisian, SN, dan harga beli order H2H.
*   `$zakki->myh2h()` — Mengambil 20 riwayat pembelian H2H terupdate.

### 3. Perbankan & Transfer VA
*   `$zakki->checkbank()` — Cek saldo, VA member, mutasi, dan pemicu Auto-Withdraw.
*   `$zakki->checkname($number)` — Verifikasi nama asli pemilik VA Bank Zakki tujuan.
*   `$zakki->transfer($to, $amount)` — Transfer saldo antar Virtual Account member Bank Zakki. Mendukung penulisan array asosiatif: `transfer(['to' => '...', 'amount' => 5000])`.
*   `$zakki->tabung($jumlah)` — Menabung / deposit saldo dari aplikasi utama (BukaOlshop) ke Bank (butuh PIN).
*   `$zakki->tarik($jumlah)` — Menarik dana tabungan ke saldo aplikasi (butuh PIN).
*   `$zakki->checkmutasi($mutasi_type)` — Riwayat mutasi Tarik/Tabung (`tarik`, `tabung`, `all`).

### 4. Noktel Marketplace (OTP Virtual)
*   `$zakki->noktelStok()` — Cek stok nomor virtual yang ready.
*   `$zakki->noktelBuy($category)` — Membeli nomor virtual baru untuk OTP.
*   `$zakki->noktelGetOtp($account_id)` — Menarik kode OTP Telegram secara real-time.
*   `$zakki->noktelCancel($invoice_id)` — Membatalkan nomor yang pending OTP & auto-refund.
*   `$zakki->noktelHistory()` — Mengambil daftar riwayat pembelian Noktel.

### 5. Reward Komputasi & Game
*   `$zakki->cekmining()` — Cek status kesulitan global, block reward, dan miner aktif.
*   `$zakki->mymining()` — Riwayat koin mining SHA256 milik akun Anda.
*   `$zakki->cekgacha()` — Statistik poin, kemenangan, dan keuntungan gacha member.

### 6. Keamanan & Utilitas
*   `$zakki->whitelistip($ip)` — Whitelist IP server Anda untuk otorisasi API H2H.
*   `$zakki->delwhitelistip($ip)` — Hapus IP server dari whitelist.
*   `$zakki->leaderboard($limit, $period)` — Mengambil peringkat sultan topup teraktif.
*   `$zakki->status()` — Informasi beban CPU, metrik finansial, dan kesehatan sistem.

---

## 🛡️ Protokol Keamanan API

> [!WARNING]
> **Selalu jalankan SDK ini di sisi backend (Server-side)!**
> Jangan pernah mengekspos API Token dan PIN Anda di sisi klien publik demi mencegah potensi pencurian saldo.
