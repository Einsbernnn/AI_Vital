<?php
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
$prompt = $data['prompt'] ?? '';

if (!$prompt) {
    echo json_encode(['diagnosis' => 'No prompt provided.']);
    exit;
}

$cohere_url = 'https://api.cohere.com/v2/chat';
$api_key = 'F3LM9ycUnenzInMB2m94RWdRwHuQLnTH7cT2f5qB';
$model = 'command-a-03-2025';

$payload = [
    'model' => $model,
    'messages' => [
        ['role' => 'user', 'content' => $prompt]
    ]
];

$ch = curl_init($cohere_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'accept: application/json',
    'content-type: application/json',
    'Authorization: bearer ' . $api_key
]);
$result = curl_exec($ch);
if ($result === false) {
    echo json_encode(['diagnosis' => 'Error: ' . curl_error($ch)]);
    curl_close($ch);
    exit;
}
$data = json_decode($result, true);
curl_close($ch);

$diagnosis = '';
if (isset($data['text'])) {
    $diagnosis = $data['text'];
} elseif (isset($data['reply'])) {
    $diagnosis = $data['reply'];
} elseif (isset($data['message']['content'][0]['text'])) {
    $diagnosis = $data['message']['content'][0]['text'];
} elseif (isset($data['content'][0]['text'])) {
    $diagnosis = $data['content'][0]['text'];
} elseif (isset($data['message'])) {
    $diagnosis = is_array($data['message']) ? json_encode($data['message']) : $data['message'];
} elseif (isset($data['error'])) {
    $diagnosis = is_array($data['error']) ? json_encode($data['error']) : $data['error'];
} else {
    $diagnosis = "No response from AI. Raw: " . $result;
}

echo json_encode(['diagnosis' => $diagnosis]);
