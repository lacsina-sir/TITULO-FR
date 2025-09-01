<?php
session_start();
require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'] ?? null;
    $name = $conn->real_escape_string($_POST['name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $date = date('Y-m-d');
    $type = $conn->real_escape_string($_POST['type']);
    $status = 'pending';

    // Generate unique transaction number
    $transaction_number = generateTransactionNumber($conn);

    // Initialize variables
    $ls_location = $ls_area = $ls_purpose = '';
    $sp_location = $sp_use = '';
    $tt_owner = $tt_reason = '';
    $fu_ref = $fu_details = '';
    $inquiry_details = '';
    $file_paths = [];

    // Handle type-specific fields
    $ls_specify_text = $sp_specify_text = $tt_specify_text = '';
    if ($type === 'Land Survey') {
        $ls_location = $conn->real_escape_string($_POST['ls_location'] ?? '');
        $ls_area = $conn->real_escape_string($_POST['ls_area'] ?? '');
        $ls_purpose = $conn->real_escape_string($_POST['ls_purpose'] ?? '');
        $ls_specify_text = $conn->real_escape_string($_POST['ls_specify_text'] ?? '');
    } elseif ($type === 'Sketch Plan') {
        $sp_location = $conn->real_escape_string($_POST['sp_location'] ?? '');
        $sp_use = $conn->real_escape_string($_POST['sp_use'] ?? '');
        $sp_specify_text = $conn->real_escape_string($_POST['sp_specify_text'] ?? '');
    } elseif ($type === 'Title Transfer') {
        $tt_owner = $conn->real_escape_string($_POST['tt_owner'] ?? '');
        $tt_reason = $conn->real_escape_string($_POST['tt_reason'] ?? '');
        $tt_specify_text = $conn->real_escape_string($_POST['tt_specify_text'] ?? '');
    } elseif ($type === 'Follow Up') {
        $fu_ref = $conn->real_escape_string($_POST['fu_ref'] ?? '');
        $fu_details = $conn->real_escape_string($_POST['fu_details'] ?? '');
    }

    // Inquiry (optional for all types)
    $inquiry_details = $conn->real_escape_string($_POST['inquiry_details'] ?? '');

    // Handle file uploads
    foreach ($_FILES as $fileGroup) {
        if (is_array($fileGroup['name'])) {
            for ($i = 0; $i < count($fileGroup['name']); $i++) {
                if ($fileGroup['error'][$i] === UPLOAD_ERR_OK) {
                    $upload_dir = 'uploads/';
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                    $filename = time() . '_' . basename($fileGroup['name'][$i]);
                    $target = $upload_dir . $filename;
                    if (move_uploaded_file($fileGroup['tmp_name'][$i], $target)) {
                        $file_paths[] = $target;
                    }
                }
            }
        }
    }

    $file_paths_json = json_encode($file_paths);
    // Insert into database
    $sql = "INSERT INTO client_forms (
        user_id, name, last_name, type, date, status, transaction_number,
        ls_location, ls_area, ls_purpose, ls_specify_text,
        sp_location, sp_use, sp_specify_text,
        tt_owner, tt_reason, tt_specify_text,
        fu_ref, fu_details,
        inquiry_details, file_paths
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param("issssssssssssssssssss",
        $user_id, $name, $last_name, $type, $date, $status, $transaction_number,
        $ls_location, $ls_area, $ls_purpose, $ls_specify_text,
        $sp_location, $sp_use, $sp_specify_text,
        $tt_owner, $tt_reason, $tt_specify_text,
        $fu_ref, $fu_details,
        $inquiry_details, $file_paths_json
    );

    $stmt->execute();
    $stmt->close();

    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('popupOverlay').style.display = 'flex';
            document.getElementById('txnDisplay').innerText = '" . addslashes($transaction_number) . "';
        });
    </script>";
}

