<?php

$app_id = "a2a7cb8131890a35353629182274c756";
$app_secret = "a9b5c28211388c7a6a705122a24e6f46";

$token = getToken($app_id, $app_secret);

echo $token;

function getToken($app_id, $app_secret) {
    $baseUrl = "https://testapp.platformode.com.tr/ccpayment/api/token";

    $data = array(
        'app_id' => $app_id,
        'app_secret' => $app_secret
    );

    $jsonData = json_encode($data);

    $ch = curl_init($baseUrl);

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
    ));

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        return 'Hata: ' . curl_error($ch);
    } else {
        $decodedResponse = json_decode($response, true);
        
        if ($decodedResponse['status_code'] == 100 ) {
			echo "İşlem Başarılı <br><br>";
			return $response;
        } else {
			echo "İşlem Başarısız <br><br>";
            return $response;
        }
    }

    curl_close($ch);
}

?>