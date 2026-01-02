<?php
$hash = "";
if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST["password"])) {
    $hash = password_hash($_POST["password"], PASSWORD_DEFAULT);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PHP Password Hash Generator</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f4f4f4; padding:40px; }
        .box { background:#fff; padding:20px; max-width:500px; margin:auto; border-radius:8px; }
        input[type=password], textarea {
            width:100%; padding:10px; margin-top:10px;
        }
        button {
            margin-top:15px; padding:10px 15px; cursor:pointer;
        }
        textarea { height:120px; }
    </style>
</head>
<body>
<div class="box">
    <h2>PHP Password Hash Generator</h2>

    <form method="post">
        <label>Enter Password:</label>
        <input type="password" name="password" required>

        <button type="submit">Generate Hash</button>
    </form>

    <?php if ($hash): ?>
        <h3>Generated Hash:</h3>
        <textarea readonly><?php echo htmlspecialchars($hash); ?></textarea>
    <?php endif; ?>
</div>
</body>
</html>
