<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>İade Entegrasyonu</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <?php include 'style.php'; ?>
</head>

<body>
<?php include 'nav.php'; ?>

    <div class="container mt-4">
        <h2 class="">İade Entegrasyonu</h2>

        <!-- HTML Form for User Input -->
        <form method="post">
            <label for="invoiceId" >Fatura ID:</label>
            <input type="text" id="invoiceId" name="invoice_id" value="" class="form-control" required><br>

            <label for="amount" >Tutar:</label>
            <input type="text" onkeypress="return (event.charCode != 8 && event.charCode == 0 || (event.charCode == 46 || (event.charCode >= 48 && event.charCode <= 57)))"
            class="form-control" required  id="amount" name="amount" value="" class="form-control" required><br>

            <button type="submit" name="process_payment" class="btns">Ödemeyi Test Et</button>
        </form>

        <?php
        if (isset($_POST['process_payment'])) {
            // Temel değişkenleri tanımla
            $baseUrl = "https://testapp.halkode.com.tr/ccpayment/api/refund";
            include 'degisken.php';
            $invoice_id = $_POST['invoice_id'];
            $amount = $_POST['amount'];

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
                "amount" => $amount,
            );

            // Gönderilen verileri tablodaki formatta göster
            echo "<h3>Gönderilen Veriler</h3>";
            echo "<table class='table table-bordered'>
                    <thead>
                        <tr>
                            <th>Fatura ID</th>
                            <th>İade Tutarı</th>
                            <th>Merchant Key</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>" . htmlspecialchars($invoice_id) . "</td>
                            <td>" . htmlspecialchars($amount) . " TL</td>
                            <td>" . htmlspecialchars($merchantKey) . "</td>
                        </tr>
                    </tbody>
                  </table>";

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

            // Yanıtı tabloya yerleştir
            echo "<h3>Yanıt</h3>";
            $decodedResponse = json_decode($response, true);

            echo "<table class='table table-bordered'>
                    <thead>
                        <tr>
                            <th>Durum Kodu</th>
                            <th>Durum Açıklaması</th>
                            <th>Sipariş Numarası</th>
                            <th>Fatura ID</th>
                            <th>Referans No</th>
                            <th>Referans Numarası</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>" . htmlspecialchars($decodedResponse['status_code']) . "</td>
                            <td>" . htmlspecialchars($decodedResponse['status_description']) . "</td>
                            <td>" . htmlspecialchars($decodedResponse['order_no']) . "</td>
                            <td>" . htmlspecialchars($decodedResponse['invoice_id']) . "</td>
                            <td>" . htmlspecialchars($decodedResponse['ref_no']) . "</td>
                            <td>" . htmlspecialchars($decodedResponse['ref_number']) . "</td>
                        </tr>
                    </tbody>
                  </table>";
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
    </div>

    <?php include 'footer.php'; ?>
</body>

</html>