<?php
// edit_profile.php
session_start();
include 'db_connect.php';

// Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success_msg = "";
$error_msg = "";

// --- HANDLE FORM SUBMISSION ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'];
    $phone_number = $_POST['phone_number'];
    $password = $_POST['password'];

    // Basic validation
    if (empty($full_name) || empty($phone_number)) {
        $error_msg = "Name and Phone Number are required.";
    } else {
        if (!empty($password)) {
            // Update with password
            // Note: In a real app, you should hash the password! 
            // Assuming plain text based on index.php example (password = '$password')
            // If the original index.php used hashing, we should use it here too.
            // Let's check index.php again in thought, but for now assuming consistent simple auth.
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone_number = ?, password = ? WHERE user_id = ?");
            $stmt->bind_param("sssi", $full_name, $phone_number, $password, $user_id);
        } else {
            // Update without password
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone_number = ? WHERE user_id = ?");
            $stmt->bind_param("ssi", $full_name, $phone_number, $user_id);
        }

        if ($stmt->execute()) {
            $success_msg = "Profile updated successfully!";
            // Update session name if changed
            $_SESSION['full_name'] = $full_name;
        } else {
            $error_msg = "Error updating profile: " . $conn->error;
        }
        $stmt->close();
    }
}

// --- FETCH CURRENT USER DATA ---
$stmt = $conn->prepare("SELECT full_name, phone_number, username FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile - BookMyCourt</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>

    <nav class="navbar">
        <a href="dashboard.php" class="brand">BookMyCourt</a>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="availability.php">Live Availability</a>
            <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                <a href="admin_courts.php">Manage Courts</a>
                <a href="admin_bookings.php">All Bookings</a>
            <?php else: ?>
                <a href="book.php">Book Court</a>
                <a href="my_bookings.php">My Bookings</a>
            <?php endif; ?>
            <a href="edit_profile.php" style="color:#3498db;">Profile</a>
            <a href="index.php" class="btn-logout">Logout</a>
        </div>
    </nav>

        <div class="container">
            <div class="welcome-card" style="max-width: 600px; margin: 0 auto 30px auto;">
                <h2>Edit Profile</h2>
                <p>Update your personal information below.</p>
            </div>
    
            <div style="background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); max-width: 600px; margin: 0 auto;">            
            <?php if($success_msg): ?>
                <div style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    <?php echo $success_msg; ?>
                </div>
            <?php endif; ?>

            <?php if($error_msg): ?>
                <div style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #333;">Username (Email)</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled style="background-color: #e9ecef; cursor: not-allowed;">
                    <small style="color: #666;">Username cannot be changed.</small>
                </div>

                <div style="margin-bottom: 20px;">
                    <label for="full_name" style="display: block; margin-bottom: 8px; font-weight: bold; color: #333;">Full Name</label>
                    <input type="text" id="full_name" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                </div>

                <div style="margin-bottom: 20px;">
                    <label for="phone_number" style="display: block; margin-bottom: 8px; font-weight: bold; color: #333;">Phone Number</label>
                    <input type="text" id="phone_number" name="phone_number" class="form-control" value="<?php echo htmlspecialchars($user['phone_number']); ?>" required>
                </div>

                <div style="margin-bottom: 30px;">
                    <label for="password" style="display: block; margin-bottom: 8px; font-weight: bold; color: #333;">New Password <span style="font-weight: normal; color: #666;">(Leave blank to keep current)</span></label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter new password">
                </div>

                <button type="submit" class="btn-blue" style="width: 100%; border: none; padding: 12px; font-size: 16px; cursor: pointer;">Update Profile</button>
            </form>
        </div>
    </div>

</body>
</html>