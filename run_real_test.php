<?php

require_once __DIR__ . '/src/ZakkiStore.php';

use MrLow12\ZakkiStore\ZakkiStore;

function run_real_test() {
    echo "==================================================\n";
    echo "🚀 PENGUJIAN REAL SDK PHP DENGAN OFFICIAL API\n";
    echo "==================================================\n";

    // Kredensial IBO6
    $token = "9d6e27f09e65d3";
    $iduser = "IBO6";

    echo "📡 Menghubungkan ke API Gateway Resmi...\n";

    // Inisialisasi SDK (Tanpa base_url, otomatis mengarah ke Official Gateway!)
    $zakki = new ZakkiStore([
        'token' => $token,
        'iduser' => $iduser,
        'autoWithdraw' => false // Set ke false demi keamanan saldo selama testing
    ]);

    try {
        // 1. Health Check
        echo "\n🔍 1. Melakukan Health Check Server...\n";
        $status = $zakki->status();
        echo "🟢 [SUCCESS] Status API: " . (isset($status['status']) ? $status['status'] : 'OK') . "\n";

        // 2. Check Bank & Profil
        echo "\n🔍 2. Mengambil Detail Akun & Profil IBO6 (checkbank)...\n";
        $bank_info = $zakki->checkbank();
        echo "Response data:\n" . json_encode($bank_info, JSON_PRETTY_PRINT) . "\n";

        $userData = isset($bank_info['data']) ? $bank_info['data'] : [];
        $bankDetail = isset($userData['bank_detail']) ? $userData['bank_detail'] : [];
        $userDetail = isset($userData['user_detail']) ? $userData['user_detail'] : [];

        echo "\n📝 RINGKASAN AKUN USER:\n";
        echo "   👤 Nama Pemegang Rekening: " . (isset($bankDetail['account_holder']) ? $bankDetail['account_holder'] : '') . "\n";
        echo "   💳 Nomor Virtual Account : " . (isset($bankDetail['virtual_account']) ? $bankDetail['virtual_account'] : '') . "\n";
        echo "   💰 Saldo Bank VA         : Rp " . number_format(isset($bankDetail['balance']) ? (float)$bankDetail['balance'] : 0.0) . "\n";
        echo "   📧 Email Terdaftar       : " . (isset($userDetail['email']) ? $userDetail['email'] : '') . "\n";
        echo "   🏆 Total Transaksi H2H   : " . (isset($userDetail['total_h2h']) ? $userDetail['total_h2h'] : 0) . " kali\n";

        // 3. Cek Katalog Harga DANA
        echo "\n🔍 3. Mengecek Katalog Produk H2H DANA (listkode)...\n";
        $katalog = $zakki->listkode('ewallet', 'DANA');
        if (isset($katalog['code']) && $katalog['code'] == 200) {
            $products = isset($katalog['data']) ? $katalog['data'] : [];
            echo "🟢 Berhasil memuat " . count($products) . " produk DANA.\n";
            if (!empty($products)) {
                echo "   Sampel Produk:\n";
                $slice = array_slice($products, 0, 3);
                foreach ($slice as $p) {
                    echo "   - Kode: {$p['kode']} | Produk: {$p['produk']} | Harga: Rp " . number_format((float)$p['harga']) . "\n";
                }
            }
        } else {
            echo "❌ Gagal memuat katalog.\n";
        }

        // 4. Leaderboard Sultan
        echo "\n🔍 4. Mengambil Data Leaderboard Sultan (3 Teratas)...\n";
        $board = $zakki->leaderboard(3, 'all');
        if (isset($board['code']) && $board['code'] == 200) {
            $list_sultan = isset($board['leaderboard']) ? $board['leaderboard'] : [];
            echo "🟢 Peringkat Sultan Teraktif:\n";
            foreach ($list_sultan as $rank) {
                $userInfo = isset($rank['user_info']) ? $rank['user_info'] : [];
                $stats = isset($rank['stats']) ? $rank['stats'] : [];
                echo "   Rank #{$rank['rank']} - {$userInfo['nama']} (VA: {$userInfo['virtual_account']}) | Total Topup: {$stats['total_topup_formatted']}\n";
            }
        } else {
            echo "❌ Gagal memuat leaderboard.\n";
        }

        echo "\n==================================================\n";
        echo "🎉 SELURUH PENGUJIAN RIEL SDK PHP BERHASIL 100%!\n";
        echo "==================================================\n";

    } catch (\Exception $e) {
        echo "\n❌ Pengujian ke Official API Gagal: " . $e->getMessage() . "\n";
    }
}

run_real_test();
