<?php
require_once "../config/auth.php";
requireLogin();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["file"])) {
    global $database;
    $user = getUser();
    $fileName = basename($_FILES["file"]["name"]);
    $filePath = "../uploads/" . $fileName;

    // Check for duplicate file
    $existingFile = $database->querySingle("SELECT * FROM uploads WHERE file_name = '$fileName'");
    if ($existingFile) {
        $uploadError = "❌ File already exists!";
    } else {
        move_uploaded_file($_FILES["file"]["tmp_name"], $filePath);
        $database->exec("INSERT INTO uploads (user_id, file_name, file_path) VALUES ({$user['id']}, '$fileName', '$filePath')");
        $uploadSuccess = true;
        $uploadedFilePath = $filePath;
        $uploadedFileType = pathinfo($fileName, PATHINFO_EXTENSION);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>𝐏𝐇𝐏 𝐇𝐎𝐒𝐓𝐈𝐍𝐆</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="rain"></div>
    <div class="container">
        <h1>𝗝𝗘𝗥𝗥𝗬 𝐏𝐇𝐏 𝐇𝐎𝐒𝐓𝐈𝐍𝐆 𝙅𝙀𝙍𝙍𝙔</h1>
        <form method="POST" enctype="multipart/form-data">
            <label>Select File:</label>
            <input type="file" name="file" required><br>

            <button type="submit">UPLOAD</button>
        </form>

        <?php if (isset($uploadSuccess) && $uploadSuccess): ?>
            <p class="info">✅ File Uploaded: <a href="<?= $uploadedFilePath ?>" target="_blank"><?= $uploadedFilePath ?></a></p>
            <p class="info">📂 File Type: <?= $uploadedFileType ?></p>
        <?php elseif (isset($uploadError)): ?>
            <p class="info" style="color: red;"><?= $uploadError ?></p>
        <?php endif; ?>

        <a class="join-now" href="https://t.me/jerryfromrussian" target="_blank">Join our Channel</a>
    </div>
    <script src="script.js"></script>
</body>
</html>
