<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ödeme Sorgulama</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <?php include 'style.php'; ?>
</head>

<body>

<?php include 'nav.php'; ?>
    <div class="container mt-4">
        <h2 class="">Ödeme Sorgulama</h2>

        <!-- HTML Form for User Input -->
        <form method="post" class="">
            <label for="invoiceId" class="">Fatura ID:</label>
            <input type="text" id="invoiceId" name="invoice_id" value="" class="form-control" required><br><br>

            <button type="submit" name="process_payment" class="btns">Ödemeyi Test Et</button>
        </form>

        <?php
        if (isset($_POST['process_payment'])) {
            // Temel değişkenleri tanımla
          
            $baseUrl = "https://testapp.halkode.com.tr/ccpayment/api/checkstatus";
            include 'degisken.php';
            $invoice_id = $_POST['invoice_id'];

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
                "invoice_id" => $invoice_id,
                "merchant_key" => $merchantKey,
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
            echo "<h3>Yanıt</h3>";
            echo "<table class='table table-bordered'>";
            echo "<thead><tr><th>Parametre</th><th>Değer</th></tr></thead><tbody>";

            // Yanıtı JSON olarak işleyip tabloya ekle
            $responseData = json_decode($response, true);
            if ($responseData) {
                foreach ($responseData as $key => $value) {
                    echo "<tr><td>" . htmlspecialchars($key) . "</td><td>" . htmlspecialchars($value) . "</td></tr>";
                }
            } else {
                echo "<tr><td colspan='2'>Yanıt alınamadı.</td></tr>";
            }

            echo "</tbody></table>";
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
    </div>

    <?php include 'footer.php'; ?>

</body>

</html>