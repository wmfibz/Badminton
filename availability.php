<?php
// availability.php
session_start();
include 'db_connect.php';

// 1. FILTER INPUTS
// Security: Escape the inputs to prevent SQL Injection
$selected_date = isset($_GET['date']) ? $conn->real_escape_string($_GET['date']) : date('Y-m-d');
$filter_court  = isset($_GET['court_id']) ? $conn->real_escape_string($_GET['court_id']) : '';

// 2. FETCH BOOKING DATA (Now with User Details)
// We need an array that tells us: Is Court X booked at Hour Y? AND by whom?
$booked_slots = []; 

// We JOIN users table so Admins can see WHO booked the slot
$sql = "SELECT b.court_id, b.start_time, b.end_time, b.booking_id, u.full_name 
        FROM bookings b
        JOIN users u ON b.user_id = u.user_id
        WHERE b.booking_date = '$selected_date'";

$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $cid = $row['court_id'];
        
        // Convert times to integers (e.g., "09:00:00" -> 9)
        $start_h = intval(date('H', strtotime($row['start_time'])));
        $end_h = intval(date('H', strtotime($row['end_time'])));
    
        // Mark every hour in this range as 'booked'
        for ($h = $start_h; $h < $end_h; $h++) {
            // Store an array with details instead of just 'true'
            $booked_slots[$cid][$h] = [
                'booked' => true,
                'name'   => $row['full_name'],
                'id'     => $row['booking_id']
            ];
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Live Availability</title>
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo time(); ?>">
</head>
<body>

<nav class="navbar">
    <a href="dashboard.php" class="brand">BookMyCourt</a>
    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="availability.php" class="active-link">Live Availability</a>
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
        <h2 class="mt-0 text-primary">Live Availability</h2>
        <p class="mb-20 text-muted">
            <?php echo (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') 
                ? "Hover over blue slots to see user details." 
                : "Click any white slot to make a booking."; ?>
        </p>
        
        <form method="get" action="" class="filter-bar">
            <div>
                <label class="text-bold" style="font-size: 12px; display: block; margin-bottom: 5px;">Date</label>
                <input type="date" name="date" value="<?php echo htmlspecialchars($selected_date); ?>" 
                       class="form-control" style="margin:0; height: 38px; padding: 6px 12px;">
            </div>
            
            <div>
                <label class="text-bold" style="font-size: 12px; display: block; margin-bottom: 5px;">Court</label>
                <select name="court_id" class="form-control" style="margin:0; height: 38px; min-width: 150px; padding: 6px 12px;">
                    <option value="">All Courts</option>
                    <option value="1" <?php if($filter_court=='1') echo 'selected'; ?>>Court 1</option>
                    <option value="2" <?php if($filter_court=='2') echo 'selected'; ?>>Court 2</option>
                    <option value="3" <?php if($filter_court=='3') echo 'selected'; ?>>Court 3</option>
                    <option value="4" <?php if($filter_court=='4') echo 'selected'; ?>>Court 4</option>
                </select>
            </div>

            <div style="align-self: flex-end;">
                <button type="submit" class="btn-blue" style="padding: 10px 20px;">Check</button>
                <?php if($filter_court || $selected_date != date('Y-m-d')): ?>
                    <a href="availability.php" style="margin-left: 10px; text-decoration: none; color: #666; font-size: 14px;">Reset</a>
                <?php endif; ?>
            </div>
        </form>

        <div class="legend">
            <div class="legend-item"><div class="box white"></div> Available (Click to Book)</div>
            <div class="legend-item"><div class="box blue"></div> Booked</div>
        </div>

        <div class="grid-container">
            <table class="schedule-table schedule-table-fixed">
                <thead>
                    <tr>
                        <th class="court-col">Court</th>
                        <?php
                        $start_hour = 9; 
                        $end_hour = 23;  
                        for ($h = $start_hour; $h <= $end_hour; $h++) {
                            // Using g A for cleaner look (9 AM)
                            $display_time = date('g A', strtotime("$h:00"));
                            echo "<th>$display_time</th>";
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Build Court Query based on Filter
                    $court_sql = "SELECT * FROM courts";
                    if($filter_court != '') {
                        $court_sql .= " WHERE court_id = '$filter_court'";
                    }
                    $court_sql .= " ORDER BY court_number ASC";
                    
                    $courts = $conn->query($court_sql);

                    if ($courts && $courts->num_rows > 0) {
                        while ($court = $courts->fetch_assoc()) {
                            $court_id = $court['court_id'];     
                            $court_num = $court['court_number']; 

                            echo "<tr>";
                            echo "<td class='court-col'>Court " . $court_num . "</td>";

                            // Loop through hours
                            for ($h = $start_hour; $h <= $end_hour; $h++) {
                                
                                // CHECK IF BOOKED
                                if (isset($booked_slots[$court_id][$h])) {
                                    $b_data = $booked_slots[$court_id][$h];
                                    
                                    // TOOLTIP LOGIC: Only Admins see the name
                                    $tooltip = "";
                                    if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
                                        $tooltip = "data-tooltip='" . htmlspecialchars($b_data['name'], ENT_QUOTES) . "'";
                                    }

                                    echo "<td class='status-cell status-booked' $tooltip></td>";
                                
                                } else {
                                    // AVAILABLE SLOT: CLICK TO BOOK
                                    // Pre-fill the URL for book.php
                                    $book_url = "book.php?date=$selected_date&time=$h:00&court=$court_id";
                                    
                                    echo "<td class='status-cell status-available'>";
                                    // Only allow click if user is not admin (Admins usually use backend) 
                                    // OR allow admins too if they book for others. Let's allow everyone.
                                    echo "<a href='$book_url' class='slot-link' title='Click to book'></a>";
                                    echo "</td>";
                                }
                            }
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='16'>No courts found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>