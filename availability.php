<?php
// availability.php
require_once 'db_connect.php';
session_start();

// Security Check: Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Fetch Availability Data for today or selected date
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$booked_slots = [];
$sql_avail = "SELECT court_id, start_time, end_time FROM bookings WHERE booking_date = '$selected_date'";
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
    <title>Live Availability - BookMyCourt</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <style>
        .filter-bar {
            background: var(--card-bg); padding: var(--space-lg); border-radius: var(--radius-lg);
            display: flex; gap: var(--space-md); align-items: flex-end; border: 1px solid var(--border-color);
            margin-bottom: var(--space-xl);
        }
        .filter-bar div { display: flex; flex-direction: column; gap: 5px; }
        .filter-bar label { font-size: 13px; font-weight: 600; color: var(--text-muted); }
        .filter-bar input { padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; }
        .btn-filter { background: var(--secondary-color); color: white; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; cursor: pointer; }
        .btn-filter:hover { background: var(--secondary-hover); }
        
        .status-cell { height: 48px; padding: 0 !important; width: 80px; text-align: center; }
        .slot-link { display: block; width: 100%; height: 100%; text-decoration: none; }
        .booked-label { font-size: 11px; font-weight: bold; color: rgba(255,255,255,0.7); }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="dashboard.php" class="brand">BookMyCourt</a>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="availability.php" class="active-link">Live Availability</a>
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

        <div class="welcome-card" style="margin-bottom: var(--space-lg);">
            <h2 style="margin:0;">Live Court Schedule</h2>
            <p style="color:var(--text-muted); margin-top:5px;">Real-time availability for badminton courts.</p>
        </div>

        <form method="get" class="filter-bar">
            <div>
                <label for="date">Select Date:</label>
                <input type="date" name="date" value="<?php echo $selected_date; ?>">
            </div>
            <button type="submit" class="btn-filter">Refresh Availability</button>
        </form>

        <div class="welcome-card">
            <div class="legend" style="margin-bottom: var(--space-md); display: flex; gap: 20px;">
                <div class="legend-item" style="display:flex; align-items:center; gap:8px; font-size:14px;">
                    <div class="box white" style="width:16px; height:16px; border:1px solid #ddd;"></div> Available
                </div>
                <div class="legend-item" style="display:flex; align-items:center; gap:8px; font-size:14px;">
                    <div class="box blue" style="width:16px; height:16px; background:var(--primary-color);"></div> Booked
                </div>
            </div>

            <div class="grid-container">
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th class="court-col" style="width: 120px;">Court</th>
                            <?php
                            $start_hour = 8;
                            $end_hour = 23;
                            for ($h = $start_hour; $h <= $end_hour; $h++) {
                                $display_time = date('g a', strtotime("$h:00"));
                                echo "<th>$display_time</th>";    
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
                            echo "<td class='court-col' style='background: #fcfcfc; font-weight: 700;'>Court $court_num</td>";     
                            for ($h = $start_hour; $h <= $end_hour; $h++) {
                                if (isset($booked_slots[$court_id][$h])) {
                                    echo "<td class='status-booked' style='text-align:center;'><span class='booked-label'>BOOKED</span></td>";
                                } else {
                                    $time_h = str_pad($h, 2, "0", STR_PAD_LEFT);
                                    $book_url = ($_SESSION['role'] != 'admin') ? "book.php?court_id=$court_id&date=$selected_date&time=$time_h" : "#";
                                    echo "<td class='status-available'><a href='$book_url' class='slot-link'></a></td>";      
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
