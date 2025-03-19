<?php
require_once "../config/database.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    
    $stmt = $database->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindValue(":email", $email, SQLITE3_TEXT);
    $user = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if ($user && password_verify($password, $user["password"])) {
        $_SESSION["user_id"] = $user["id"];
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid credentials!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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
            width: 80%;
            margin: 50px auto;
            padding: 20px;
            background: rgba(0, 0, 0, 0.8);
            border: 2px solid #CD7F32;
            border-radius: 10px;
            box-shadow: 0 0 20px #CD7F32;
        }
        h1 { color: #CD7F32; }
        input, button {
            display: block;
            width: 80%;
            margin: 10px auto;
            padding: 10px;
            font-size: 1em;
            border-radius: 5px;
        }
        input { background: #222; color: #fff; border: 1px solid #CD7F32; }
        button {
            background: #CD7F32;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        .rain {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            overflow: hidden;
            z-index: 0;
        }
        .raindrop {
            position: absolute;
            width: 2px;
            height: 100px;
            background: linear-gradient(transparent, rgba(255, 255, 255, 0.5));
            animation: fall 2s linear infinite;
        }
        @keyframes fall {
            0% { transform: translateY(-100px); opacity: 1; }
            100% { transform: translateY(100vh); opacity: 0; }
        }
    </style>
</head>
<body>
    <div class="rain"></div>

    <div class="container">
        <h1>Login</h1>
        <form method="POST">
            <input type="email" name="email" required placeholder="Email">
            <input type="password" name="password" required placeholder="Password">
            <button type="submit">Login</button>
        </form>
        <a href="register.php">Don't have an account? Register</a>
        <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    </div>

    <script>
        const rainContainer = document.querySelector('.rain');
        for (let i = 0; i < 100; i++) {
            const raindrop = document.createElement('div');
            raindrop.classList.add('raindrop');
            raindrop.style.left = Math.random() * 100 + 'vw';
            raindrop.style.animationDelay = Math.random() * 2 + 's';
            rainContainer.appendChild(raindrop);
        }
    </script>
</body>
</html>
