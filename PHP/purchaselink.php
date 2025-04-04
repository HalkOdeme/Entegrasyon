<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link ile Ödeme Entegrasyonu</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <?php include 'style.php'; ?>
</head>

<body>

    <?php include 'nav.php'; ?>
    <div class="container mt-4">
        <h2 class="">Link ile Ödeme Entegrasyonu</h2>

        <?php 
            // Fatura ID'si otomatik olarak oluşturuluyor
            $invoice_id = date('Ymd') . '-' . rand(1000, 9999);
            ?>

        <form method="post">
            <div class="form-row">
            <div class="form-group col-md-6 mt-2">
                <label for="invoice_id" >Fatura Numarası:</label>
                <input type="text" class="form-control" id="invoice_id" name="invoice_id" value="<?php echo $invoice_id; ?>" readonly>
            </div>
                <div class="form-group col-md-6 mt-2">
                    <label for="name">Kart Sahibi Adı:</label>
                    <input type="text"
                        onkeypress='return ((event.charCode >= 65 && event.charCode <= 90) || (event.charCode >= 97 && event.charCode <= 122) || (event.charCode == 32))'
                        id="name" name="name" class="form-control" required />
                </div>
                <div class="form-group col-md-6 mt-2">
                    <label for="surname">Kart Sahibi Soyadı:</label>
                    <input type="text" onkeydown="return /[a-z]/i.test(event.key)" id="surname" name="surname"
                        class="form-control" required />
                </div>

                <div class="form-group col-md-6 mt-2">
                    <label for="total">Tutar:</label>
                    <input type="text"
                        onkeypress="return (event.charCode != 8 && event.charCode == 0 || (event.charCode == 46 || (event.charCode >= 48 && event.charCode <= 57)))"
                        id="total" name="total" class="form-control" required />
                </div>
            </div>

            <button type="submit" name="process_payment" class="btns">Ödemeyi Test Et</button>
        </form>


        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {

            $apiUrl = "https://testapp.halkode.com.tr/ccpayment/purchase/link";
            include 'degisken.php';
            $invoice_id = $_POST['invoice_id'];
            $currency_code = "TRY";
            $name = $_POST['name'];
            $surname = $_POST['surname'];
            $total = $_POST['total'];

            $requestData = [
                "merchant_key" => $merchantKey,
                "currency_code" => $currency_code,
                "invoice" => "{\"invoice_id\":\"$invoice_id\",\"invoice_description\":\"Testdescription\",\"total\":$total,\"return_url\":\"https://google.com.tr\",\"cancel_url\":\"https://github.com.tr\",\"items\":[{\"name\":\"Item1\",\"price\":$total,\"quantity\":1,\"description\":\"Test\"}]}",
                "name" => $name,
                "surname" => $surname
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($requestData));

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                $error_message = 'cURL Error: ' . curl_error($ch);
                curl_close($ch);
                exit($error_message);
            }

            curl_close($ch);

            $responseData = json_decode($response, true);
            ?>

       

            <h2>Gönderilen Parametreler</h2>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Parametre</th>
                        <th>Değer</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Merchant Key</strong></td>
                        <td><?php echo htmlspecialchars($merchantKey); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Fatura ID</strong></td>
                        <td><?php echo htmlspecialchars($invoice_id); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Para Birimi</strong></td>
                        <td><?php echo htmlspecialchars($currency_code); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Ad</strong></td>
                        <td><?php echo htmlspecialchars($name); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Soyad</strong></td>
                        <td><?php echo htmlspecialchars($surname); ?></td>
                    </tr>
                </tbody>
            </table>

            <h2>Cevap Sonuçları</h2>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Parametre</th>
                        <th>Değer</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Yanıt verileri işleme: Gereksiz bilgileri kaldırıyoruz
                    foreach ($responseData as $key => $value) {
                        // 'status', 'status_code' ve 'success_message' gibi gereksiz alanları atlıyoruz
                        if ($key != 'status' && $key != 'status_code' && $key != 'success_message') {
                            // Eğer değer bir URL içeriyorsa tıklanabilir hale getir
                            if (filter_var($value, FILTER_VALIDATE_URL)) {
                                echo "<tr><td><strong>$key</strong></td><td><a href=\"" . htmlspecialchars($value) . "\" target=\"_blank\">" . htmlspecialchars($value) . "</a></td></tr>";
                            } else {
                                echo "<tr><td><strong>$key</strong></td><td>" . htmlspecialchars(print_r($value, true)) . "</td></tr>";
                            }
                        }
                    }
                    ?>
                </tbody>
            </table>

        <?php } ?>

    </div>

    <?php include 'footer.php'; ?>

</body>

</html>
