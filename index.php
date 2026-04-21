<?php
// index.php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['role'] = $row['role'];
        $_SESSION['full_name'] = $row['full_name'];
        header("Location: dashboard.php");
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo time(); ?>">
    <style>
        .alert { padding: 1rem; border-radius: 12px; margin-bottom: 2rem; font-size: 0.9rem; font-weight: 700; text-align: left; border: 1px solid; }
        .alert-success { background: #ecfdf5; color: #065f46; border-color: #a7f3d0; }
        .alert-error { background: #fef2f2; color: #991b1b; border-color: #fecaca; }
        .login-footer { margin-top: 2rem; font-size: 0.95rem; color: #64748b; font-weight: 600; }
        .login-footer a { color: var(--accent); text-decoration: none; border-bottom: 2px solid var(--accent); padding-bottom: 2px; }
        .login-footer a:hover { color: var(--primary); border-color: var(--primary); }
    </style>
</head>
<body class="login-page">

    <div class="login-card">
        <div style="display:flex; align-items:center; justify-content:center; gap:12px; margin-bottom: 2.5rem;">
            <div style="width:48px; height:48px; background:var(--accent); border-radius:12px;"></div>
            <h1 style="margin:0; font-size:1.75rem; font-weight:800; letter-spacing:-1px; color:var(--primary); text-transform:uppercase;">BookMyCourt</h1>
        </div>

        <h2 style="font-size:1.5rem; margin-bottom: 0.5rem; color:var(--primary);">Welcome back</h2>
        <p style="color:#64748b; margin-bottom: 2.5rem; font-weight:600;">Sign in to your athlete account</p>

        <?php if(isset($_GET['registered'])): ?>
            <div class="alert alert-success">Registration successful! You can now login.</div>
        <?php endif; ?>

        <?php if(isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <div style="text-align:left; margin-bottom:0.5rem;"><label style="font-size:0.85rem; font-weight:800; color:var(--primary); text-transform:uppercase;">Username / Email</label></div>
            <input type="text" name="username" class="login-input" placeholder="e.g. champion123" required autofocus>
            
            <div style="text-align:left; margin-bottom:0.5rem;"><label style="font-size:0.85rem; font-weight:800; color:var(--primary); text-transform:uppercase;">Password</label></div>
            <input type="password" name="password" class="login-input" placeholder="••••••••" required>
            
            <button type="submit" class="login-btn">Sign In to Dashboard</button>
        </form>

        <div class="login-footer">
            New to the court? <a href="register.php">Create free account</a>
        </div>
    </div>

</body>
</html>
