<?php

namespace ZakkiStore;

class ZakkiStore {
    private $baseUrl;
    private $token;
    private $iduser;
    private $email;
    private $pin;
    private $isAutoWithdraw;

    /**
     * Inisialisasi ZakkiStore PHP SDK Klien.
     * 
     * @param string|array $config Konfigurasi atau Token API member.
     * @param string|null $token Token API member (jika $config adalah base_url).
     * @param string|null $iduser ID User member (opsional).
     * @param string|null $email Email member (opsional).
     * @param string|number|null $pin PIN transaksi member (opsional).
     * @param bool $autoWithdraw Aktifkan penarikan saldo VA otomatis ke aplikasi (default: false).
     */
    public function __construct($config, $token = null, $iduser = null, $email = null, $pin = null, $autoWithdraw = false) {
        // Deteksi pintar jika user menaruh array konfigurasi (JS-style object)
        if (is_array($config)) {
            $baseUrl = isset($config['baseUrl']) ? $config['baseUrl'] : (isset($config['base_url']) ? $config['base_url'] : 'https://qris.zakki.store');
            $token = isset($config['token']) ? $config['token'] : null;
            $iduser = isset($config['iduser']) ? $config['iduser'] : (isset($config['id_user']) ? $config['id_user'] : null);
            $email = isset($config['email']) ? $config['email'] : null;
            $pin = isset($config['pin']) ? $config['pin'] : null;
            $autoWithdraw = isset($config['autoWithdraw']) ? $config['autoWithdraw'] : (isset($config['auto_withdraw']) ? $config['auto_withdraw'] : false);
        } else {
            // Jika dipanggil secara posisional
            if (is_string($config) && (strpos($config, 'http://') === 0 || strpos($config, 'https://') === 0)) {
                $baseUrl = $config;
            } else {
                // Parameter pertama adalah token karena base_url default ke official
                $token = $config;
                $baseUrl = 'https://qris.zakki.store';
            }
        }

        if (empty($token)) {
            throw new \InvalidArgumentException('token wajib disertakan dalam konfigurasi SDK.');
        }
        if (empty($baseUrl)) {
            throw new \InvalidArgumentException('base_url wajib disertakan dalam konfigurasi SDK.');
        }

        $this->baseUrl = rtrim($baseUrl, '/');
        $this->token = $token;
        $this->iduser = $iduser;
        $this->email = $email;
        $this->pin = $pin;
        $this->isAutoWithdraw = (bool)$autoWithdraw;
    }

    /**
     * Mengatur status auto-withdraw.
     * 
     * @param bool $status
     */
    public function enableAutoWithdraw($status) {
        $this->isAutoWithdraw = (bool)$status;
    }

    /**
     * Request Helper internal menggunakan cURL.
     * 
     * @private
     */
    private function _request($endpoint, $method = 'GET', $data = null) {
        $url = $this->baseUrl . $endpoint;
        $ch = curl_init();

        $headers = [
            'Content-Type: application/json'
        ];

        if (strtoupper($method) === 'GET') {
            if (!empty($data)) {
                $url .= '?' . http_build_query($data);
            }
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        } else {
            curl_setopt($ch, CURLOPT_POST, true);
            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            throw new \RuntimeException("[ZakkiStore SDK Error] Koneksi Gagal: " . $error_msg);
        }

        curl_close($ch);

        $resJson = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("[ZakkiStore SDK Error] Gagal membaca response JSON dari server.");
        }

        if ($httpCode >= 400) {
            $err_msg = isset($resJson['message']) ? $resJson['message'] : "HTTP Error! Status: " . $httpCode;
            if ($httpCode === 403 || stripos($err_msg, 'ip') !== false) {
                $err_msg .= "\n⚠️ [IP BLOCKED / UNREGISTERED] IP Anda diblokir atau belum terdaftar di whitelist API. Silakan hubungi developer via WhatsApp (https://wa.me/6283844082339) atau Telegram (https://t.me/zakki_store) untuk mendapatkan bantuan.";
            }
            throw new \RuntimeException("[ZakkiStore SDK Error] " . $err_msg);
        }

