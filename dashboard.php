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
    <style>
        .live-badge { 
            background: #e74c3c; color: white; padding: 4px 10px; border-radius: 4px; 
            font-size: 11px; font-weight: bold; text-transform: uppercase; margin-right: 10px;
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.6; }
            100% { opacity: 1; }
        }
        .hero-banner {
            width: 100%; height: 200px; object-fit: cover; border-radius: var(--radius-lg);
            margin-bottom: var(--space-lg); border: 1px solid var(--border-color);
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="dashboard.php" class="brand">BookMyCourt</a>
        <div class="nav-links">
            <a href="dashboard.php" class="active-link">Dashboard</a>
            <a href="availability.php">Live Availability</a>
            <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                <a href="admin_courts.php">Courts</a>
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
            <p style="color:var(--text-muted); margin-bottom: var(--space-md);">Manage your badminton court bookings with ease.</p>
            <div class="user-details">
                <span><strong>Email:</strong> <?php echo htmlspecialchars($user['username']); ?></span>
                <span><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone_number']); ?></span>
            </div>
        </div>

        <img src="badminton1.jpg" alt="Badminton Hero" class="hero-banner">

        <div class="grid-row">
            <?php if ($user['role'] == 'admin'): ?>
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
            <?php else: ?>
                <div class="action-card">
                    <h3>Book a Court</h3>
                    <p>Reserve a badminton court for your next game.</p>
                    <a href="book.php" class="btn-blue">Book Now</a>
                </div>

                <div class="action-card">
                    <h3>My Bookings</h3>
                    <p>View, edit, or cancel your existing bookings.</p>
                    <a href="my_bookings.php" class="btn-blue">Manage My Bookings</a>
                </div>
            <?php endif; ?>

            <div class="action-card">
                <h3>Live Availability</h3>
                <p>Check court status and real-time bookings.</p>
                <a href="availability.php" class="btn-blue">Check Availability</a>
            </div>
        </div>

        <!-- Live Court Status Section -->
        <div class="welcome-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-md);">
                <div style="display: flex; align-items: center;">
                    <span class="live-badge">Live</span>
                    <h3 style="margin:0; color: var(--primary-color);">Today's Court Status</h3>
                </div>
                <a href="availability.php" style="font-size: 0.9em; color: var(--secondary-color); text-decoration: none; font-weight: 700;">View Full Schedule &rarr;</a>
            </div>

            <div class="legend" style="margin-bottom: var(--space-md);">
                <div class="legend-item" style="display:flex; align-items:center; gap:8px; font-size:13px; color:var(--text-muted);">
                    <div class="box white" style="width:12px; height:12px; border:1px solid #ddd;"></div> Available
                </div>
                <div class="legend-item" style="display:flex; align-items:center; gap:8px; font-size:13px; color:var(--text-muted); margin-left: 20px;">
                    <div class="box blue" style="width:12px; height:12px; background:var(--primary-color);"></div> Booked
                </div>
            </div>

            <div class="grid-container">
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th class="court-col">Court</th>
                            <?php
                            $start_hour = 9;
                            $end_hour = 21; // Standard range
                            for ($h = $start_hour; $h <= $end_hour; $h++) {
                                $display_time = date('g a', strtotime("$h:00"));
                                echo "<th style='font-size: 0.8em;'>$display_time</th>";    
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
                            echo "<td class='court-col' style='font-weight: 600; font-size: 13px;'>Court $court_num</td>";     
                            for ($h = $start_hour; $h <= $end_hour; $h++) {
                                if (isset($booked_slots[$court_id][$h])) {
                                    echo "<td class='status-booked'></td>";
                                } else {
                                    echo "<td class='status-available'></td>";      
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
