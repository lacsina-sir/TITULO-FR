<?php

// CONNECT
$host = "localhost";
$user = "root";
$pass = "";
$db   = "titulo_db";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// FETCH latest form submissions
$sql = "
    SELECT
        id,
        CONCAT(name, ' ', last_name) AS full_name,
        type,
        transaction_number,
        ls_location,
        ls_area,
        ls_purpose,
        ls_specify_text,
        sp_location,
        sp_use,
        sp_specify_text,
        tt_owner,
        tt_reason,
        tt_specify_text,
        fu_ref,
        fu_details,
        inquiry_details,
        file_paths,
        status,
        created_at
    FROM client_forms
    WHERE status = 'pending'
    ORDER BY created_at DESC
";

$result = $conn->query($sql);
if (!$result) {
    die("Query failed: " . $conn->error);
}

$getForm = $conn->prepare("SELECT * FROM client_forms WHERE id = ?");
$getForm->bind_param("i", $id);
$getForm->execute();
$formResult = $getForm->get_result();
$form = $formResult->fetch_assoc();
$getForm->close();

if ($form) {
    $clientName = $form['name'] . ' ' . $form['last_name'];
    $status = 'Pending';
    $now = date('Y-m-d H:i:s');
    $details = json_encode($form); // Save all form fields

    $insert = $conn->prepare("INSERT INTO pending_updates (user_id, client_name, request_type, transaction_number, status, last_updated, details) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $insert->bind_param("issssss", $form['user_id'], $clientName, $form['type'], $form['transaction_number'], $status, $now, $details);
    $insert->execute();
    $insert->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Client Requests – Admin</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to right, #0f2027, #203a43, #2c5364);
            color: #fff;
            display: flex;
        }

        .sidebar {
            width: 220px;
            background-color: #111;
            height: 100vh;
            padding-top: 20px;
            position: fixed;
        }

        .sidebar h2 {
        text-align: center;
        margin-bottom: 30px;
        font-size: 22px;
        color: #00bcd4;
        }

        .sidebar a {
        display: block;
        padding: 15px 20px;
        color: #bbb;
        text-decoration: none;
        transition: background 0.3s, color 0.3s;
        }

        .sidebar a:hover {
        background-color: #333;
        color: #fff;
        }

        .sidebar a.active {
        background-color: #00ffff;
        color: black;
        }

        .main {
        margin-left: 220px;
        padding: 30px;
        width: calc(100% - 220px);
        }
        
        .main h1 {
        font-size: 28px;
        margin-bottom: 20px;
        color: #fff;
        }
        .request-card {
        background-color: #1e1e2f;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 0 15px rgba(0,0,0,0.3);
        margin-bottom: 20px;
        transition: transform 0.2s ease;
        }

        .request-card h3 {
        margin: 0 0 5px;
        font-size: 20px;
        color: #00bcd4;
        }

        .request-card p {
        margin: 8px 0;
        font-size: 16px;
        }

        .request-card small {
        color: #bbb;
        }
        
        .request-card a {
        color: #00ffcc;
        text-decoration: none;
        }
        .request-card a:hover {
        text-decoration: underline;
        }

        .container {
            max-width: 1000px;
            margin: 50px auto;
            padding: 30px;
            background-color: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.1);
            animation: fadeIn 1s ease-in-out;
        }

        .button-group {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            gap: 10px;
        }
        .request-card {
            position: relative;
        }

        .approve-btn, .reject-btn, .message-btn {
            padding: 6px 14px;
            border-radius: 6px;
            border: none;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
        }
        .approve-btn { 
            background: #00ffcc; color: #222; 
        }
        .approve-btn:hover { 
            background: #00e6b3; 
        }
        .reject-btn { 
            background: #ff4444; color: #fff;
        }
        .reject-btn:hover { 
            background: #d32f2f; 
        }
        .rejct {
            display: block;
            font-size: 1.5em;
            margin-block-start: 0.83em;
            margin-block-end: 0.83em;
            margin-inline-start: 0px;
            margin-inline-end: 0px;
            font-weight: bold;
            unicode-bidi: isolate;
        }
    </style>
    </head>
    <body>

    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Titulo Admin</h2>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="admin_client_requests.php" class="active">Client Requests</a>
        <a href="admin_client_updates.php">Client Updates</a>
        <a href="transaction_files.php">Survey Files</a>
        <a href="admin_chat.php">Chat</a>
        <a href="index.php">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main">
        <h1>Client Requests</h1>

        <?php if ($result->num_rows): ?>
        <?php while ($r = $result->fetch_assoc()): ?>
        <div class="request-card">
            <h3><?= htmlspecialchars($r['full_name']) ?></h3>
            <p>Type: <?= htmlspecialchars($r['type']) ?></p>

            <?php if (!empty($r['transaction_number'])): ?>
                <p style="color:#00ffcc;">
                    Transaction #: <strong><?= htmlspecialchars($r['transaction_number']) ?></strong>
                </p>
            <?php endif; ?>

            <?php if ($r['type'] === 'Land Survey'): ?>
                <p>Location: <?= htmlspecialchars($r['ls_location']) ?></p>
                <p>Area: <?= htmlspecialchars($r['ls_area']) ?></p>
                <p>Purpose: <?= htmlspecialchars($r['ls_purpose']) ?></p>

            <?php elseif ($r['type'] === 'Sketch Plan'): ?>
                <p>Location: <?= htmlspecialchars($r['sp_location']) ?></p>
                <p>Use: <?= htmlspecialchars($r['sp_use']) ?></p>

            <?php elseif ($r['type'] === 'Title Transfer'): ?>
                <p>Owner: <?= htmlspecialchars($r['tt_owner']) ?></p>
                <p>Reason: <?= htmlspecialchars($r['tt_reason']) ?></p>

            <?php elseif ($r['type'] === 'Follow Up'): ?>
                <p>Reference: <?= htmlspecialchars($r['fu_ref']) ?></p>
                <p>Details: <?= nl2br(htmlspecialchars($r['fu_details'])) ?></p>

            <?php elseif ($r['type'] === 'Inquiry'): ?>
                <p>Details: <?= nl2br(htmlspecialchars($r['inquiry_details'])) ?></p>
            <?php endif; ?>

            <?php if (!empty($r['specify_text'])): ?>
                <p><strong>Other:</strong> <?= htmlspecialchars($r['specify_text']) ?></p>
            <?php endif; ?>

            <?php if (!empty($r['file_paths'])): ?>
                <p>Files:</p>
                <ul>
                    <?php 
                    $files = json_decode($r['file_paths'], true);
                    if (!is_array($files)) {
                        $files = explode(',', $r['file_paths']);
                    }
                    foreach ($files as $file): 
                        $file = trim($file);
                        if ($file): ?>
                            <li>
                                <a href="<?= htmlspecialchars($file) ?>" target="_blank">View File</a>
                            </li>
                        <?php endif;
                    endforeach; ?>
                </ul>
            <?php else: ?>
                <p><em>No files uploaded.</em></p>
            <?php endif; ?>

            <small>Requested on <?= date("F j, Y, g:i a", strtotime($r['created_at'])) ?></small>
            <div class="button-group">
                <button class="approve-btn" data-id="<?= htmlspecialchars($r['id']) ?>" title="Approve">&#10003;</button>
                <button class="reject-btn" data-id="<?= htmlspecialchars($r['id']) ?>" title="Reject">&#10005;</button>
            </div>
        </div>

        <?php endwhile; ?>
        <?php else: ?>
        <p class="container">No client requests found.</p>
        <?php endif; ?>
    </div>
    <div id="rejectModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); justify-content:center; align-items:center; z-index:2000;">
        <div style="background:#1f2b38; padding:30px; border-radius:12px; width:400px; box-shadow:0 0 20px rgba(255,0,0,0.3);">
            <h2 style="color:#ff4444; margin-bottom:20px;">Reject Request</h2>
            <form id="rejectForm">
            <input type="hidden" name="id" id="rejectId">
            <div style="margin-bottom:12px;">
                <label style="color:#fff;">Reason for rejection:</label><br>
                <textarea id="rejectReason" name="reason" required placeholder="Enter reason..." style="width:100%; padding:8px; border-radius:6px; border:none; resize:vertical;"></textarea>
            </div>
            <div style="text-align:right;">
                <button type="button" onclick="closeRejectModal()" style="margin-right:10px; padding:8px 12px; background:#ccc; border:none; border-radius:6px; color:#222;">Cancel</button>
                <button type="submit" style="padding:8px 12px; background:#ff4444; border:none; border-radius:6px; color:#fff;">Save</button>
            </div>
            </form>
        </div>
    </div>
