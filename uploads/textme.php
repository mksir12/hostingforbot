<?php
// bot.php — Webhook-based Telegram Instagram Reset Bot

ini_set('max_execution_time', 0);
date_default_timezone_set('UTC');

const TELEGRAM_TOKEN = '8489791410:AAHkfHjHaXcnKxuEjC5N6Y93Hhrlz6M24Gc';
$LOG_FILE = __DIR__ . '/bot.log';

// — Logger
function logger($msg, $level = 'INFO') {
    global $LOG_FILE;
    file_put_contents($LOG_FILE, date('c') . " [$level] $msg\n", FILE_APPEND);
}

// — Rate limiter (5 req/min per chat)
$rateLimits = [];
function canRequest($chat) {
    global $rateLimits;
    $now = time();
    $rateLimits[$chat] = array_filter($rateLimits[$chat] ?? [], fn($t) => $now - $t < 60);
    return count($rateLimits[$chat]) < 5;
}
function addRequest($chat) {
    global $rateLimits;
    $rateLimits[$chat][] = time();
}

// — UUID v4
function uuidv4() {
    $d = random_bytes(16);
    $d[6] = chr((ord($d[6]) & 0x0f) | 0x40);
    $d[8] = chr((ord($d[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($d), 4));
}

// — HTTP POST helper
function httpPost($url, $headers, $data) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER    => $headers,
        CURLOPT_POST          => true,
        CURLOPT_POSTFIELDS    => $data,
        CURLOPT_TIMEOUT       => 15,
    ]);
    $resp = curl_exec($ch);
    $err = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($err) logger("cURL error: $err", 'ERROR');
    return [$code, json_decode($resp, true)];
}

// — Instagram Mobile Reset
function instaMobileReset($username) {
    $device_id = bin2hex(random_bytes(16));
    $payload = [
        'signed_body' => 'SIGNATURE.{"username":"' . $username . '","guid":"' . uuidv4() . '","device_id":"' . $device_id . '","phone_id":"' . uuidv4() . '","_csrftoken":"missing","waterfall_id":"' . uuidv4() . '","_uid":"0","adid":"' . uuidv4() . '","login_attempt_count":"0"}',
        'ig_sig_key_version' => '4',
        'processed_headers'   => '1'
    ];
    $headers = [
        "User-Agent: Instagram 253.0.0.23.114 Android",
        "X-IG-App-ID: 567067343352427",
        "X-IG-Device-ID: $device_id",
        "Accept: application/json"
    ];
    return httpPost('https://i.instagram.com/api/v1/accounts/send_password_reset/', $headers, $payload);
}

// — Instagram Web Reset
function instaWebReset($username) {
    $csrf = uuidv4();
    $device = uuidv4();
    $headers = [
        "User-Agent: Mozilla/5.0",
        "X-CSRFToken: $csrf",
        "Referer: https://www.instagram.com/accounts/password/reset/"
    ];
    $data = [
        'email_or_username' => $username,
        '_csrftoken'        => $csrf,
        'guid'              => $device,
        'device_id'         => $device
    ];
    return httpPost('https://www.instagram.com/api/v1/web/accounts/account_recovery_send_ajax/', $headers, $data);
}

// — Send Telegram message
function sendTelegram($chat, $text) {
    $payload = [
        'chat_id'    => $chat,
        'text'       => $text,
        'parse_mode' => 'Markdown'
    ];
    $ch = curl_init("https://api.telegram.org/bot" . TELEGRAM_TOKEN . "/sendMessage");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_exec($ch);
    curl_close($ch);
}

// — Username validation
function validUsername($u) {
    return preg_match('/^[A-Za-z0-9._]{1,30}$/', $u);
}

// — Webhook Entry Point
$input = file_get_contents("php://input");
$update = json_decode($input, true);

if (!$update || !isset($update['message']['text'])) {
    logger("Empty or invalid update", 'ERROR');
    http_response_code(200);
    exit;
}

$chat = $update['message']['chat']['id'] ?? null;
$text = trim($update['message']['text'] ?? '');
if (!$chat || $text === '') exit;

logger("Got from $chat: $text");

// — Commands & messages

if ($text === '/start') {
    $txt = <<<EOT
👋 *Welcome to Instagram Account Recovery Bot!*
Made by @Kuttuxd7

This bot helps you reset your Instagram password securely.

📌 Available commands:
/start - Show welcome message
/reset - Start password reset process
/help - Show usage help

To begin, send your Instagram username (without @)
EOT;
    sendTelegram($chat, $txt);
    exit;
}

if ($text === '/help') {
    $txt = <<<EOT
📖 *Help - How to use this bot*

✅ Steps to reset your password:
1. Send your Instagram username (without @)
2. Bot tries mobile reset first, then web fallback
3. Check your email (and spam folder)

⚠️ Rules:
- Only valid usernames (letters, numbers, `.`, `_`)
- Max *5 requests per minute* per user
EOT;
    sendTelegram($chat, $txt);
    exit;
}

if ($text === '/status') {
    $txt = "✅ *Bot is running*\n📅 Server time: `" . date('Y-m-d H:i:s') . "`\n📡 Webhook mode active";
    sendTelegram($chat, $txt);
    exit;
}

if ($text === '/reset') {
    if (!canRequest($chat)) {
        sendTelegram($chat, "⚠️ Rate limit exceeded. Please wait a minute before trying again.");
    } else {
        sendTelegram($chat, "📝 Please send your Instagram username (without @)\n\nExample: `example`");
    }
    exit;
}

if (validUsername($text)) {
    if (!canRequest($chat)) {
        sendTelegram($chat, "⚠️ Rate limit exceeded. Please wait a minute before trying again.");
        exit;
    }
    addRequest($chat);

    list($code, $resp) = instaMobileReset($text);
    logger("Mobile reset $code: " . json_encode($resp));

    if ($code === 200 && isset($resp['obfuscated_email'])) {
        $msg = "✅ Reset link sent successfully!\n" .
               "📧 Check this email: `{$resp['obfuscated_email']}`\n" .
               "⚠️ Also check your spam folder\n" .
               "⏳ Link expires in 1 hour";
        sendTelegram($chat, $msg);
        exit;
    }

    list($code2, $resp2) = instaWebReset($text);
    logger("Web reset $code2: " . json_encode($resp2));

    if ($code2 === 200 && isset($resp2['contact_point'])) {
        $msg2 = "✅ Reset link sent successfully!\n" .
                "📧 Check this email: `{$resp2['contact_point']}`\n" .
                "⚠️ Also check your spam folder\n" .
                "⏳ Link expires in 1 hour";
        sendTelegram($chat, $msg2);
    } else {
        $err = $resp2['message'] ?? $resp['message'] ?? 'Unknown error';
        sendTelegram($chat, "❌ Reset failed. $err");
    }
    exit;
}

// — Invalid username
sendTelegram($chat, "❌ Invalid username format. Use only letters, numbers, dots, or underscores. Do not include `@`.");

exit;
