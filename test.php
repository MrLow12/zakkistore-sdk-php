<?php

require_once __DIR__ . '/src/ZakkiStore.php';

use MrLow12\ZakkiStore\ZakkiStore;

function run_tests() {
    echo "🧪 Menjalankan uji coba inisialisasi SDK PHP...\n";

    try {
        // Inisialisasi mock client
        $zakki = new ZakkiStore([
            'token' => 'mock_token_123',
            'iduser' => 'mock_user_IBO99',
            'pin' => '123456',
            'autoWithdraw' => false
        ]);

        echo "✅ Inisialisasi ZakkiStore Client berhasil!\n";

        // Cek eksistensi metode utama
        $methods = [
            'topup', 'cektopup', 'cancel',
            'listkode', 'h2h', 'cekh2h', 'myh2h',
            'checkbank', 'checkname', 'transfer', 'tabung', 'tarik', 'checkmutasi',
            'noktelStok', 'noktelBuy', 'noktelGetOtp', 'noktelCancel', 'noktelHistory',
            'cekmining', 'mymining', 'cekgacha',
            'whitelistip', 'delwhitelistip', 'leaderboard', 'status'
        ];

        echo "\n🔍 Memverifikasi eksistensi 25 Native Methods...\n";
        foreach ($methods as $method) {
            if (method_exists($zakki, $method)) {
                echo "  [OK] Metode '{$method}' terdeteksi dan aktif.\n";
            } else {
                echo "  [FAIL] Metode '{$method}' TIDAK TERDETEKSI!\n";
                throw new \Exception("Metode '{$method}' tidak lengkap!");
            }
        }

        echo "\n🏆 Uji Coba Kepatuhan Metode & Struktur PHP Sukses 100%!\n";
    } catch (\Exception $e) {
        echo "❌ Uji coba gagal: " . $e->getMessage() . "\n";
        exit(1);
    }
}

run_tests();
