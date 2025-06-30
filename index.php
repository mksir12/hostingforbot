<?php
// Ensure the uploads directory exists
$uploadDir = __DIR__ . "/uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true); // Create directory with full permissions
}

$uploadedFileName = "";
$telegramWebhookUrl = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["file"], $_POST["bot_token"])) {
    $botToken = trim($_POST["bot_token"]);
    $targetFile = $uploadDir . basename($_FILES["file"]["name"]);
    $uploadedFileName = basename($_FILES["file"]["name"]);

    // Move uploaded file to the target directory
    if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFile)) {
        $uploadSuccess = true;
        $uploadedFilePath = "uploads/" . $uploadedFileName;
        $uploadedFileType = $_FILES["file"]["type"];

        // Generate Telegram webhook URL
        if (!empty($botToken)) {
            $telegramWebhookUrl = "https://api.telegram.org/bot" . htmlspecialchars($botToken) . "/setWebhook?url=https://hosting-setup.onrender.com/uploads/" . rawurlencode($uploadedFileName);
        }
    } else {
        $uploadError = "Error uploading file. Check permissions.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>𝐏𝐇𝐏 𝐇𝐎𝐒𝐓𝐈𝐍𝐆</title>
    <style>
        body {
            margin: 0;
            overflow: hidden;
            font-family: Arial, sans-serif;
            color: #00ff00;
            background: black;
            text-align: center;
        }
        .container {
            position: relative;
            z-index: 1;
            width: 80%;
            margin: 50px auto;
            border: 2px solid #CD7F32;
            padding: 20px;
            background: rgba(0, 0, 0, 0.8);
            border-radius: 10px;
            box-shadow: 0 0 20px #CD7F32;
        }
        h1 {
            font-size: 2.5em;
            color: #CD7F32;
        }
        input, button {
            display: block;
            margin: 10px auto;
            padding: 10px;
            font-size: 1em;
            border-radius: 5px;
        }
        input {
            width: 80%;
            background: #222;
            color: #fff;
            border: 1px solid #CD7F32;
        }
        button {
            background: #CD7F32;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        .info {
            margin-top: 20px;
            font-size: 1.2em;
        }
        .join-now {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 1.2em;
            color: #fff;
            background: #CD7F32;
            text-decoration: none;
            border-radius: 5px;
            box-shadow: 0 0 10px #CD7F32;
            transition: all 0.3s ease;
        }
        .join-now:hover {
            background: #0056b3;
            box-shadow: 0 0 20px #0056b3;
        }
        .rain {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            background: black;
            overflow: hidden;
        }
        .raindrop {
            position: absolute;
            width: 2px;
            height: 100px;
            background: linear-gradient(transparent, rgba(255, 255, 255, 0.5));
            animation: fall 2s linear infinite;
        }
        @keyframes fall {
            0% {
                transform: translateY(-100px);
                opacity: 1;
            }
            100% {
                transform: translateY(100vh);
                opacity: 0;
            }
        }
    </style>
</head>
<body>
    <div class="rain"></div>

    <div class="container">
        <h1>𝗝𝗘𝗥𝗥𝗬 𝐏𝐇𝐏 𝐇𝐎𝐒𝐓𝐈𝐍𝐆 𝙅𝙀𝙍𝙍𝙔</h1>
        <form action="" method="POST" enctype="multipart/form-data">
            <label style="color: #CD7F32;">Select File:</label>
            <input type="file" name="file" required><br>

            <label style="color: #CD7F32;">Enter Telegram Bot Token:</label>
            <input type="text" name="bot_token" placeholder="Enter Bot Token" required><br>

            <button type="submit">UPLOAD</button>
        </form>

        <?php if (isset($uploadSuccess) && $uploadSuccess): ?>
            <p class="info">✅ File Uploaded: <a href="<?= $uploadedFilePath ?>" target="_blank"><?= $uploadedFilePath ?></a></p>
            <p class="info">📂 File Type: <?= $uploadedFileType ?></p>
            <p class="info">👤 OWNER: @CineMazhavil</p>
            <?php if (!empty($telegramWebhookUrl)) : ?>
                <p class="info">🌐 Click Here : <a href="<?= $uploadedFilePath ?>" target="_blank"><?= $uploadedFilePath ?></a></p>
            <?php endif; ?>
        <?php elseif (isset($uploadError)): ?>
            <p class="info" style="color: red;"><?= $uploadError ?></p>
        <?php endif; ?>

        <a class="join-now" href="https://t.me/jerryfromrussian" target="_blank">Join our Channel</a>
    </div>

    <script>
        const rainContainer = document.querySelector('.rain');
        const rainCount = 100;

        for (let i = 0; i < rainCount; i++) {
            const raindrop = document.createElement('div');
            raindrop.classList.add('raindrop');
            raindrop.style.left = Math.random() * 100 + 'vw';
            raindrop.style.animationDelay = Math.random() * 2 + 's';
            raindrop.style.animationDuration = 2 + Math.random() * 2 + 's';
            rainContainer.appendChild(raindrop);
        }
    </script>
</body>
</html>
