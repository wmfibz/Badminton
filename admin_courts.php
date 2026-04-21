<?php
// admin_courts.php
session_start();
include 'db_connect.php';

// Security: Kick out if not admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { 
    header("Location: index.php"); exit(); 
}

$edit_mode = false;
$edit_data = ['court_id' => '', 'court_number' => '', 'price_per_hour' => ''];

// --- HANDLE INSERT / UPDATE ---
if (isset($_POST['save_court'])) {
    $number = $_POST['court_number'];
    $price = $_POST['price'];

    if ($_POST['court_id'] != "") {
        // UPDATE EXISTING
        $id = $_POST['court_id'];
        $conn->query("UPDATE courts SET court_number='$number', price_per_hour='$price' WHERE court_id='$id'");
    } else {
        // INSERT NEW
        $conn->query("INSERT INTO courts (court_number, price_per_hour) VALUES ('$number', '$price')");
    }
    header("Location: admin_courts.php");
}

// --- HANDLE DELETE ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM courts WHERE court_id=$id");
    header("Location: admin_courts.php");
}

// --- HANDLE EDIT CLICK ---
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM courts WHERE court_id=$id");
    $edit_data = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Courts</title>
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo time(); ?>">
</head>
<body>
    <nav class="navbar">
        <a href="dashboard.php" class="brand">BookMyCourt</a>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="availability.php">Live Availability</a>
            <a href="admin_courts.php" style="color:#3498db;">Manage Courts</a>
            <a href="admin_bookings.php">All Bookings</a>
			<a href="edit_profile.php">Profile</a>
            <a href="index.php" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="container">
        
        <div class="welcome-card">
            <h3 style="margin-top:0; color:#2c3e50;"><?php echo $edit_mode ? "Edit Court" : "Add New Court"; ?></h3>
            <p style="color:#666; margin-bottom:20px;">Enter court details below.</p>
            
            <form method="post">
                <input type="hidden" name="court_id" value="<?php echo $edit_data['court_id']; ?>">
                
                <label>Court Number (e.g., 1, 2, 3):</label>
                <input type="number" name="court_number" class="form-control" value="<?php echo $edit_data['court_number']; ?>" required>

                <label>Price Per Hour (RM):</label>
                <input type="number" step="0.01" name="price" class="form-control" value="<?php echo $edit_data['price_per_hour']; ?>" required>
                
                <button type="submit" name="save_court" class="btn btn-search">
                    <?php echo $edit_mode ? "Update Court" : "Add Court"; ?>
                </button>
                <?php if($edit_mode): ?>
                    <a href="admin_courts.php" class="btn btn-red" style="text-align:center; margin-top:5px; display:block; background:#666;">Cancel</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="welcome-card" style="margin-top: 30px;">
            <h3 style="margin-top:0; color:#2c3e50;">Existing Courts</h3>
            <p style="color:#666; margin-bottom:20px;">List of all registered courts.</p>
            
                        <table class="schedule-table">
            
                            <tr>
            
                                <th>Court No.</th> <th>Price (RM)</th>
            
                                <th>Actions</th>
            
                            </tr>
            
                            <?php
            
                            // Ordered by Court Number so it looks nice (1, 2, 3...)
            
                            $result = $conn->query("SELECT * FROM courts ORDER BY court_number ASC");
            
                            while ($row = $result->fetch_assoc()) {
            
                                echo "<tr>";
            
                                echo "<td style='font-weight:bold; font-size:1.2em;'>" . $row['court_number'] . "</td>"; // Display Number
            
                                echo "<td>" . $row['price_per_hour'] . "</td>";
            
                                
            
                                echo "<td>
            
                                    <a href='admin_courts.php?edit=".$row['court_id']."' class='btn-book'>Edit</a>
            
                                    <a href='admin_courts.php?delete=".$row['court_id']."' class='btn-book' style='color:red; border-color:red;' onclick='return confirm(\"Are you sure?\")'>Delete</a>
            
                                </td>";
            
                                echo "</tr>";
            
                            }
            
                            ?>
            
                        </table>
        </div>
    </div>
</body>
</html>