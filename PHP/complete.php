<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Ödeme Tamamlama</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <?php include 'style.php'; ?>
</head>

<body>
<?php include 'nav.php'; ?>

    <div class="container">
        <h2 class="mt-5">Ödeme Tamamlama</h2>

        <!-- HTML Form for User Input -->
        <form method="post">
            <label for="merchantKey">MerchantKey:</label>
            <input type="text" id="merchantKey" name="merchantKey" value="$2y$10$avMpLZvIIEY4brcULaj4u.can9eg3gAnx5s3JGz5Yxd.9zka8YfaO" required><br><br>

            <label for="invoice_id">Fatura ID:</label>
            <input type="text" id="invoice_id" name="invoice_id" value="s92711df41131" required><br><br>

            <label for="order_id">İşlem Numarası:</label>
            <input type="text" id="order_id" name="order_id" value="VP17123239825285705" required><br><br>

            <button type="submit" name="process_payment" class="btns">Ödemeyi Test Et</button>
        </form>
    </div>

    <?php
    if (isset($_POST['process_payment'])) {
        // Temel değişkenleri tanımla
        $baseUrl = "https://testapp.halkode.com.tr/ccpayment/api/complete";
        include 'degisken.php';
        $order_id = $_POST['order_id']; // Order ID formdan alınacak
        $invoice_id = $_POST['invoice_id']; // Fatura ID formdan alınacak
        $status = "complete";

        // Token isteği
        $tokenResponse = getToken($app_id, $appSecret);
        $decodedTokenResponse = json_decode($tokenResponse, true);

        // Token yanıtını kontrol et
        if ($decodedTokenResponse === null || $decodedTokenResponse['status_code'] != 100) {
            echo "<p><strong>Hata:</strong> Token alınamadı. Lütfen bilgilerinizi kontrol ediniz.</p>";
            return;
        }
        $token = $decodedTokenResponse['data']['token'];

        // Formdan verileri topla (URL-encoded)
        $data = array(
            "invoice_id" => $invoice_id,   // Dinamik Fatura ID
            "merchant_key" => $merchantKey,
            "order_id" => $order_id,   // Dinamik İşlem Numarası
            "status" => $status,
        );

        // Gönderilen verileri göster
        echo "<h3>Gönderilen Veriler</h3>";
        echo "<pre>" . htmlspecialchars(http_build_query($data)) . "</pre>";

        // Ödeme isteğini göndermek için CURL (URL-encoded format)
        $ch = curl_init($baseUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer $token"
        ));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));  // URL-encoded veri
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);

        // CURL hatası kontrolü
        if (curl_errno($ch)) {
            echo 'CURL Hatası: ' . curl_error($ch);
            curl_close($ch);
            return;
        }
        curl_close($ch);

        // Yanıtı yazdırarak kontrol et
        echo "<h3>API Yanıtı</h3><pre>" . htmlspecialchars($response) . "</pre>";

        // Yanıtı kontrol et ve işlem tamamlandığında mesaj göster
        parse_str($response, $responseData);  // URL-encoded yanıtı çöz
        if (isset($responseData['status_code']) && $responseData['status_code'] == 100) {
            echo "<h3>İşlem Tamamlandı</h3><p>Ödeme işleminiz başarıyla tamamlanmıştır.</p>";
        } else {
            echo "<h3>Hata</h3><p>İşlem sırasında bir hata oluştu. Lütfen tekrar deneyin.</p>";
        }
    }

    function getToken($app_id, $app_secret)
    {
        $baseUrl = "https://testapp.halkode.com.tr/ccpayment/api/token";  // URL-encoded endpoint
        $data = array('app_id' => $app_id, 'app_secret' => $app_secret);
        $ch = curl_init($baseUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));  // URL-encoded veri
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        $response = curl_exec($ch);

        // CURL hatası kontrolü
        if (curl_errno($ch)) {
            echo 'CURL Hatası: ' . curl_error($ch);
            curl_close($ch);
            return null;
        }
        curl_close($ch);
        return $response;
    }
    ?>

    <?php include 'footer.php'; ?>

</body>

</html>