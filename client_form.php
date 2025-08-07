<?php
session_start();
require 'db_connection.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'], $_POST['date'], $_POST['type'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $date = $conn->real_escape_string($_POST['date']);
    $type = $conn->real_escape_string($_POST['type']);
    $location = isset($_POST['location']) ? $conn->real_escape_string($_POST['location']) : '';
    $others_text = isset($_POST['others_text']) ? $conn->real_escape_string($_POST['others_text']) : '';
    $status = 'Waiting for approval';

    // Handle file upload
    $file_name = '';
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_name = basename($_FILES['file']['name']);
        $target_file = $upload_dir . time() . '_' . $file_name;
        if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
            $file_name = $target_file;
        } else {
            $file_name = '';
        }
    }

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO client_forms (user_id, name, last_name, date, type, location, others_text, file_path, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $user_id = $_SESSION['user_id'] ?? null;
    $stmt->bind_param("issssssss", $user_id, $name, $last_name, $date, $type, $location, $others_text, $file_name, $status);
    $stmt->execute();
    $stmt->close();

    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('popupOverlay').style.display = 'flex';
        });
    </script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Client Forms | Titulo</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            color: #fff;
            min-height: 100vh;
        }
        .topnav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(10px);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 40px;
            z-index: 1000;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .topnav .brand {
            font-size: 22px;
            font-weight: bold;
            color: #00ffcc;
            letter-spacing: 1px;
        }
        .topnav .nav-links a {
            margin-left: 25px;
            color: #fff;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
        }
        .topnav .nav-links a:hover {
            color: #00ffcc;
        }
        .main {
            padding: 100px 40px 40px;
            max-width: 600px;
            margin: auto;
            animation: fadeIn 1s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .form-section {
            background: rgba(255,255,255,0.05);
            border-left: 5px solid #00ffcc;
            padding: 32px 28px;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
        }
        .form-section h2 {
            color: #00ffcc;
            margin-bottom: 24px;
            text-align: center;
        }
        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            margin-bottom: 7px;
            color: #fff;
            font-weight: 500;
        }
        .form-group input[type="text"],
        .form-group select,
        .form-group input[type="file"] {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: none;
            background: #222;
            color: #fff;
            font-size: 15px;
        }
        .form-group input[type="file"] {
            background: #222;
            color: #fff;
        }
        button[type="submit"] {
            width: 100%;
            padding: 12px;
            background: #00ffcc;
            color: #222;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }
        button[type="submit"]:hover {
            background: #009bb5;
        }
        #successMsg {
            display: none;
            color: #00ffcc;
            font-weight: bold;
            margin-top: 20px;
        }
        .form-row {
            display: flex;
            justify-content: space-between;
            gap: 2%;
        }
        .form-row .form-group {
            flex: 1;
        }
    </style>
</head>
<body>

<div class="topnav">
<div class="brand">TITULO</div>
<div class="nav-links">
    <a href="client_dashboard.php">Dashboard</a>
    <a href="client_files.php">Files</a>
    <a href="client_profile.php">Profile</a>
    <a href="client_form.php">Forms</a>
    <a href="client-side_tracking.php">Tracking</a>
    <a href="index.php">Logout</a>
</div>
</div>

<div class="main">
    <div class="form-section">
        <h2>Submit Form</h2>
        <form id="userForm" method="POST" enctype="multipart/form-data">
            <div class="form-group form-row">
                <div style="width:49%;display:inline-block;">
                    <label for="name">First Name:</label>
                    <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($_SESSION['first_name'] ?? ''); ?>" readonly>
                </div>
                <div style="width:49%;display:inline-block;margin-left:2%;">
                    <label for="last_name">Last Name:</label>
                    <input type="text" name="last_name" id="last_name" value="<?php echo htmlspecialchars($_SESSION['last_name'] ?? ''); ?>" readonly>
                </div>
            </div>
            <div class="form-group">
                <label for="date">Date:</label>
                <input type="text" name="date" id="date" value="<?php echo date('Y-m-d'); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="type">Type:</label>
                <select name="type" id="type" required>
                    <option value="">Select type</option>
                    <option value="Land Survey">Land Survey</option>
                    <option value="Follow Up">Follow Up</option>
                    <option value="Others">Others</option>
                </select>
            </div>
            <div class="form-group" id="locationGroup" style="display:none;">
                <label for="location">Location of Property:</label>
                <input type="text" name="location" id="location">
            </div>
            <div class="form-group" id="othersGroup" style="display:none;">
                <label for="others_text">Please specify:</label>
                <textarea name="others_text" id="others_text" rows="4" style="width:100%;padding:10px;border-radius:8px;border:none;background:#222;color:#fff;font-size:15px;"></textarea>
            </div>
            <div class="form-group" id="fileGroup">
                <label for="file">Upload Completed Form:</label>
                <input type="file" name="file" id="file">
            </div>
            <button type="submit">Submit</button>
        </form>
        <div id="successMsg">
            Successfully submitted!
        </div>
    </div>
</div>

<!-- Popup Message -->
<div id="popupOverlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); justify-content:center; align-items:center; z-index:2000;">
    <div style="background:#fff; padding:30px 40px; border-radius:12px; text-align:center; max-width:400px; box-shadow:0 8px 20px rgba(0,0,0,0.3);">
        <h2 style="color:#2c5364; margin-bottom:20px;">Successfully Submitted</h2>
        <button onclick="window.location.href='client_dashboard.php'" style="padding:10px 20px; background:#00ffcc; border:none; border-radius:8px; font-weight:bold; color:#222; cursor:pointer;">OK</button>
    </div>
</div>

<script>
document.getElementById('type').addEventListener('change', function() {
    var type = this.value;
    document.getElementById('locationGroup').style.display = (type === 'Follow Up') ? 'block' : 'none';
    document.getElementById('othersGroup').style.display = (type === 'Others') ? 'block' : 'none';
    document.getElementById('fileGroup').style.display = (type === 'Land Survey') ? 'block' : 'none';
});
</script>

</body>
</html>
