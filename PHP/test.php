<?php

function generateHashKey($amount, $invoice_id, $merchant_key, $app_secret)
{
    // amount, invoice_id ve merchant_key birleştirilir
    $data = $amount . '|' . $invoice_id . '|' . $merchant_key;

    // 16 karakterlik bir IV (Initialization Vector) oluşturulur
    $iv = bin2hex(random_bytes(8)); // 16 karakter (8 byte)

    // app_secret üzerinden SHA1 hash üretilir
    $password = sha1($app_secret);

    // 4 karakterlik bir salt oluşturulur
    $salt = bin2hex(random_bytes(2)); // 4 karakter (2 byte)

    // SHA256 ile password ve salt birleştirilir
    $saltWithPassword = hash('sha256', $password . $salt);

    // Veriyi şifreler
    $encrypted = encryptor($data, substr($saltWithPassword, 0, 32), $iv);

    // IV, salt ve şifrelenmiş veriyi birleştirir ve gerekli değişiklikleri yapar
    $msg_encrypted_bundle = $iv . ':' . $salt . ':' . $encrypted;
    $msg_encrypted_bundle = str_replace('/', '__', $msg_encrypted_bundle);

    return $msg_encrypted_bundle;
}

function encryptor($textToEncrypt, $strKey, $strIV)
{
    // AES-256-CBC ile şifreleme yapar
    $encrypted = openssl_encrypt(
        $textToEncrypt,
        'AES-256-CBC',
        $strKey,
        OPENSSL_RAW_DATA,
        $strIV
    );

    // Şifrelenmiş veriyi base64 formatına çevirir
    return base64_encode($encrypted);
}

// Örnek kullanım
$amount = $_POST['amount'] ?? '';
$invoice_id = $_POST['invoice_id'] ?? '';
$app_id = "a2a7cb8131890a35353629182274c756";
$appSecret = "a9b5c28211388c7a6a705122a24e6f46";
$merchantKey = '$2y$10$e8Mzadv9523RVrAqL7K3.efxdERBrbaVZNaJ3AwlmZriYNrygSH72';

// Hash key üretimi
$hashKey = generateHashKey($amount, $invoice_id, $merchant_key, $app_secret);

echo "Hash Key: " . $hashKey;
