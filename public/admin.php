<?php
require_once "../config/auth.php";
requireLogin();

if (!isAdmin()) {
    die("Access Denied");
}

$users = $database->query("SELECT id, email FROM users");
$uploads = $database->query("SELECT file_name, user_id FROM uploads");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="rain"></div>
    <div class="container">
        <h1>𝗝𝗘𝗥𝗥𝗬 𝐏𝐇𝐏 𝐇𝐎𝐒𝐓𝐈𝐍𝐆 - ADMIN</h1>

        <h3>Users</h3>
        <ul>
            <?php while ($user = $users->fetchArray(SQLITE3_ASSOC)) { ?>
                <li>User ID: <?= htmlspecialchars($user['id']) ?> - <?= htmlspecialchars($user['email']) ?></li>
            <?php } ?>
        </ul>

        <h3>Uploaded Files</h3>
        <ul>
            <?php while ($file = $uploads->fetchArray(SQLITE3_ASSOC)) { ?>
                <li><?= htmlspecialchars($file['file_name']) ?> - Uploaded by User ID: <?= htmlspecialchars($file['user_id']) ?></li>
            <?php } ?>
        </ul>
        
        <a class="join-now" href="https://t.me/jerryfromrussian" target="_blank">Join our Channel</a>
    </div>
    <script src="script.js"></script>
</body>
</html>
