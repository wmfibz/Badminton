<?php
// register.php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'];
    $username = $_POST['username']; // This is the email
    $password = $_POST['password'];
    $phone_number = $_POST['phone_number'];
    $role = 'member'; // Default role

    // Check if username already exists
    $checkStmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
    $checkStmt->bind_param("s", $username);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $error = "Username (Email) already exists!";
    } else {
        // Insert new user
        // Note: Using plain text password as per existing system pattern. 
        // ideally this should be password_hash($password, PASSWORD_DEFAULT)
        $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, role, phone_number) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, $password, $full_name, $role, $phone_number);

        if ($stmt->execute()) {
            header("Location: index.php?registered=1");
            exit;
        } else {
            $error = "Error: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register - BookMyCourt</title>
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo time(); ?>">
</head>
<body class="login-page">

    <div class="login-card" style="max-width: 450px;">
        <div class="login-logo"><img src="logo.jpg" alt="Badminton" width="120" height="120"></div>
        <h2>Create Account</h2>
        <p>Join BookMyCourt today!</p>

        <?php if(isset($error)): ?>
            <div style="background-color: #ffeaea; color: #d63031; padding: 10px; border-radius: 5px; margin-bottom: 20px; font-size: 14px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <input type="text" name="full_name" class="login-input" placeholder="Full Name" required>
            <input type="email" name="username" class="login-input" placeholder="Email Address (Username)" required>
            <input type="tel" name="phone_number" class="login-input" placeholder="Phone Number" required>
            <input type="password" name="password" class="login-input" placeholder="Password" required>
            
            <button type="submit" class="login-btn" style="background-color: #27ae60;">Register</button>
        </form>

        <div style="margin-top: 20px; font-size: 14px; color: #666;">
            Already have an account? <a href="index.php" style="color: #3498db; text-decoration: none; font-weight: bold;">Login here</a>
        </div>
    </div>

</body>
</html>