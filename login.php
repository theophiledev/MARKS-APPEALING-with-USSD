<?php
require 'db.php';
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin'] = $admin['username'];
        header("Location: admin.php");
        exit;
    } else {
        $error = "Invalid credentials.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<h2>Admin Login</h2>
<div class="container">
<?php if (isset($error)) echo "<p style='color:red'>$error</p>"; ?>
<form method="post">
    Username: <input type="text" name="username" required>
    Password: <input type="password" name="password" required><br>
    <button type="submit">Login</button>
    <p style="text-align: center;">Don't have an admin account? <a href="register.php">Register</a></p>
</form>
</div>
</body>
</html>
