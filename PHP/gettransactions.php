<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>2D Ödeme İşleme Sorgulama</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <?php include 'style.php'; ?>
</head>

<body></body>
<?php include 'nav.php'; ?>

    <div class="container">
        <h2 class="mt-5">Tarih Görme Entegrasyonu</h2>

        <!-- HTML Form for User Input -->
        <form method="post">
            <label for="invoiceId">Fatura ID:</label>
            <input type="text" id="invoiceId" name="invoice_id" value="Abcd12456" required><br><br>

            <label for="merchantKey">MerchantKey:</label>
            <input type="text" id="merchantKey" name="merchant_key" value="$2y$10$avMpLZvIIEY4brcULaj4u.can9eg3gAnx5s3JGz5Yxd.9zka8YfaO" required><br><br>

            <label for="credit_card">Kredi Kartı Numarası:</label>
            <input type="text" id="credit_card" name="credit_card" value="540061" required><br><br>

            <label for="amount">Miktar:</label>
            <input type="number" id="amount" name="amount" value="100.00" step="0.01" required><br><br>

            <label for="currencyCode">KOD:</label>
            <input type="text" id="currencyCode" name="currency_code" value="TRY" required><br><br>

            <button type="submit" name="process_payment" class="btns">Ödemeyi Test Et</button>
        </form>
    </div>

    <?php
    if (isset($_POST['process_payment'])) {
        // Temel değişkenleri tanımla
        $baseUrl = "https://testapp.halkode.com.tr/ccpayment/api/getTransactions";
        include 'degisken.php';
        $invoice_id = $_POST['invoice_id'];
        $credit_card = $_POST['credit_card'];
        $amount = $_POST['amount'];
        $currency_code = $_POST['currency_code'];
        $date = date('Y-m-d');  // Güncel tarihi al

        // Token isteği
        $tokenResponse = getToken($app_id, $appSecret);
        $decodedTokenResponse = json_decode($tokenResponse, true);

        if ($decodedTokenResponse['status_code'] == 100) {
            $token = $decodedTokenResponse['data']['token'];
        } else {
            echo "<p><strong>Hata:</strong> Token alınamadı. Lütfen bilgilerinizi kontrol ediniz.</p>";
            return;
        }

        // Formdan verileri topla
        $data = array(
            "merchant_key" => $merchant_key,
            "date" => $date,
            "invoiceid" => $invoice_id,
            "credit_card" => $credit_card,
            "amount" => $amount,
            "currency_code" => $currency_code,
        );

        // Gönderilen verileri göster
        echo "<h3>Gönderilen Veriler</h3>";
        echo "<pre>" . htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT)) . "</pre>";

        // Ödeme isteğini göndermek için CURL
        $ch = curl_init($baseUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            "Authorization: Bearer $token"
        ));
        $jsonData = json_encode($data);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        // Yanıtı göster
        echo "<h3>Yanıt</h3><pre>" . htmlspecialchars($response) . "</pre>";

        // Güncel tarihi göster
        echo "<h3>Güncel Tarih</h3><p>$date</p>";  // Güncel tarihi burada gösteriyoruz
    }

    function generateHashKey($total, $installment, $currency_code, $merchant_key, $invoice_id, $app_secret)
    {
        $data = $total . '|' . $installment . '|' . $currency_code . '|' . $merchant_key . '|' . $invoice_id;
        $iv = substr(sha1(mt_rand()), 0, 16);
        $password = sha1($app_secret);
        $salt = substr(sha1(mt_rand()), 0, 4);
        $saltWithPassword = hash('sha256', $password . $salt);
        $encrypted = openssl_encrypt("$data", 'aes-256-cbc', "$saltWithPassword", 0, $iv);
        $msg_encrypted_bundle = "$iv:$salt:$encrypted";
        $msg_encrypted_bundle = str_replace('/', '__', $msg_encrypted_bundle);
        return $msg_encrypted_bundle;
    }

    function getToken($app_id, $app_secret)
    {
        $baseUrl = "https://testapp.halkode.com.tr/ccpayment/api/token";
        $data = array('app_id' => $app_id, 'app_secret' => $app_secret);
        $jsonData = json_encode($data);
        $ch = curl_init($baseUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
    ?>

    <?php include 'footer.php'; ?>

</body>

</html>