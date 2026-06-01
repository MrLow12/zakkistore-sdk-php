# 🐘 Zakkistore SDK for PHP

**Official B2B Client Library for Zakki Store API Gateway**

Pustaka PHP resmi untuk memudahkan integrasi layanan Host-to-Host (H2H) prabayar/pascabayar, payment gateway QRIS otomatis, perbankan Virtual Account (VA), Noktel OTP virtual, mining reward, dan gacha koin Zakki Store ke dalam proyek PHP Anda (Laravel, Symfony, CodeIgniter, Yii, native PHP, bot WhatsApp/Telegram, dll).

---

## 🚀 Instalasi & Inisialisasi

Instal pustaka dari Composer:

```bash
composer require zakkistore/zakkistore-sdk
```

### Inisialisasi Klien

```php
require_once 'vendor/autoload.php';

use ZakkiStore\ZakkiStore;

// Inisialisasi klien SDK (Bisa tanpa base_url karena official otomatis ter-set!)
$zakki = new ZakkiStore([
    'baseUrl' => 'https://qris.zakki.store',
    'token' => 'API_TOKEN_ANDA',
    'iduser' => 'IBO99',
    'email' => 'member@gmail.com',
    'pin' => '123456',              // PIN transaksi (Wajib untuk tabung & tarik)
    'autoWithdraw' => true          // Aktifkan penarikan saldo VA otomatis ke aplikasi!
]);
```

---

## 🛠️ Fitur Unggulan

### 🔄 Auto-Withdraw Saldo VA
Jika opsi `'autoWithdraw' => true` diaktifkan, SDK akan memicu penarikan dana VA bank otomatis secara *real-time* menjadi saldo utama aplikasi zakki store ketika fungsi `$zakki->checkbank()` dipanggil.

### 💡 Dual-Flow Pascabayar & Bebas Nominal
*   **Pascabayar (PLN/BPJS/PDAM):** Inquiry tagihan terlebih dahulu, lalu bayar dengan format tujuan `[ID_Pelanggan].[Nominal_Tagihan]` (Contoh: `122345678901.150000`).
*   **E-Wallet Bebas Nominal:** Kirim transfer E-Wallet nominal kustom dengan format tujuan `[No_HP].[Nominal]` (Contoh: `08123456789.25000`).

---

## 📑 Daftar Referensi Metode Lengkap & Struktur Pengelompokan (36 Fungsi Resmi)

Seluruh fungsi yang didukung oleh SDK ini dikelompokkan secara rapi ke dalam 7 kategori layanan utama demi mempermudah pemahaman dan integrasi:

### 1. ⚡ Layanan Payment Gateway (QRIS Topup) — [4 Fungsi]
*   **`$zakki->topup($nominal)`** — Membuat tiket pembayaran QRIS dinamis instan dengan nominal kode unik.
*   **`$zakki->cektopup($idtopup)`** — Mengecek status pembayaran tiket QRIS tertentu secara real-time.
*   **`$zakki->mytopup()`** — Mengambil seluruh riwayat transaksi topup QRIS akun Anda.
*   **`$zakki->cancel($id_transaksi, $all_pending)`** — Membatalkan satu atau seluruh tiket topup pending.

### 2. 🏪 Layanan Transaksi Host-to-Host (H2H) — [4 Fungsi]
*   **`$zakki->listkode($jenis, $product_type)`** — Mengambil katalog produk prabayar/pascabayar aktif beserta daftar harga beli.
*   **`$zakki->h2h($kode, $tujuan, $refID)`** — Mengirimkan order transaksi H2H (pulsa, paket data, PLN kustom, dll).
*   **`$zakki->cekh2h($id_trx)`** — Mengecek status transaksi, Serial Number (SN), dan harga beli riil dari order H2H.
*   **`$zakki->myh2h()`** — Mengambil 20 riwayat transaksi H2H terupdate milik akun Anda.

