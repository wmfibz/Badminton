<?php
// index.php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Secure SQL Search
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['role'] = $row['role'];
        $_SESSION['full_name'] = $row['full_name'];
        header("Location: dashboard.php"); // Redirect to dashboard
    } else {
        $error = "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - BookMyCourt</title>
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo time(); ?>">
    <style>
        .alert { padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; font-weight: 500; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background-color: #ffeaea; color: #d63031; border: 1px solid #f5c6cb; }
        .login-footer { margin-top: 20px; font-size: 14px; color: var(--text-muted); }
        .login-footer a { color: var(--secondary-color); text-decoration: none; font-weight: 600; }
        .login-footer a:hover { text-decoration: underline; }
        .copyright { margin-top: 30px; font-size: 12px; color: #aaa; }
    </style>
</head>
<body class="login-page">

    <div class="login-card">
        <div class="login-logo"><img src="logo.jpg" alt="Badminton" width="100" height="100"></div>       
        <h2>BookMyCourt</h2>
        <p>Welcome back! Please login to your account.</p>

        <?php if(isset($_GET['registered'])): ?>
            <div class="alert alert-success">
                Registration successful! Please login.
            </div>
        <?php endif; ?>

        <?php if(isset($error)): ?>
            <div class="alert alert-error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <input type="text" name="username" class="login-input" placeholder="Username" required autofocus>       
            <input type="password" name="password" class="login-input" placeholder="Password" required>   
            <button type="submit" class="login-btn">Login to Account</button>
        </form>

        <div class="login-footer">
            Don't have an account? <a href="register.php">Register here</a>
        </div>

        <div class="copyright">
            &copy; <?php echo date("Y"); ?> BookMyCourt System. All rights reserved.
        </div>
    </div>

</body>
</html>
