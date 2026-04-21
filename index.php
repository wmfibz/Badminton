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
</head>
<body class="login-page">

    <div class="login-card">
        <div class="login-logo"><img src="logo.jpg" alt="Badminton" width="120" height="120"></div>
        <h2>BookMyCourt</h2>
        <p>Welcome back! Please login to your account.</p>

        <?php if(isset($_GET['registered'])): ?>
            <div style="background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px; font-size: 14px;">
                Registration successful! Please login.
            </div>
        <?php endif; ?>

        <?php if(isset($error)): ?>
            <div style="background-color: #ffeaea; color: #d63031; padding: 10px; border-radius: 5px; margin-bottom: 20px; font-size: 14px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <input type="text" name="username" class="login-input" placeholder="Username" required>
            <input type="password" name="password" class="login-input" placeholder="Password" required>
            <button type="submit" class="login-btn">Login</button>
        </form>

        <div style="margin-top: 15px; font-size: 14px; color: #666;">
            Don't have an account? <a href="register.php" style="color: #3498db; text-decoration: none; font-weight: bold;">Register here</a>
        </div>

        <div style="margin-top: 20px; font-size: 13px; color: #aaa;">
            &copy; <?php echo date("Y"); ?> Badminton System
        </div>
    </div>

</body>
</html>