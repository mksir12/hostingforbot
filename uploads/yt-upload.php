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

$title     = $_POST['title']     ?? 'Unknown Title';
$channel   = $_POST['channel']   ?? 'Unknown Channel';
$duration  = $_POST['duration']  ?? 'Unknown Duration';
$thumbnail = $_POST['thumbnail'] ?? null;

if (!$url || !$chat_id || !$bot_token) {
    http_response_code(400);
    echo "Missing required parameters";
    exit;
}

// Step 1: Download the audio file
$tmpFile = tempnam(sys_get_temp_dir(), 'tgaudio_');
file_put_contents($tmpFile, file_get_contents($url));

// Step 2: Build caption
$caption = "<b>🎵 {$title}</b>\n👤 <i>{$channel}</i>\n⏱️ <i>{$duration}</i>";

// Step 3: Prepare Telegram sendAudio payload
$telegram_url = "https://api.telegram.org/bot{$bot_token}/sendAudio";
$post_fields = [
    'chat_id'             => $chat_id,
    'audio'               => new CURLFile($tmpFile, 'audio/mpeg', "{$title}.mp3"),
    'caption'             => $caption,
    'parse_mode'          => "HTML",
    'reply_to_message_id' => $reply_to,
];

// Step 4: Add thumbnail if available
if ($thumbnail) {
    $thumbFile = tempnam(sys_get_temp_dir(), 'tgthumb_') . '.jpg';
    file_put_contents($thumbFile, file_get_contents($thumbnail));
    $post_fields['thumb'] = new CURLFile($thumbFile, 'image/jpeg');
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $telegram_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);

$response = curl_exec($ch);
$error    = curl_error($ch);
curl_close($ch);

// Cleanup
@unlink($tmpFile);
if (isset($thumbFile)) @unlink($thumbFile);

if ($error) {
    http_response_code(500);
    echo "cURL Error: " . $error;
} else {
    echo $response;
}
?>
