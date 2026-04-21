<?php
// dashboard.php
require_once 'db_connect.php';
session_start();

// 1. Security Check: Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// 2. Fetch User Data
$user_id = $_SESSION['user_id'];
// We select username and phone_number based on your database columns
$stmt = $conn->prepare("SELECT username, full_name, phone_number, role FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Fetch Availability Data for today
$today = date('Y-m-d');
$booked_slots = []; 
$sql_avail = "SELECT court_id, start_time, end_time FROM bookings WHERE booking_date = '$today'";
$res_avail = $conn->query($sql_avail);
while ($row = $res_avail->fetch_assoc()) {
    $cid = $row['court_id'];
    $start_h = intval(date('H', strtotime($row['start_time'])));
    $end_h = intval(date('H', strtotime($row['end_time'])));
    for ($h = $start_h; $h < $end_h; $h++) {
        $booked_slots[$cid][$h] = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - BookMyCourt</title>
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
            <a href="edit_profile.php">Profile</a>
            <a href="index.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="container">
	
        <div class="welcome-card">
            <h2>Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!</h2>
            <p style="color:#666;">Manage your badminton court bookings with ease.</p>
            <div class="user-details">
                <strong>Email:</strong> <?php echo htmlspecialchars($user['username']); ?> 
                <span style="margin:0 10px; color:#ccc;">|</span> 
                <strong>Phone:</strong> <?php echo htmlspecialchars($user['phone_number']); ?>
            </div>
        </div>

	<div class="welcome-card"><center>
	<img src="badminton1.jpg" alt="Badminton" width="630" height="150">
	</center></div>

        <?php if ($user['role'] == 'admin'): ?>
            <div class="grid-row">
                <div class="action-card">
                    <h3>Manage Courts</h3>
                    <p>Add, edit price, or delete badminton courts.</p>
                    <a href="admin_courts.php" class="btn-blue">Go to Courts</a>
                </div>
                <div class="action-card">
                    <h3>All Bookings</h3>
                    <p>View all bookings from every member.</p>
                    <a href="admin_bookings.php" class="btn-blue">View Data</a>
                </div>
                <div class="action-card">
                    <h3>Live Availability</h3>
                    <p>Check court status and <br> real-time bookings.</p>
                    <a href="availability.php" class="btn-blue">Check Now</a>
                </div>
            </div>

        <?php else: ?>
            <div class="grid-row">
                <div class="action-card">
                    <h3>Book a Court</h3>
                    <p>Reserve a badminton court for your <br> next game.</p>
                    <a href="book.php" class="btn-blue">Book Now</a>
                </div>

                <div class="action-card">
                    <h3>My Bookings</h3>
                    <p>View, edit, or cancel your existing <br> bookings.</p>
                    <a href="my_bookings.php" class="btn-blue">Manage</a>
                </div>

                <div class="action-card">
                    <h3>Edit Profile</h3>
                    <p>Update your personal information.</p>
                    <a href="edit_profile.php" class="btn-blue">Edit</a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Live Court Status Section -->
        <div class="welcome-card" style="margin-top: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 style="margin:0; color: #2c3e50;">Live Court Status (Today)</h3>
                <a href="availability.php" style="font-size: 0.9em; color: #3498db; text-decoration: none; font-weight: bold;">View Full Schedule &rarr;</a>
			</div>
            
            <div class="legend">
                <div class="legend-item"><div class="box white"></div> Available</div>
                <div class="legend-item"><div class="box blue"></div> Booked</div>
            </div>

            <div class="grid-container">
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th class="court-col">Court</th>
                            <?php
                            $start_hour = 9; 
                            $end_hour = 22; // Show a slightly shorter range for dashboard
                            for ($h = $start_hour; $h <= $end_hour; $h++) {
                                $display_time = date('g a', strtotime("$h:00"));
                                echo "<th style='font-size: 0.8em; padding: 5px;'>$display_time</th>";
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $courts = $conn->query("SELECT * FROM courts ORDER BY court_number ASC");
                        while ($court = $courts->fetch_assoc()) {
                            $court_id = $court['court_id'];
                            $court_num = $court['court_number'];
                            echo "<tr>";
                            echo "<td class='court-col' style='padding: 5px;'>Court $court_num</td>";
                            for ($h = $start_hour; $h <= $end_hour; $h++) {
                                if (isset($booked_slots[$court_id][$h])) {
                                    echo "<td class='status-booked' style='padding: 5px;'></td>";
                                } else {
                                    echo "<td class='status-available' style='padding: 5px;'></td>";
                                }
                            }
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</body>
</html>