<?php
// admin_bookings.php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { header("Location: index.php"); exit(); }

if (isset($_GET['delete_book'])) {
    $id = intval($_GET['delete_book']); // Cast to integer for security
    $stmt = $conn->prepare("DELETE FROM bookings WHERE booking_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_bookings.php");
    exit();
}

// --- NEW FILTER LOGIC ---
// Initialize variables to hold filter values (keep them empty if not set)
$filter_search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$filter_date   = isset($_GET['date']) ? $conn->real_escape_string($_GET['date']) : '';
$filter_court  = isset($_GET['court_id']) ? $conn->real_escape_string($_GET['court_id']) : '';
$filter_status = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';

// Build the SQL WHERE clause dynamically
$where_conditions = ["1=1"]; // Start with a TRUE condition so we can always append with "AND"

if ($filter_search != '') {
    // Search both Name AND Booking ID
    $where_conditions[] = "(users.full_name LIKE '%$filter_search%' OR bookings.booking_id = '$filter_search')";
}
if ($filter_date != '') {
    $where_conditions[] = "bookings.booking_date = '$filter_date'";
}
if ($filter_court != '') {
    // Assumes your court_id matches the value (1, 2, 3, etc)
    $where_conditions[] = "bookings.court_id = '$filter_court'";
}
if ($filter_status != '') {
    $where_conditions[] = "bookings.status = '$filter_status'";
}

// Join the array into a single string
$where_sql = implode(' AND ', $where_conditions);
// ------------------------

?>

<!DOCTYPE html>
<html>
<head>
    <title>All Bookings</title>
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo time(); ?>">
</head>
<body>
    <nav class="navbar">
        <a href="dashboard.php" class="brand">BookMyCourt</a>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="availability.php">Live Availability</a>
            <a href="admin_courts.php">Manage Courts</a>
            <a href="admin_bookings.php" class="active-link">All Bookings</a>
            <a href="edit_profile.php">Profile</a>
            <a href="index.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="welcome-card">
            <h3 class="mt-0 text-primary">Master Booking List</h3>
            <p class="text-muted mb-20">Manage all member bookings.</p>
            
            <form method="get" class="filter-container">
                
                <div class="input-group">
                    <label>Search</label>
                    <input type="text" name="search" class="form-control-filter w-180" 
                           placeholder="Name or ID..." 
                           value="<?php echo htmlspecialchars($filter_search); ?>">
                </div>

                <div class="input-group">
                    <label>Date</label>
                    <input type="date" name="date" class="form-control-filter" 
                           value="<?php echo htmlspecialchars($filter_date); ?>">
                </div>

                <div class="input-group">
                    <label>Court</label>
                    <select name="court_id" class="form-control-filter">
                        <option value="">All Courts</option>
                        <option value="1" <?php if($filter_court == '1') echo 'selected'; ?>>Court 1</option>
                        <option value="2" <?php if($filter_court == '2') echo 'selected'; ?>>Court 2</option>
                        <option value="3" <?php if($filter_court == '3') echo 'selected'; ?>>Court 3</option>
                        <option value="4" <?php if($filter_court == '4') echo 'selected'; ?>>Court 4</option>
                    </select>
                </div>

                <div class="input-group">
                    <label>Status</label>
                    <select name="status" class="form-control-filter">
                        <option value="">All Statuses</option>
                        <option value="Reserved" <?php if($filter_status == 'Reserved') echo 'selected'; ?>>Reserved</option>
                        <option value="Completed" <?php if($filter_status == 'Completed') echo 'selected'; ?>>Completed</option>
                        <option value="Cancelled" <?php if($filter_status == 'Cancelled') echo 'selected'; ?>>Cancelled</option>
                    </select>
                </div>

                <div class="input-group">
                    <label class="vis-hidden">Action</label>
                    <div class="flex-gap-5">
                        <button type="submit" class="btn-filter">Filter</button>
                        <?php if(!empty($filter_search) || !empty($filter_date) || !empty($filter_court) || !empty($filter_status)):
                            ?><a href="admin_bookings.php" class="btn-reset">Reset</a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>

            <table class="schedule-table">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Member Name</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Court No.</th> 
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // MAIN QUERY USING THE DYNAMIC WHERE CLAUSE
                    $sql = "SELECT bookings.*, users.full_name, users.user_id, courts.court_number 
                            FROM bookings 
                            JOIN users ON bookings.user_id = users.user_id 
                            JOIN courts ON bookings.court_id = courts.court_id
                            WHERE $where_sql
                            ORDER BY bookings.booking_date DESC, bookings.start_time ASC";
                    
                    $result = $conn->query($sql);

                    if ($result && $result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row['booking_id'] . "</td>";
                            echo "<td>" . htmlspecialchars($row['full_name']) . "</td>"; // Added htmlspecialchars for security
                            echo "<td>" . $row['booking_date'] . "</td>";
                            echo "<td>" . date('h:i A', strtotime($row['start_time'])) . "</td>";
                            echo "<td class='text-bold'>Court " . $row['court_number'] . "</td>";
                            
                            // Simple color coding for status
                            $statusClass = 'text-bold';
                            if($row['status'] == 'Reserved') $statusClass .= ' text-green';
                            else if($row['status'] == 'Cancelled') $statusClass .= ' text-red';
                            else if($row['status'] == 'Completed') $statusClass .= ' text-gray';

                            echo "<td class='$statusClass'>" . $row['status'] . "</td>";
                            
                            echo "<td>
                                    <a href='admin_bookings.php?delete_book=".$row['booking_id']."' 
                                       class='text-red text-bold no-decoration'
                                       onclick='return confirm(\"Delete this booking?\")'>
                                       [X] Delete
                                    </a>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' style='text-align:center; padding:20px;'>No bookings found matching your filters.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
