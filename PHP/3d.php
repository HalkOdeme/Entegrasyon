<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3D Ödeme İşlemi</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <?php include 'style.php'; ?>
</head>

<body>

    <?php include 'nav.php'; ?>

    <?php
    include 'db.php';
    $query = $pdo->query("SELECT total FROM tutar ORDER BY id DESC LIMIT 1");
    $result = $query->fetch(PDO::FETCH_ASSOC);
    $total = $result ? $result['total'] : 2000;
    ?>

    <div class="container mt-4">

        <h2>3D Ödeme İşlemi</h2>

        <form method="post">
            <?php
            $invoice_id = date('Ymd') . '-' . rand(1000, 9999);
            ?>
            <div class="form-group">
                <label for="invoice_id" class="mt-2">Fatura Numarası:</label>
                <input type="text" class="form-control" id="invoice_id" name="invoice_id" value="<?php echo $invoice_id; ?>" readonly>
            </div>
            <div class="form-group">
                <label for="cc_holder_name">Kart Üzerindeki İsim / Soyisim:</label>
                <input type="text" class="form-control"
                    onkeypress='return ((event.charCode >= 65 && event.charCode <= 90) || (event.charCode >= 97 && event.charCode <= 122) || (event.charCode == 32))'
                    id="cc_holder_name" name="cc_holder_name" required>
            </div>
            <div class="form-group">
                <label for="cc_no">Kart Numarası:</label>
                <input type="text" placeholder="********************"
                    onkeypress="return (event.charCode !=8 && event.charCode ==0 || ( event.charCode == 46 || (event.charCode >= 48 && event.charCode <= 57)))"
                    maxlength="16" class="form-control" id="cc_no" name="cc_no" required oninput="updateInstallments()">
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="expiry_month">Son Kullanım Ayı (AA):</label>
                    <input type="text" placeholder="**"
                        onkeypress="return (event.charCode !=8 && event.charCode ==0 || ( event.charCode == 46 || (event.charCode >= 48 && event.charCode <= 57)))"
                        maxlength="2" class="form-control" id="expiry_month" name="expiry_month" required />
                </div>
                <div class="form-group col-md-6">
                    <label for="expiry_year">Son Kullanım Yılı (YY):</label>
                    <input type="text" placeholder="**"
                        onkeypress="return (event.charCode !=8 && event.charCode ==0 || ( event.charCode == 46 || (event.charCode >= 48 && event.charCode <= 57)))"
                        maxlength="2" class="form-control" id="expiry_year" name="expiry_year" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="cvv">CVV:</label>
                    <input type="text" placeholder="***"
                        onkeypress="return (event.charCode !=8 && event.charCode ==0 || ( event.charCode == 46 || (event.charCode >= 48 && event.charCode <= 57)))"
                        maxlength="3" class="form-control" id="cvv" name="cvv" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="total">Tutar:</label>
                    <input type="text"
                        onkeypress="return (event.charCode !=8 && event.charCode ==0 || ( event.charCode == 46 || (event.charCode >= 48 && event.charCode <= 57)))"
                        class="form-control" id="total" name="total" required oninput="updateInstallments()" value="<?= $total ?>">
                </div>

                <div class="form-group col-md-6">
                    <label for="installments_number">Taksit Sayısını Seçiniz:</label>
                    <select class="form-control" id="installments_number" name="installments_number" required>
                        <option value="1">Tek Çekim</option>
                    </select>
                </div>

                <div class="form-group col-md-6">
                    <label for="transaction_type">İşlem Tipi:</label>
                    <select class="form-control" id="transaction_type" name="transaction_type" required>
                        <option value="Auth">Auth</option>
                        <option value="PreAuth">PreAuth</option>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label for="invoice_description">Fatura Açıklaması:</label>
                    <input type="text" class="form-control" onkeydown="return /[a-z]/i.test(event.key)"
                        id="invoice_description" name="invoice_description" required>
                </div>
            </div>
            <button type="submit" name="process_payment" class="btns">Ödemeyi Gönder</button>
        </form>

        <?php
        if (isset($_POST['process_payment'])) {
            $baseUrl = "https://testapp.halkode.com.tr/ccpayment/api/paySmart3D";
            $app_id = "f77c7d06a417638ccde51c35fd6f6c17";
            $appSecret = "30296568e1d7941de4fd684dbc7203e4";
            $merchantKey = '$2y$10$XUmbnOQ0nmHsZy8WxIno4euYobTVUzxqtU1h..x32zyfG6qw7OYrq';

            $installments_number = $_POST['installments_number'];
            $currencyCode = 'TRY';

            $data = array(
                "cc_holder_name" => $_POST['cc_holder_name'],
                "cc_no" => $_POST['cc_no'],
                "expiry_month" => $_POST['expiry_month'],
                "expiry_year" => $_POST['expiry_year'],
                "cvv" => $_POST['cvv'],
                "currency_code" => $currencyCode,
                "installments_number" => $installments_number, 
                "invoice_id" => $invoice_id,
                "invoice_description" => "ewrwer",
                "total" => $total,
                "merchant_key" => $merchantKey,
                "items" => json_encode(
                    array(
                        array(
                            "name" => "Item1",
                            "price" => $total,
                            "quantity" => 1, // Ürün miktarını 1 olarak sabit bırak
                            "description" => "item1 description"
                        )
                    )
                ),
                "name" => "John",
                "surname" => "Dao",
                "hash_key" => generateHashKey($total, $installments_number, $currencyCode, $merchantKey, $invoice_id, $appSecret),
                "return_url" => "http://localhost/PHP/succes.php",
                "cancel_url" => "http://localhost/PHP/fail.php",
                "transaction_type" => $_POST['transaction_type']
            );

            $ch = curl_init($baseUrl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // API'ye JSON formatında gönder
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']); // JSON header ekle

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); // HTTP kodu al
            $error = curl_error($ch);
            curl_close($ch);

            if ($httpCode !== 200 || !$response) {
                echo json_encode(["error" => "API isteği başarısız. HTTP Kodu: $httpCode"]);
                exit;
            }

            echo $response;
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
        ?>
    </div>

    <?php include 'footer.php'; ?>


    <script>
        function updateInstallments() {
            const ccNo = document.getElementById('cc_no').value;
            const total = document.getElementById('total').value;

            if (ccNo.length === 16 && total.length > 0) {
                fetch('get_installment3d.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            cc_no: ccNo,
                            total: total
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        const installmentsSelect = document.getElementById('installments_number');
                        installmentsSelect.innerHTML = '<option value="1">Tek Çekim</option>';

                        if (data.installments && Array.isArray(data.installments)) {
                            data.installments.forEach(installment => {
                                const option = document.createElement('option');
                                option.value = installment.installment_number;
                                option.text = `${installment.installment_number} Taksit - ${installment.amount} ${installment.currency}`;
                                installmentsSelect.appendChild(option);
                            });
                        } else {
                            console.error('Hata: Taksit bilgisi alınamadı.', data);
                            alert('Taksit bilgisi alınamadı. Lütfen bilgilerinizi kontrol edin.');
                        }
                    })
                    .catch(error => {
                        console.error('Hata:', error);
                        alert('Bir hata oluştu. Lütfen daha sonra tekrar deneyin.');
                    });
            }
        }
    </script>

</body>

</html>