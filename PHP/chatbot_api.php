// chatbot_api.php
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true)['message'];

    $api_key = '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'; // OpenAI API anahtarını gir
    $url = 'https://api.openai.com/v1/chat/completions';

    $headers = [
        'Content-Type: application/json',
        "Authorization: Bearer $api_key"
    ];

    $data = [
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            ['role' => 'system', 'content' => 'Sen bir ödeme sayfasında destek veren yardımcı bir botsun. Kullanıcının ödeme ile ilgili sorularına kısa ve net cevaplar ver.'],
            ['role' => 'user', 'content' => $input]
        ],
        'max_tokens' => 100,
        'temperature' => 0.7
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        "Authorization: Bearer {$api_key}"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $responseDecoded = json_decode($response, true);
    echo json_encode(['response' => $responseDecoded['choices'][0]['message']['content']]);
}
?>
