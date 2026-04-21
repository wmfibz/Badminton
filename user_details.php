<?php
// user_details.php
session_start();
include 'db_connect.php';

// Security: Admin only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { 
    header("Location: index.php"); exit(); 
}

if (!isset($_GET['user_id'])) {
    header("Location: admin_bookings.php"); exit();
}

$user_id = $_GET['user_id'];

// Fetch User Info
$sql_user = "SELECT * FROM users WHERE user_id = '$user_id'";
$result_user = $conn->query($sql_user);

if ($result_user->num_rows == 0) {
    die("User not found.");
}
$user = $result_user->fetch_assoc();

// Fetch Booking History
$sql_bookings = "SELECT bookings.*, courts.court_number 
                 FROM bookings 
                 JOIN courts ON bookings.court_id = courts.court_id 
                 WHERE bookings.user_id = '$user_id' 
                 ORDER BY bookings.booking_date DESC";
$result_bookings = $conn->query($sql_bookings);
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Details</title>
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo time(); ?>">
</head>
<body>
    <nav class="navbar">
        <a href="dashboard.php" class="brand">BookMyCourt</a>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="admin_bookings.php">Back to Bookings</a>
        </div>
    </nav>

    <div class="container">
        <div class="welcome-card">
            <h2 style="margin-top:0; color:#2c3e50;">User Profile</h2>
            
            <div style="background:#f9f9f9; padding:15px; border-radius:8px; margin-bottom:20px;">
                <p><strong>Full Name:</strong> <?php echo $user['full_name']; ?></p>
                <p><strong>Username:</strong> <?php echo $user['username']; ?></p>
                <p><strong>Phone:</strong> <?php echo $user['phone_number']; ?></p>
            </div>

            <h3 style="color:#2c3e50;">Booking History</h3>
            <table class="schedule-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Court</th>
                        <th>Status</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result_bookings->num_rows > 0) {
                        while($row = $result_bookings->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . date('d M Y', strtotime($row['booking_date'])) . "</td>";
                            echo "<td>" . date('h:i A', strtotime($row['start_time'])) . "</td>";
                            echo "<td>Court " . $row['court_number'] . "</td>";
                            echo "<td>" . $row['status'] . "</td>";
                            echo "<td>RM " . $row['total_price'] . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No bookings found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>