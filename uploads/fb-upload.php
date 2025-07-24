<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Only POST allowed";
    exit;
}

$url       = $_POST['url']       ?? '';
$chat_id   = $_POST['chat_id']   ?? '';
$bot_token = $_POST['bot_token'] ?? '';
$reply_to  = $_POST['reply_to']  ?? '';

if (!$url || !$chat_id || !$bot_token) {
    http_response_code(400);
    echo "Missing required parameters";
    exit;
}

// Step 1: Download the video to temp file
$tmpFile = tempnam(sys_get_temp_dir(), 'tgvid_');
file_put_contents($tmpFile, file_get_contents($url));

// Step 2: Send to Telegram
$telegram_url = "https://api.telegram.org/bot{$bot_token}/sendVideo";

$post_fields = [
    'chat_id'             => $chat_id,
    'video'               => new CURLFile($tmpFile),
    'caption'             => "<b>🎬 Here's your video reel!</b>",
    'parse_mode'          => "HTML",
    'reply_to_message_id' => $reply_to,
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $telegram_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);

$response = curl_exec($ch);
$error    = curl_error($ch);
curl_close($ch);

// Step 3: Cleanup
@unlink($tmpFile);

// Output
if ($error) {
    http_response_code(500);
    echo "cURL Error: " . $error;
} else {
    echo $response;
}
?>