<?php
// book.php
session_start();
include 'db_connect.php';

// Security Check
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

$bookingSuccess = false;

// Initialize inputs
$selected_date = isset($_POST['date']) ? $_POST['date'] : date('Y-m-d');
$selected_time = isset($_POST['start_time']) ? $_POST['start_time'] : "10:00:00"; 
$duration = isset($_POST['duration']) ? intval($_POST['duration']) : 1;

// --- HANDLE BOOKING ---
if (isset($_POST['confirm_booking'])) {
    $court_id = $_POST['final_court_id'];
    $date = $_POST['final_date'];
    $start = $_POST['final_start'];
    $dur = $_POST['final_duration'];
    $price = $_POST['final_price'];
    
    // Calculate End Time
    $end_time = date('H:i:s', strtotime($start) + ($dur * 3600));
    $user_id = $_SESSION['user_id'];

    $sql = "INSERT INTO bookings (user_id, court_id, booking_date, start_time, end_time, total_price, status) 
            VALUES ('$user_id', '$court_id', '$date', '$start', '$end_time', '$price', 'Reserved')";
    
    if ($conn->query($sql) === TRUE) {
        $bookingSuccess = true;
    } else {
        echo "<script>alert('Error: " . $conn->error . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book a Court</title>
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
                <a href="book.php" style="color:#3498db;">Book Court</a>
                <a href="my_bookings.php">My Bookings</a>
            <?php endif; ?>
            <a href="edit_profile.php">Profile</a>
            <a href="index.php" class="btn-logout">Logout</a>
        </div>
    </nav>

<div class="container">

    <div class="welcome-card">
        <h2 style="margin-top:0; color:#2c3e50;">Book a Court</h2>
        <p style="color:#666; margin-bottom:20px;">Select your preferred date and time to check available courts.</p>
        
        <form method="post">
            <div class="step-section">
                <div class="step-header"><div class="step-icon">1</div><div class="step-title">Pick a date</div></div>
                <input type="date" name="date" class="form-control" value="<?php echo $selected_date; ?>" required>
            </div>

            <div class="step-section">
                <div class="step-header"><div class="step-icon">2</div><div class="step-title">Select start time</div></div>
                <input type="time" name="start_time" class="form-control" value="<?php echo $selected_time; ?>" step="3600" required onchange="this.value = this.value.split(':')[0] + ':00'">
            </div>

            <div class="step-section">
                <div class="step-header"><div class="step-icon">3</div><div class="step-title">Select duration (Hours)</div></div>
                <select name="duration" class="form-control">
                    <option value="1" <?php if($duration == 1) echo 'selected'; ?>>1 Hour</option>
                    <option value="2" <?php if($duration == 2) echo 'selected'; ?>>2 Hours</option>
                    <option value="3" <?php if($duration == 3) echo 'selected'; ?>>3 Hours</option>
                </select>
            </div>

            <button type="submit" name="check_availability" class="btn-search">Check Availability</button>
        </form>
    </div>

    <?php if (isset($_POST['check_availability']) && !$bookingSuccess): ?>
        
        <div class="welcome-card" style="margin-top: 30px;">
            <div class="step-header"><div class="step-icon">4</div><div class="step-title">Select court(s)</div></div>
            <p style="color:#666; margin-bottom:20px;">Available courts for your selected time.</p>

            <div class="court-list">
                <?php
                $req_start = $selected_time; 
                $req_end = date('H:i:s', strtotime($selected_time) + ($duration * 3600));

                // Order by court_number so Court 1 appears before Court 2
                $sql_avail = "SELECT * FROM courts WHERE court_id NOT IN (
                    SELECT court_id FROM bookings 
                    WHERE booking_date = '$selected_date' 
                    AND ((start_time < '$req_end' AND end_time > '$req_start'))
                ) ORDER BY court_number ASC";

                $result = $conn->query($sql_avail);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $total_cost = $row['price_per_hour'] * $duration;
                        $formatted_price = number_format($total_cost, 2);
                        
                        echo "
                        <div class='court-card'>
                            <div class='court-info'>
                                <h3>Court " . $row['court_number'] . "</h3>
                                <p>Badminton</p>
                                <div class='court-price'>RM " . $formatted_price . "</div>
                            </div>
                            <div>
                                <button type='button' class='btn-book' 
                                    onclick='openCheckoutModal(
                                        \"" . $row['court_id'] . "\", 
                                        \"" . $row['court_number'] . "\", 
                                        \"" . $selected_date . "\", 
                                        \"" . $req_start . "\", 
                                        \"" . $req_end . "\", 
                                        \"" . $formatted_price . "\", 
                                        \"" . $duration . "\"
                                    )'>
                                    Book Now
                                </button>
                            </div>
                        </div>";
                    }
                } else {
                    echo "<p style='color:red;'>No courts available.</p>";
                }
                ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<div id="checkoutModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-body">
            <div class="summary-section">
                <span class="close-modal" onclick="closeModal()">&times;</span>
                <div class="summary-title">BOOKING SUMMARY</div>
                
                <h3 style="margin: 5px 0 10px 0;">Uitm Machang Sport Center</h3>
                <div class="summary-row"><span id="modalDate">Date Here</span></div>
                <div class="summary-row"><span id="modalTime">Time Here</span></div>
                <div class="summary-row" style="margin-top:5px; font-weight:bold;">
                    <span>Badminton - Court <span id="modalCourtNum">#</span></span>
                    <span id="modalPriceTop">RM 0.00</span>
                </div>
            </div>

            <div class="summary-section">
                <div class="summary-title">PRICE SUMMARY</div>
                <div class="summary-row"><span>Subtotal</span><span id="modalSubtotal">RM 0.00</span></div>
                <hr style="border: 0; border-top: 1px solid #eee; margin: 10px 0;">
                <div class="total-row"><span>TOTAL</span><span id="modalTotal">RM 0.00</span></div>
            </div>

            <div class="warning-box"><span class="warning-icon"></span> No refund or cancellation allowed !!!</div>
			
			<div class="warning-box"><span class="warning-icon"></span> Please make payment at the counter !!!</div>

            <form method="post">
                <input type="hidden" name="final_court_id" id="inputCourtId">
                <input type="hidden" name="final_date" id="inputDate">
                <input type="hidden" name="final_start" id="inputStart">
                <input type="hidden" name="final_duration" id="inputDuration">
                <input type="hidden" name="final_price" id="inputPrice">
                <button type="submit" name="confirm_booking" class="btn-checkout">Confirm Book</button>
            </form>
        </div>
    </div>