</div>

<script>
function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
    }

    document.querySelectorAll('.reject-btn').forEach(btn => {
    btn.onclick = function () {
        document.getElementById('rejectId').value = btn.dataset.id;
        document.getElementById('rejectModal').style.display = 'flex';
    };
    });

    document.getElementById('rejectForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const id = document.getElementById('rejectId').value;
    const reason = document.getElementById('rejectReason').value;

    fetch('admin_reject_request.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + encodeURIComponent(id) + '&reason=' + encodeURIComponent(reason)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Save successful.');
            const card = document.querySelector(`.reject-btn[data-id="${id}"]`).closest('.request-card');
            card.style.transition = 'opacity 0.4s';
            card.style.opacity = 0;
            setTimeout(() => card.remove(), 400);
        } else {
            alert('Failed to reject: ' + (data.error || 'Unknown error'));
        }
        closeRejectModal();
    });
    });

    document.querySelectorAll('.approve-btn').forEach(btn => {
    btn.onclick = function() {
        const id = btn.dataset.id;
        fetch('admin_approve_request.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=' + encodeURIComponent(id)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Remove the request card from the DOM
                const card = btn.closest('.request-card');
                card.style.transition = 'opacity 0.4s';
                card.style.opacity = 0;
                setTimeout(() => card.remove(), 400);
            } else {
                alert('Failed to approve: ' + (data.error || 'Unknown error'));
            }
        });
    };
    });

</script>

</body>
</html>
