<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>2D Ödeme İşleme Sorgulama</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <?php include 'style.php'; ?>
</head>

<body>
    <?php include 'nav.php'; ?>

    <div class="container mt-4">
        <h2 class="">Tüm Yapılan İşlemleri Görme Entegrasyonu</h2>

        <!-- HTML Form for User Input -->
        <form method="post">
            <div class="form-group">

            <div class="form-group">
                <label for="merchant_key" class="mt-2">Fatura Numarası:</label>
                <input type="text" class="form-control" id="merchant_key" name="merchant_key"  value="$2y$10$tA5Q5IJJv8zpSh0sM.6bueB53HG2VmEKdWnj.HGewu9y5VUk7qvee" readonly>
            </div>
            </div>

            <button type="submit" name="process_payment" class="btns">Tüm Yapılan İşlemleri Göster</button>
        </form>
    </div>
    <?php
    if (isset($_POST['process_payment'])) {
        // Temel değişkenleri tanımla
    
        $baseUrl = "https://testapp.halkode.com.tr/ccpayment/api/alltransaction";
        include 'degisken.php';

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

        // Yanıtı alt alta göstermek için tabloyu kullan
        echo "<h3>Yanıt</h3>";
        $decodedResponse = json_decode($response, true);

        echo "<table class='table table-bordered'>
                    <thead>
                        <tr>
                            <th>Alan</th>
                            <th>Değer</th>
                        </tr>
                    </thead>
                    <tbody>";

        // Yanıt dizisini tek tek işleyip her birini tabloya alt alta ekle
        foreach ($decodedResponse as $key => $value) {
            // Eğer değer bir dizi ise, onu JSON formatında string'e çevir
            if (is_array($value)) {
                $value = json_encode($value, JSON_PRETTY_PRINT);
            }
            echo "<tr>
                        <td>" . htmlspecialchars($key) . "</td>
                        <td>" . htmlspecialchars($value) . "</td>
                      </tr>";
        }

        echo "</tbody></table>";
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