        return $resJson;
    }

    // ==========================================================
    // --- 1. PAYMENT GATEWAY (QRIS TOPUP) ---
    // ==========================================================

    public function topup($nominal) {
        return $this->_request('/topup', 'POST', [
            'token' => $this->token,
            'nominal' => (int)$nominal
        ]);
    }

    public function cektopup($idtopup) {
        return $this->_request('/cektopup', 'GET', [
            'idtopup' => $idtopup
        ]);
    }

    public function cancel($id_transaksi = null, $all_pending = false) {
        // Fleksibilitas jika argumen pertama adalah boolean (true/false)
        if (is_bool($id_transaksi)) {
            $all_pending = $id_transaksi;
            $id_transaksi = null;
        }

        $payload = ['token' => $this->token];
        if (!empty($id_transaksi)) {
            $payload['id_transaksi'] = $id_transaksi;
        }
        if ($all_pending) {
            $payload['all'] = true;
        }

        return $this->_request('/cancel', 'POST', $payload);
    }

    // ==========================================================
    // --- 2. TRANSAKSI H2H (HOST-TO-HOST) ---
    // ==========================================================

    public function listkode($jenis = null, $product_type = null) {
        $payload = [];
        if (!empty($jenis)) {
            $payload['jenis'] = $jenis;
        }
        if (!empty($product_type)) {
            $payload['type'] = $product_type;
        }
        return $this->_request('/listkode', 'GET', $payload);
    }

    public function h2h($kode, $tujuan = null, $refID = null) {
        // Dukungan array untuk JS destructuring style
        if (is_array($kode)) {
            $payload = $kode;
            $kode = isset($payload['kode']) ? $payload['kode'] : null;
            $tujuan = isset($payload['tujuan']) ? $payload['tujuan'] : null;
            $refID = isset($payload['refID']) ? $payload['refID'] : (isset($payload['ref_id']) ? $payload['ref_id'] : null);
        }

        return $this->_request('/h2h', 'POST', [
            'token' => $this->token,
            'kode' => $kode,
            'tujuan' => $tujuan,
            'refID' => $refID
        ]);
    }

    public function cekh2h($id_trx) {
        return $this->_request('/cekh2h', 'GET', [
            'id' => $id_trx
        ]);
    }

    public function myh2h() {
        return $this->_request('/myh2h', 'GET', [
            'token' => $this->token
        ]);
    }

    // ==========================================================
    // --- 3. PERBANKAN & TRANSFER SALDO ---
    // ==========================================================

    public function checkbank() {
        $payload = ['token' => $this->token];
        if (!empty($this->iduser)) {
            $payload['iduser'] = $this->iduser;
        } elseif (!empty($this->email)) {
            $payload['email'] = $this->email;
        }

        $bankRes = $this->_request('/checkbank', 'GET', $payload);

        // Alur Auto-Withdraw otomatis seperti Node.js & Python
        if ($this->isAutoWithdraw && isset($bankRes['data']['bank_detail'])) {
            $bankDetail = $bankRes['data']['bank_detail'];
            $balance = isset($bankDetail['balance']) ? (float)$bankDetail['balance'] : 0.0;

            if ($balance > 0) {
                try {
                    $withdrawRes = $this->tarik((int)$balance);

                    // Ambil kembali informasi bank terbaru setelah ditarik
                    $bankRes = $this->_request('/checkbank', 'GET', $payload);

                    // Sematkan flag sukses auto-withdraw ke dalam respon data
                    $bankRes['auto_withdraw_executed'] = true;
                    $bankRes['auto_withdraw_amount'] = (int)$balance;
                    $bankRes['auto_withdraw_message'] = isset($withdrawRes['message']) ? $withdrawRes['message'] : 'Auto-withdraw berhasil dijalankan.';
                } catch (\Exception $err) {
                    // Sematkan flag gagal ke dalam respon data
                    $bankRes['auto_withdraw_executed'] = false;
                    $bankRes['auto_withdraw_error'] = $err->getMessage();
                }
            }
        }

        return $bankRes;
    }

    public function checkname($number) {
        return $this->_request('/checkname', 'GET', [
            'number' => trim($number)
        ]);
    }

    public function transfer($to, $amount = null) {
        // Dukungan array untuk JS destructuring style
        if (is_array($to)) {
            $payload = $to;
            $to = isset($payload['to']) ? $payload['to'] : null;
            $amount = isset($payload['amount']) ? $payload['amount'] : null;
        }

        return $this->_request('/transfer', 'POST', [
            'token' => $this->token,
            'to' => $to,
            'amount' => (int)$amount
        ]);
    }

    public function tabung($jumlah) {
        if (empty($this->pin)) {
            throw new \RuntimeException('[ZakkiStore SDK Error] PIN transaksi diperlukan untuk melakukan transaksi tabung.');
        }

        $payload = [
            'token' => $this->token,
            'jumlah' => (int)$jumlah,
            'pin' => $this->pin
        ];

        if (!empty($this->iduser)) {
            $payload['iduser'] = $this->iduser;
        }
        if (!empty($this->email)) {
            $payload['email'] = $this->email;
        }

        return $this->_request('/tabung', 'POST', $payload);
    }

    public function tarik($jumlah) {
        if (empty($this->pin)) {
            throw new \RuntimeException('[ZakkiStore SDK Error] PIN transaksi diperlukan untuk melakukan transaksi tarik.');
        }

        $payload = [
            'token' => $this->token,
            'jumlah' => (int)$jumlah,
            'pin' => $this->pin
        ];

        if (!empty($this->iduser)) {
            $payload['iduser'] = $this->iduser;
        }
        if (!empty($this->email)) {
            $payload['email'] = $this->email;
        }

        return $this->_request('/tarik', 'POST', $payload);
    }

    public function checkmutasi($mutasi_type = 'all') {
        $payload = [
            'token' => $this->token,
            'type' => $mutasi_type
        ];

        if (!empty($this->iduser)) {
            $payload['iduser'] = $this->iduser;
        }
        if (!empty($this->email)) {
            $payload['email'] = $this->email;
        }

        return $this->_request('/checkmutasi', 'GET', $payload);
    }

    // ==========================================================
    // --- 4. NOKTEL MARKETPLACE (OTP VIRTUAL) ---
    // ==========================================================

    public function noktelStok() {
        return $this->_request('/noktel/stok', 'GET', [
            'token' => $this->token
        ]);
    }

    public function noktelBuy($category) {
        return $this->_request('/noktel/buy', 'POST', [
            'token' => $this->token,
            'category' => trim($category)
        ]);
    }

    public function noktelGetOtp($account_id) {
        return $this->_request('/noktel/getotp', 'GET', [
            'token' => $this->token,
            'account_id' => trim($account_id)
        ]);
    }

    public function noktelCancel($invoice_id) {
        return $this->_request('/noktel/cancel', 'POST', [
            'token' => $this->token,
            'invoice_id' => trim($invoice_id)
        ]);
    }

    public function noktelHistory() {
        return $this->_request('/noktel/history', 'GET', [
            'token' => $this->token
        ]);
    }

    // ==========================================================
    // --- 5. REWARD KOMPUTASI & GAME ---
    // ==========================================================

    public function cekmining($idmining) {
        if (empty($idmining)) {
            throw new \InvalidArgumentException('Parameter idmining wajib diisi.');
        }
        return $this->_request('/cekmining', 'GET', [
            'idmining' => trim($idmining)
        ]);
    }

    public function mymining() {
        return $this->_request('/mymining', 'GET', [
            'token' => $this->token
        ]);
    }

    public function miningStart() {
        return $this->_request('/mining/start', 'GET', [
            'token' => $this->token
        ]);
    }

    public function miningSubmit($nonce, $signature) {
        if ($nonce === null || $nonce === '') {
            throw new \InvalidArgumentException('Parameter nonce wajib disertakan.');
        }
        if (empty($signature)) {
            throw new \InvalidArgumentException('Parameter signature wajib disertakan.');
        }
        return $this->_request('/mining/submit', 'POST', [
            'token' => $this->token,
            'nonce' => $nonce,
            'signature' => $signature
        ]);
    }

    public function cekgacha() {
        return $this->_request('/cekgacha', 'GET', [
            'token' => $this->token
        ]);
    }

    // ==========================================================
    // --- 6. UTILITY & SECURITY ---
    // ==========================================================

    public function whitelistip($ip) {
        return $this->_request('/whitelistip', 'POST', [
            'token' => $this->token,
            'ip' => trim($ip)
        ]);
    }

    public function delwhitelistip($ip) {
        return $this->_request('/delwhitelistip', 'POST', [
            'token' => $this->token,
            'ip' => trim($ip)
        ]);
    }

    public function leaderboard($limit = 10, $period = 'all') {
        return $this->_request('/leaderboard', 'GET', [
            'limit' => (int)$limit,
            'period' => trim($period)
        ]);
    }

    public function status() {
        return $this->_request('/status', 'GET');
    }

    // ==========================================================
    // --- 7. METODE INTEGRASI BARU ---
    // ==========================================================

    public function setcallback($site) {
        return $this->_request('/setcallback', 'GET', [
            'token' => $this->token,
            'site' => trim($site)
        ]);
    }

    public function delcallback() {
        return $this->_request('/delcallback', 'GET', [
            'token' => $this->token
        ]);
    }

    public function setnotifbot($telegramId) {
        return $this->_request('/setnotifbot', 'GET', [
            'token' => $this->token,
            'id' => trim($telegramId)
        ]);
    }

    public function delnotifbot() {
        return $this->_request('/delnotifbot', 'GET', [
            'token' => $this->token
        ]);
    }

    public function checktransfer($idtransfer) {
        return $this->_request('/checktransfer', 'GET', [
            'idtransfer' => trim($idtransfer)
        ]);
    }

    public function mytransfer($transfer_type = 'all') {
        return $this->_request('/mytransfer', 'GET', [
            'token' => $this->token,
            'type' => trim($transfer_type)
        ]);
    }

    public function mytopup() {
        return $this->_request('/mytopup', 'GET', [
            'token' => $this->token
        ]);
    }

    public function cekmyip() {
        return $this->_request('/cekmyip', 'GET');
    }

    public function cekip($ip) {
        return $this->_request('/cekip', 'GET', [
            'ip' => trim($ip)
        ]);
    }
}
