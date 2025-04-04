<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Taksit Gösterme Entegrasyonu</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <?php include 'style.php'; ?>
</head>

<body>
    <?php include 'nav.php'; ?>

    <div class="container mt-4">
        <h2 class="">Kart Taksit Gösterme Entegrasyonu</h2>

        <!-- HTML Form for User Input -->
        <form method="post">
            <div class="form-group">
                <label for="credit_card" class="mt-2">Kredi Kartı Numarası:</label>
                <input type="text" id="credit_card" placeholder="********************" name="credit_card" value=""
                    onkeypress="return (event.charCode !=8 && event.charCode ==0 || ( event.charCode == 46 || (event.charCode >= 48 && event.charCode <= 57)))"
                    maxlength="20" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="amount">Tutar:</label>
                <input type="text" id="amount" placeholder="****" name="amount" value=""
                    onkeypress="return (event.charCode !=8 && event.charCode ==0 || ( event.charCode == 46 || (event.charCode >= 48 && event.charCode <= 57)))"
                    maxlength="4" class="form-control" required>
            </div>
            <button type="submit" name="process_payment" class="btns">Ödemeyi Test Et</button>
        </form>

        <?php
        if (isset($_POST['process_payment'])) {
            // Temel değişkenleri tanımla
        
            $baseUrl = "https://testapp.halkode.com.tr/ccpayment/api/getpos";
            include 'degisken.php';

            $credit_card = $_POST['credit_card'];
            $amount = $_POST['amount'];
            $currencyCode = "TRY";

            // Token isteği
            $tokenResponse = getToken($app_id, $appSecret);
            $decodedTokenResponse = json_decode($tokenResponse, true);

            if ($decodedTokenResponse['status_code'] == 100) {
                $token = $decodedTokenResponse['data']['token'];
            } else {
                echo "<p><strong>Hata:</strong> Token alınamadı. Lütfen bilgilerinizi kontrol ediniz.</p>";
                return;
            }

           
            $data = array(
                "merchant_key" => $merchantKey,
                "credit_card" => $credit_card,
                "amount" => $amount,
                "currency_code" => $currencyCode,
            );

         
            echo "<h3>Gönderilen Veriler</h3>";
            echo "<pre>" . htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT)) . "</pre>";

          
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

          
            $response_data = json_decode($response, true);

            
            echo "<h3>API Yanıtı</h3>";
            echo "<table class='table table-bordered'>";
            echo "<thead><tr><th>Anahtar</th><th>Değer</th></tr></thead>";
            echo "<tbody>";

            foreach ($response_data as $key => $value) {
                if (is_array($value)) {
                    
                    echo "<tr><td>$key</td><td>";
                    echo "<table class='table table-bordered'>";
                    foreach ($value as $sub_key => $sub_value) {
                       
                        if (is_array($sub_value)) {
                           
                            echo "<tr><td>$sub_key</td><td>";
                            echo "<pre>" . print_r($sub_value, true) . "</pre></td></tr>";
                        } else {
                            echo "<tr><td>$sub_key</td><td>" . htmlspecialchars($sub_value) . "</td></tr>";
                        }
                    }
                    echo "</table></td></tr>";
                } else {
                   
                    echo "<tr><td>$key</td><td>" . htmlspecialchars($value) . "</td></tr>";
                }
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
