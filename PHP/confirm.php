<?php
// Token almak için fonksiyon
function getToken($app_id, $app_secret) {
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

// Hash hesaplama fonksiyonu
function generateConfrimPaymentHashKey($merchant_key, $invoice_id, $status, $app_secret)
{
    $data = $merchant_key . '|' . $invoice_id . '|' . $status;

    // AES-256-CBC için iv (başlatma vektörü) oluşturuluyor
    $iv = substr(sha1(mt_rand()), 0, 16); // Random bir başlatma vektörü
    $password = sha1($app_secret);

    // Salt oluşturuluyor ve password ile birleştiriliyor
    $salt = substr(sha1(mt_rand()), 0, 4);
    $saltWithPassword = hash('sha256', $password . $salt);

    // Verileri şifreliyoruz (AES-256-CBC algoritması ile)
    // null yerine [] olarak değiştirildi
    $encrypted = openssl_encrypt("$data", 'aes-256-cbc', "$saltWithPassword", 0, $iv);

    // Şifreli veriyi bundle ediyoruz
    $msg_encrypted_bundle = "$iv:$salt:$encrypted";

    // URL'deki / karakterinin yerine __ koyuyoruz
    $msg_encrypted_bundle = str_replace('/', '__', $msg_encrypted_bundle);

    // Sonuç olarak şifrelenmiş bundle'ı döndürüyoruz
    return $msg_encrypted_bundle;
}

if (isset($_POST['process_payment'])) {
    // Temel değişkenleri tanımla
    $baseUrl = "https://testapp.halkode.com.tr/ccpayment/api/confirmPayment";
    $app_id = "006f074e818c52970b4212a4767181f3";
    $appSecret = "ff486ce745834e39ed38908cdd7ceaaf"; // app_secret, token almak için kullanılacak
    $merchantKey = '$2y$10$tA5Q5IJJv8zpSh0sM.6bueB53HG2VmEKdWnj.HGewu9y5VUk7qvee';

    // Token isteği
    $tokenResponse = getToken($app_id, $appSecret);
    $decodedTokenResponse = json_decode($tokenResponse, true);

    if ($decodedTokenResponse['status_code'] == 100) {
        $token = $decodedTokenResponse['data']['token'];
    } else {
        echo "<p><strong>Hata:</strong> Token alınamadı. Lütfen bilgilerinizi kontrol ediniz.</p>";
        return;
    }

    // Formdan verileri al
    $invoice_id = $_POST['invoice_id'];
    $total = $_POST['total'];
    $status = "1";  // Sabit status değeri

    // Hash hesaplaması yapıyoruz
    $hash_key = generateConfrimPaymentHashKey($merchantKey, $invoice_id, $status, $appSecret);

    // Gönderilen verileri hazırlama
    $data = array(
        "merchant_key" => $merchantKey,
        "invoice_id" => $invoice_id,
        "total" => $total,
        "status" => $status,
        "hash_key" => $hash_key // Hesaplanan hash burada yer alacak
    );

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

    // Yanıtı işlem sonrasında gösterilecek
    $responseData = json_decode($response, true);
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ödeme Doğrulama</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <?php include 'style.php'; ?>
</head>
<body>
<?php include 'nav.php'; ?>

<div class="container mt-5">
    <h2>Ödeme Tamamlama İşlemi</h2>

    <!-- Form -->
    <form method="post">
        <div class="form-group">
            <label for="invoice_id" class="mt-2">Fatura ID:</label>
            <input type="text" class="form-control" id="invoice_id" name="invoice_id" required>
        </div>
    
        <div class="form-group">
            <label for="total">Tutar:</label>
            <input type="text" class="form-control" id="total" name="total" required>
        </div>
        <button type="submit" name="process_payment" class="btn btn-primary">Ödemeyi Gönder</button>
    </form>

    <!-- Sonuç -->
    <?php if (isset($responseData)) { ?>
        <div class="result mt-4">
            <h3>Yanıt</h3>
            <pre><?php echo htmlspecialchars(json_encode($responseData, JSON_PRETTY_PRINT)); ?></pre>
        </div>
    <?php } ?>
</div>

<?php include 'footer.php'; ?>

</body>
</html>
