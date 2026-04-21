<?php
// dashboard.php
require_once 'db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <style>
        .live-badge { 
            background: var(--error); color: white; padding: 4px 12px; border-radius: var(--radius-sm); 
            font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em;
            display: inline-flex; align-items: center; gap: 6px;
        }
        .live-badge::before { content: ""; width: 6px; height: 6px; background: white; border-radius: 50%; }
        .hero-section {
            background: linear-gradient(rgba(15, 23, 42, 0.85), rgba(15, 23, 42, 0.85)), url('badminton1.jpg');
            background-size: cover; background-position: center; padding: 4rem 2.5rem; border-radius: 24px;
            color: white; margin-bottom: 2rem; position: relative; overflow: hidden;
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="dashboard.php" class="brand">BookMyCourt</a>
        <div class="nav-links">
            <a href="dashboard.php" class="active-link">Dashboard</a>
            <a href="availability.php">Live Schedule</a>
            <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                <a href="admin_courts.php">Courts</a>
                <a href="admin_bookings.php">Data</a>
            <?php else: ?>
                <a href="book.php">Book Now</a>
                <a href="my_bookings.php">My Bookings</a>
            <?php endif; ?>
            <a href="edit_profile.php">Profile</a>
            <a href="index.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="container">

        <div class="hero-section">
            <span class="live-badge" style="margin-bottom: 1rem;">Athletic Dashboard</span>
            <h2 style="font-size: 3rem; margin: 0; font-weight: 800; letter-spacing: -2px;">Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!</h2>
            <p style="font-size: 1.1rem; opacity: 0.9; margin-top: 0.5rem; font-weight: 600;">Dominate the court. Your next victory starts here.</p>
            
            <div class="user-details" style="border-color: rgba(255,255,255,0.1); color: white;">
                <span>ID: <?php echo htmlspecialchars($user['username']); ?></span>
                <span style="opacity: 0.5;">|</span>
                <span>Role: <?php echo strtoupper($user['role']); ?></span>
            </div>
        </div>

        <div class="grid-row">
            <?php if ($user['role'] == 'admin'): ?>
                <div class="action-card">
                    <h3 style="text-transform: uppercase;">Courts Control</h3>
                    <p>Manage badminton court availability and pricing models.</p>
                    <a href="admin_courts.php" class="btn-blue">Manage System</a>
                </div>
                <div class="action-card">
                    <h3 style="text-transform: uppercase;">Booking Logs</h3>
                    <p>Access historical and real-time player data from the system.</p>
                    <a href="admin_bookings.php" class="btn-blue">Access Database</a>
                </div>
            <?php else: ?>
                <div class="action-card">
                    <h3 style="text-transform: uppercase;">Reserve Court</h3>
                    <p>Claim your spot for the next session. High-performance courts.</p>
                    <a href="book.php" class="btn-blue">Book Session</a>
                </div>

                <div class="action-card">
                    <h3 style="text-transform: uppercase;">My Sessions</h3>
                    <p>Track your upcoming matches and manage your schedule.</p>
                    <a href="my_bookings.php" class="btn-blue">Manage Schedule</a>
                </div>
            <?php endif; ?>

            <div class="action-card">
                <h3 style="text-transform: uppercase;">Live Availability</h3>
                <p>Check court status and real-time bookings for all players.</p>
                <a href="availability.php" class="btn-blue">View Live Grid</a>
            </div>
        </div>

        <div class="welcome-card" style="margin-top: 1rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <span class="live-badge">Live Status</span>
                    <h3 style="margin:0; font-weight: 800; text-transform: uppercase;">Court Pulse</h3>
                </div>
                <a href="availability.php" style="font-size: 0.85rem; color: var(--accent); text-decoration: none; font-weight: 800; text-transform: uppercase;">Detailed View &rarr;</a>
            </div>

            <div class="grid-container">
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th class="court-col">Arena</th>
                            <?php
                            for ($h = 9; $h <= 21; $h++) {
                                echo "<th>" . date('g a', strtotime("$h:00")) . "</th>";
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $courts = $conn->query("SELECT * FROM courts ORDER BY court_number ASC");
                        while ($court = $courts->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td class='court-col' style='font-weight: 800; text-transform: uppercase; font-size: 0.75rem; text-align: center;'>Court " . $court['court_number'] . "</td>";
                            for ($h = 9; $h <= 21; $h++) {
                                $class = isset($booked_slots[$court['court_id']][$h]) ? 'status-booked' : 'status-available';
                                echo "<td class='$class'></td>";
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