### 3. 🏦 Layanan Perbankan & Transfer Saldo VA — [8 Fungsi]
*   **`$zakki->checkbank()`** — Memeriksa detail Virtual Account (VA), saldo bank VA, serta memicu Auto-Withdraw jika diaktifkan.
*   **`$zakki->checkname($number)`** — Memverifikasi nama asli pemilik rekening Virtual Account tujuan sebelum melakukan transfer.
*   **`$zakki->transfer($to, $amount)`** — Mengirimkan saldo antar-VA member secara instan dan bebas biaya admin.
*   **`$zakki->tabung($jumlah)`** — Menyetorkan saldo aktif aplikasi ke rekening bank Virtual Account terhubung Anda.
*   **`$zakki->tarik($jumlah)`** — Menarik dana dari bank Virtual Account ke saldo aktif aplikasi Zakki Store Anda.
*   **`$zakki->checkmutasi($mutasi_type)`** — Melihat riwayat mutasi tabung/tarik saldo bank VA (`all`, `tarik`, `tabung`).
*   **`$zakki->checktransfer($idtransfer)`** — Mengecek status pengiriman dana transfer tertentu secara detail.
*   **`$zakki->mytransfer($type)`** — Mengambil riwayat pengiriman dan penerimaan transfer saldo (`all`, `kirim`, `terima`).

### 4. 📱 Layanan Noktel Marketplace (OTP Virtual) — [5 Fungsi]
*   **`$zakki->noktelStok()`** — Memeriksa ketersediaan stok nomor virtual aktif per kategori layanan/aplikasi.
*   **`$zakki->noktelBuy($category)`** — Membeli nomor virtual baru untuk penerimaan kode verifikasi/OTP.
*   **`$zakki->noktelGetOtp($account_id)`** — Mengambil kode verifikasi/OTP yang masuk ke nomor virtual secara real-time.
*   **`$zakki->noktelCancel($invoice_id)`** — Membatalkan order nomor virtual yang pending OTP dan memicu auto-refund saldo.
*   **`$zakki->noktelHistory()`** — Mengambil daftar riwayat lengkap pemesanan nomor virtual.

### 5. ⛏️ Layanan Reward Komputasi SHA-256 (Mining) & Game — [5 Fungsi]
*   **`$zakki->miningStart()`** — Meminta challenge penambangan SHA-256 serta target kesulitan (difficulty) dari server.
*   **`$zakki->miningSubmit($nonce, $signature)`** — Mengirimkan hasil kerja hashing SHA-256 (Proof-of-Work) untuk mendapatkan koin.
*   **`$zakki->cekmining($idmining)`** — Mengecek status audit dan persetujuan dari blok mining yang telah Anda selesaikan.
*   **`$zakki->mymining()`** — Melihat riwayat penambangan koin dan total reward hashing akun Anda.
*   **`$zakki->cekgacha()`** — Mengecek jumlah tiket gacha, riwayat kemenangan, dan detail koin keberuntungan Anda.

### 6. 🔒 Layanan Keamanan IP & Utilitas — [6 Fungsi]
*   **`$zakki->whitelistip($ip)`** — Mendaftarkan IP server/host Anda agar diizinkan melakukan transaksi H2H via API (Maksimal 3 IP).
*   **`$zakki->delwhitelistip($ip)`** — Menghapus alamat IP terdaftar dari whitelist API.
*   **`$zakki->cekmyip()`** — Mendeteksi alamat IP publik host/server Anda saat ini yang terbaca oleh sistem.
*   **`$zakki->cekip($ip)`** — Mengecek detail status IP whitelisting tertentu.
*   **`$zakki->leaderboard($limit, $period)`** — Melihat daftar Sultan topup teraktif secara global.
*   **`$zakki->status()`** — Memeriksa beban CPU server, statistik finansial global, dan kesehatan sistem.

### 7. 🔗 Layanan Webhook Callback & Notifikasi Bot — [4 Fungsi]
*   **`$zakki->setcallback($site)`** — Memasang URL callback real-time untuk menerima laporan status transaksi H2H.
*   **`$zakki->delcallback()`** — Menghapus URL callback yang terpasang di sistem.
*   **`$zakki->setnotifbot($telegramId)`** — Memasang ID Telegram Anda untuk menerima notifikasi otomatis transaksi sukses/gagal.
*   **`$zakki->delnotifbot()`** — Menonaktifkan bot notifikasi Telegram.


## 🛡️ Protokol Keamanan API

> [!WARNING]
> **Selalu jalankan SDK ini di sisi backend (Server-side)!**
> Jangan pernah mengekspos API Token dan PIN Anda di sisi klien publik demi mencegah potensi pencurian saldo.
