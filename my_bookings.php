<?php
// my_bookings.php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

// --- HANDLE CANCELLATION ---
if (isset($_GET['cancel_id'])) {
    $cancel_id = $_GET['cancel_id'];
    $user_id = $_SESSION['user_id'];
    
    // Security: Ensure the booking belongs to the logged-in user
    $check_sql = "SELECT * FROM bookings WHERE booking_id = '$cancel_id' AND user_id = '$user_id' AND status = 'Reserved'";
    $check_result = $conn->query($check_sql);
    
    if ($check_result->num_rows > 0) {
        $conn->query("DELETE FROM bookings WHERE booking_id = '$cancel_id'");
        // Or update status: $conn->query("UPDATE bookings SET status='Cancelled' WHERE booking_id='$cancel_id'");
        // But for now, let's delete as per implied requirement or matching admin logic. 
        // Admin logic deletes, so let's delete.
        header("Location: my_bookings.php?msg=cancelled");
        exit();
    } else {
        echo "<script>alert('Cannot cancel this booking.'); window.location='my_bookings.php';</script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Bookings</title>
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo time(); ?>">
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
                <a href="my_bookings.php" style="color:#3498db;">My Bookings</a>
            <?php endif; ?>
            <a href="edit_profile.php">Profile</a>
            <a href="index.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="welcome-card">
            <h2 style="margin-top:0; color:#2c3e50;">My Bookings</h2>
            <p style="color:#666; margin-bottom:20px;">Here is a list of all your court reservations.</p>

            <table class="schedule-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Court</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $user_id = $_SESSION['user_id'];
                    
                    // Fetch bookings
                    $sql = "SELECT bookings.*, courts.court_number 
                            FROM bookings 
                            JOIN courts ON bookings.court_id = courts.court_id 
                            WHERE bookings.user_id = '$user_id' 
                            ORDER BY bookings.booking_date DESC";
                    
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . date('d M Y', strtotime($row['booking_date'])) . "</td>";
                            echo "<td>" . date('h:i A', strtotime($row['start_time'])) . "</td>";
                            echo "<td>Court " . $row['court_number'] . "</td>";
                            echo "<td>RM " . $row['total_price'] . "</td>";
                            
                            // Status Color
                            $color = ($row['status'] == 'Reserved') ? '#f39c12' : 'green';
                            echo "<td style='color:$color; font-weight:bold;'>" . $row['status'] . "</td>";
                            
                            // Action Button (Cancel) - Only if Reserved
                            if($row['status'] == 'Reserved') {
                                 echo "<td><a href='my_bookings.php?cancel_id=" . $row['booking_id'] . "' style='color:red; text-decoration:none;' onclick='return confirm(\"Are you sure you want to cancel?\")'>Cancel</a></td>";
                            } else {
                                 echo "<td>-</td>";
                            }
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No bookings found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
            
            <br>
            <a href="dashboard.php" style="text-decoration:none; color:#3498db; font-weight: bold;">&larr; Back to Dashboard</a>
        </div>
    </div>

</body>
</html>