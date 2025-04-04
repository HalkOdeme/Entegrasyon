<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $ccNo = $data['cc_no'];
    $total = $data['total'];

    $baseUrl = "https://testapp.halkode.com.tr/ccpayment/api/getpos";
    $app_id = "006f074e818c52970b4212a4767181f3";
    $appSecret = "ff486ce745834e39ed38908cdd7ceaaf";
    $merchantKey = '$2y$10$tA5Q5IJJv8zpSh0sM.6bueB53HG2VmEKdWnj.HGewu9y5VUk7qvee';
    $currencyCode = "TRY";

    // Token isteği
    $tokenResponse = getToken($app_id, $appSecret);
    $decodedTokenResponse = json_decode($tokenResponse, true);

    if ($decodedTokenResponse['status_code'] == 100) {
        $token = $decodedTokenResponse['data']['token'];
    } else {
        echo json_encode(['error' => 'Token alınamadı', 'token_response' => $decodedTokenResponse]);
        return;
    }

    $data = array(
        "merchant_key" => $merchantKey,
        "credit_card" => $ccNo,
        "amount" => $total,
        "currency_code" => $currencyCode,
    );

    $ch = curl_init($baseUrl);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        "Authorization: Bearer $token"
    ));
    $jsonData = json_encode($data);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // CURL hatalarını yakala
    curl_setopt($ch, CURLOPT_FAILONERROR, true);

    $response = curl_exec($ch);

    // CURL hata kontrolü
    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        echo json_encode(['error' => 'CURL Hatası: ' . $error_msg]);
        return;
    }

    curl_close($ch);

    $response_data = json_decode($response, true);

    // API'den dönen yanıtı logla
    error_log("API Yanıtı: " . print_r($response_data, true));

    if (isset($response_data['data'])) {
        $installments = [];
        foreach ($response_data['data'] as $item) {
            $installments[] = [
                'installment_number' => $item['installments_number'],
                'amount' => $item['amount_to_be_paid'],
                'currency' => $item['currency_code']
            ];
        }
        echo json_encode(['installments' => $installments]);
    } else {
        echo json_encode(['error' => 'Taksit bilgisi alınamadı', 'api_response' => $response_data]);
    }
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

    // CURL hatalarını yakala
    curl_setopt($ch, CURLOPT_FAILONERROR, true);

    $response = curl_exec($ch);

    // CURL hata kontrolü
    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        return json_encode(['error' => 'CURL Hatası: ' . $error_msg]);
    }

    curl_close($ch);
    return $response;
}
?>