</div>

<div id="successModal" class="modal-overlay">
    <div class="modal-content">
        <div class="success-body">
            <div class="success-icon">&#10004;</div>
            <div class="success-title">Booking Confirmed!</div>
            <div class="success-message">Please proceed to counter for payment after play.</div>
            <a href="dashboard.php" class="btn-success-close">Back to Dashboard</a>
        </div>
    </div>
</div>

<script>
function openCheckoutModal(courtId, courtNum, date, start, end, price, duration) {
    let startTime = new Date('1970-01-01T' + start + 'Z').toLocaleTimeString('en-US',{timeZone:'UTC',hour12:true,hour:'numeric',minute:'numeric'});
    let endTime = new Date('1970-01-01T' + end + 'Z').toLocaleTimeString('en-US',{timeZone:'UTC',hour12:true,hour:'numeric',minute:'numeric'});
    let dateObj = new Date(date);
    let dateStr = dateObj.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', weekday: 'long' });

    document.getElementById('modalDate').innerText = dateStr;
    document.getElementById('modalTime').innerText = startTime + " - " + endTime;
    document.getElementById('modalCourtNum').innerText = courtNum; // Show Number
    document.getElementById('modalPriceTop').innerText = "RM " + price;
    document.getElementById('modalSubtotal').innerText = "RM " + price;
    document.getElementById('modalTotal').innerText = "RM " + price;

    document.getElementById('inputCourtId').value = courtId; // Send ID to database
    document.getElementById('inputDate').value = date;
    document.getElementById('inputStart').value = start;
    document.getElementById('inputDuration').value = duration;
    document.getElementById('inputPrice').value = price;

    document.getElementById('checkoutModal').style.display = 'flex';
}

function closeModal() { document.getElementById('checkoutModal').style.display = 'none'; }
<?php if ($bookingSuccess): ?>
    document.getElementById('successModal').style.display = 'flex';
<?php endif; ?>
window.onclick = function(event) {
    let modal1 = document.getElementById('checkoutModal');
    if (event.target == modal1) { modal1.style.display = "none"; }
}
</script>

</body>
</html>