function generateTransactionNumber($conn) {
    do {
        $prefix = 'TXN';
        $random = strtoupper(bin2hex(random_bytes(3)));
        $txn = $prefix . '-' . date('ymd') . '-' . $random;

        $check = $conn->prepare("SELECT id FROM client_forms WHERE transaction_number = ?");
        if (!$check) {
            die("Prepare failed: " . $conn->error);
        }

        $check->bind_param("s", $txn);
        $check->execute();
        $check->store_result();
    } while ($check->num_rows > 0);

    return $txn;
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
                <label for="type">Request Type:</label>
                <select name="type" id="type" required>
                    <option value="">Select type</option>
                    <option value="Land Survey">Land Survey</option>
                    <option value="Sketch Plan">Sketch Plan / Vicinity Plan</option>
                    <option value="Title Transfer">Title Transfer / Titling</option>
                    <option value="Follow Up">Follow Up</option>
                </select>
            </div>
            <!-- Land Survey Fields -->
            <div class="form-group" id="landSurveyFields" style="display:none;">
                <label for="ls_location">Property Location (Address or GPS):</label>
                <input type="text" name="ls_location" id="ls_location" required>
                <label for="ls_area">Lot Size / Area (sqm):</label>
                <input type="text" name="ls_area" id="ls_area" required>
                <label for="ls_purpose">Purpose of Survey:</label>
                <select name="ls_purpose" id="ls_purpose" required>
                    <option value="">Select purpose</option>
                    <option value="Sale">Sale</option>
                    <option value="Transfer">Transfer</option>
                    <option value="Development">Development</option>
                    <option value="Others">Others</option>
                </select>
                <div id="ls_specifyGroup" style="display:none;">
                    <label for="ls_specify_text">Specify:</label>
                    <input type="text" name="ls_specify_text" id="ls_specify_text" required>
                </div>
                <label>Upload Existing Documents:</label>
                <div id="ls_files">
                    <input type="file" name="ls_files[]" required>
                </div>
                <button type="button" onclick="addFileInput('ls_files')" style="margin-top:8px;">Add More Files</button>
            </div>
            <!-- Sketch Plan Fields -->
            <div class="form-group" id="sketchPlanFields" style="display:none;">
                <label for="sp_location">Property Location (address):</label>
                <input type="text" name="sp_location" id="sp_location" required>
                <label for="sp_use">Intended Use:</label>
                <select name="sp_use" id="sp_use" required>
                    <option value="">Select use</option>
                    <option value="Building Permit">Building Permit</option>
                    <option value="Zoning">Zoning</option>
                    <option value="Others">Others</option>
                </select>
                <div id="sp_specifyGroup" style="display:none;">
                    <label for="sp_specify_text">Specify:</label>
                    <input type="text" name="sp_specify_text" id="sp_specify_text" required>
                </div>
                <label>Upload Supporting Files:</label>
                <div id="sp_files">
                    <input type="file" name="sp_files[]" required>
                </div>
                <button type="button" onclick="addFileInput('sp_files')" style="margin-top:8px;">Add More Files</button>
            </div>
            <!-- Title Transfer Fields -->
            <div class="form-group" id="titleTransferFields" style="display:none;">
                <label for="tt_owner">Current Title Owner Name:</label>
                <input type="text" name="tt_owner" id="tt_owner" required>
                <label for="tt_reason">Reason for Transfer:</label>
                <select name="tt_reason" id="tt_reason" required>
                    <option value="">Select reason</option>
                    <option value="Sale">Sale</option>
                    <option value="Inheritance">Inheritance</option>
                    <option value="Donation">Donation</option>
                    <option value="Others">Others</option>
                </select>
                <div id="tt_specifyGroup" style="display:none;">
                    <label for="tt_specify_text">Specify:</label>
                    <input type="text" name="tt_specify_text" id="tt_specify_text" required>
                </div>
                <label>Upload Required Documents:</label>
                <div id="tt_files">
                    <input type="file" name="tt_files[]" required>
                </div>
                <button type="button" onclick="addFileInput('tt_files')" style="margin-top:8px;">Add More Files</button>
            </div>
            <!-- Inquiry -->
            <div class="form-group" id="inquiryFields" style="display:none;">
                <label for="inquiry_details">Inquiry Details:</label>
                <textarea name="inquiry_details" id="inquiry_details" rows="3" style="width:100%;padding:10px;border-radius:8px;border:none;background:#222;color:#fff;font-size:15px;"></textarea>
            </div>
            <!-- Follow Up Fields -->
            <div class="form-group" id="followUpFields" style="display:none;">
                <label for="fu_ref">Reference Number / Transaction ID:</label>
                <input type="text" name="fu_ref" id="fu_ref" required>
                <label for="fu_details">Follow-Up Details:</label>
                <textarea name="fu_details" id="fu_details" rows="3" style="width:100%;padding:10px;border-radius:8px;border:none;background:#222;color:#fff;font-size:15px;" required></textarea>
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
        <h2 style="color:#2c5364; margin-bottom:15px;">Successfully Submitted!</h2>
        <p style="color:#2c5364; font-size:16px; margin-bottom:20px;">
            Your Transaction Number is:<br>
            <strong style="color:#00aa88; font-size:18px;" id="txnDisplay"></strong>
        </p>
        <button onclick="window.location.href='client_dashboard.php'" style="padding:10px 20px; background:#00ffcc; border:none; border-radius:8px; font-weight:bold; color:#222; cursor:pointer;">OK</button>
    </div>
</div>

<script>

function addFileInput(containerId) {
    const container = document.getElementById(containerId);

    // Create a wrapper so input & remove button stay together
    const wrapper = document.createElement('div');
    wrapper.style.display = 'flex';
    wrapper.style.alignItems = 'center';
    wrapper.style.gap = '8px';
    wrapper.style.marginTop = '8px';

    // Create file input
    const input = document.createElement('input');
    input.type = 'file';
    input.name = containerId + '[]';
    input.required = false;

    // Create remove button
    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.textContent = '✖';
    removeBtn.style.background = 'transparent';
    removeBtn.style.border = 'none';
    removeBtn.style.color = 'red';
    removeBtn.style.fontSize = '16px';
    removeBtn.style.cursor = 'pointer';

    // Remove input when button is clicked
    removeBtn.addEventListener('click', function () {
        wrapper.remove();
    });

    wrapper.appendChild(input);
    wrapper.appendChild(removeBtn);
    container.appendChild(wrapper);
}

document.addEventListener('DOMContentLoaded', function () {
    const typeEl = document.getElementById('type');

    function hideAllSections() {
        ['landSurveyFields','sketchPlanFields','titleTransferFields','followUpFields','inquiryFields']
        .forEach(id => document.getElementById(id).style.display = 'none');
    }

    function clearRequiredAll() {
        document.querySelectorAll('#landSurveyFields input, #landSurveyFields select, #landSurveyFields textarea,' +
            '#sketchPlanFields input, #sketchPlanFields select, #sketchPlanFields textarea,' +
            '#titleTransferFields input, #titleTransferFields select, #titleTransferFields textarea,' +
            '#followUpFields input, #followUpFields select, #followUpFields textarea,' +
            '#inquiryFields input, #inquiryFields select, #inquiryFields textarea'
        ).forEach(el => {
            el.required = false;
        });
    }

    function handleTypeChange() {
        const type = typeEl.value;

        hideAllSections();
        clearRequiredAll();

        if (type === 'Land Survey') {
            document.getElementById('landSurveyFields').style.display = 'block';
            document.getElementById('inquiryFields').style.display = 'block';
            ['ls_location','ls_area','ls_purpose'].forEach(id => document.getElementById(id).required = true);
            document.querySelector('#ls_files input[type="file"]').required = true;
        } 
        else if (type === 'Sketch Plan') {
            document.getElementById('sketchPlanFields').style.display = 'block';
            document.getElementById('inquiryFields').style.display = 'block';
            ['sp_location','sp_use'].forEach(id => document.getElementById(id).required = true);
            document.querySelector('#sp_files input[type="file"]').required = true;
        } 
        else if (type === 'Title Transfer') {
            document.getElementById('titleTransferFields').style.display = 'block';
            document.getElementById('inquiryFields').style.display = 'block';
            ['tt_owner','tt_reason'].forEach(id => document.getElementById(id).required = true);
            document.querySelector('#tt_files input[type="file"]').required = true;
        } 
        else if (type === 'Follow Up') {
            document.getElementById('followUpFields').style.display = 'block';
            ['fu_ref','fu_details'].forEach(id => document.getElementById(id).required = true);
        }
    }

    // Make "Specify" required only if "Others" is chosen
    function toggleSpecifyField(dropdownId, specifyGroupId, specifyInputId) {
        const dropdown = document.getElementById(dropdownId);
        const specifyGroup = document.getElementById(specifyGroupId);
        const specifyInput = document.getElementById(specifyInputId);

        dropdown.addEventListener('change', function () {
        if (this.value === 'Others') {
            specifyGroup.style.display = 'block';
            specifyInput.required = true;
        } else {
            specifyGroup.style.display = 'none';
            specifyInput.required = false;
            specifyInput.value = '';
        }
        });
    }

    // Attach listeners ONCE
    typeEl.addEventListener('change', handleTypeChange);
    toggleSpecifyField('ls_purpose', 'ls_specifyGroup', 'ls_specify_text');
    toggleSpecifyField('sp_use', 'sp_specifyGroup', 'sp_specify_text');
    toggleSpecifyField('tt_reason', 'tt_specifyGroup', 'tt_specify_text');

    // Initialize on load (in case a type is preselected)
    handleTypeChange();
});
</script>

</body>
</html>
