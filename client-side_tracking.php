<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

$user_id = $_SESSION['user_id'];
$form_id = $_GET['form_id'] ?? null;

$formsQuery = "SELECT client_forms.*, (
  SELECT remarks FROM progress_tracker pt WHERE pt.client_id = client_forms.id AND (pt.status = '' OR pt.status IS NULL) ORDER BY pt.updated_at DESC LIMIT 1
) AS latest_admin_remark, (
  SELECT status FROM progress_tracker pt2 WHERE pt2.client_id = client_forms.id AND pt2.status IS NOT NULL AND pt2.status <> '' ORDER BY pt2.updated_at DESC LIMIT 1
) AS latest_tracking_status FROM client_forms WHERE user_id = ? ORDER BY created_at DESC";
$formsStmt = $conn->prepare($formsQuery);
$formsStmt->bind_param("i", $user_id);
$formsStmt->execute();
$forms = $formsStmt->get_result();

// If a form is clicked, fetch its details
$form = null;
$tracking = null;
if ($form_id) {
  $formQuery = "SELECT * FROM client_forms WHERE id = ? AND user_id = ?";
  $formStmt = $conn->prepare($formQuery);

  if (!$formStmt) {
    die("Prepare failed: " . $conn->error);
  }

  $formStmt->bind_param("ii", $form_id, $user_id);
  $formStmt->execute();
  $form = $formStmt->get_result()->fetch_assoc();

  // Get progress tracker
  $trackingQuery = "SELECT status, remarks, DATE_FORMAT(updated_at, '%b %d, %Y %H:%i') AS updated_at
                  FROM progress_tracker
                  WHERE client_id = ?
                  ORDER BY updated_at ASC";

  $trackingStmt = $conn->prepare($trackingQuery);
  if (!$trackingStmt) {
    die("Tracking prepare failed: " . $conn->error);
  }
  $trackingStmt->bind_param("i", $form_id);
  $trackingStmt->execute();
  $tracking = $trackingStmt->get_result();
  $trackingStmt->close();

  date_default_timezone_set('Asia/Manila');

  // Fetch latest status from progress_tracker (if any)
  $statusQuery = "SELECT status FROM progress_tracker WHERE client_id = ? ORDER BY updated_at DESC LIMIT 1";
  $statusStmt = $conn->prepare($statusQuery);
  if ($statusStmt) {
    $statusStmt->bind_param("i", $form_id);
    $statusStmt->execute();
    $statusResult = $statusStmt->get_result()->fetch_assoc();
    $statusStmt->close();
    $latestStatus = $statusResult['status'] ?? $form['status'];
  } else {
    $latestStatus = $form['status'];
  }

  $updateStatus = $conn->prepare("UPDATE client_forms SET status = ? WHERE id = ?");
  if ($updateStatus) {
    $updateStatus->bind_param("si", $latestStatus, $form_id);
    $updateStatus->execute();
    $updateStatus->close();
  }
}

// Handle approve/reject
if (isset($_POST['action']) && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $remarks = $_POST['remarks'] ?? '';

    if ($_POST['action'] === 'approve') {
        $status = 'Approved';
    } elseif ($_POST['action'] === 'reject') {
        $status = 'Rejected';
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        exit;
    }

    // Check if row exists in progress_tracker
    $check = $conn->prepare("SELECT id FROM progress_tracker WHERE client_id = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $result = $check->get_result();
    $exists = $result->num_rows > 0;
    $check->close();

    if ($exists) {
        $stmt = $conn->prepare("UPDATE progress_tracker SET status = ?, remarks = ?, updated_at = NOW() WHERE client_id = ?");
        $stmt->bind_param("ssi", $status, $remarks, $id);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $conn->prepare("INSERT INTO progress_tracker (client_id, status, remarks, updated_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iss", $id, $status, $remarks);
        $stmt->execute();
        $stmt->close();
    }

    // Update client_forms table to sync status
    $updateForm = $conn->prepare("UPDATE client_forms SET status = ? WHERE id = ?");
    $updateForm->bind_param("si", $status, $id);
    $updateForm->execute();
    $updateForm->close();

    echo json_encode(['success' => true, 'status' => $status]);
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Client Tracking | TITULO</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, sans-serif;
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
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 40px;
      z-index: 1000;
    }

    .topnav .brand {
      font-size: 22px;
      font-weight: bold;
      color: #00ffcc;
    }

    .topnav .nav-links a {
      margin-left: 25px;
      color: #fff;
      text-decoration: none;
      font-weight: bold;
    }

    .topnav .nav-links a:hover {
      color: #00ffcc;
    }

    .container {
      max-width: 1100px;
      margin: 100px auto 50px;
      background: rgba(255, 255, 255, 0.05);
      border-radius: 12px;
      padding: 30px;
    }

    h1 {
      text-align: center;
      color: #00ffcc;
      margin-bottom: 25px;
    }

    .no-transaction {
      text-align: center;
      padding: 60px 0;
      color: #999;
      font-size: 20px;
      font-weight: 600;
      letter-spacing: 0.5px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      border: 1px solid rgba(7, 255, 152, 0.83);
      border-radius: 8px;
      overflow: hidden;
    }

    th,
    td {
      padding: 12px;
      text-align: center;
      border: 1px solid rgba(60, 137, 101, 1);
    }

    th {
      background: #00ffcc;
      color: #000;
    }

    td {
      background: rgba(255, 255, 255, 0.05);
    }

    .clickable-row:hover {
      background: rgba(0, 255, 204, 0.1);
      cursor: pointer;
    }

    .status {
      font-weight: bold;
      padding: 6px 14px;
      border-radius: 20px;
      display: inline-block;
    }

    .approved {
      color: #00e676;
      background: rgba(0, 230, 118, 0.1);
    }

    .rejected {
      color: #ff5252;
      background: rgba(255, 82, 82, 0.1);
    }

    .pending {
      color: #ffeb3b;
      background: rgba(255, 235, 59, 0.1);
    }

    .admin-updated {
      color: #3bbcff;
      background: rgba(59, 188, 255, 0.06);
    }

    .search-container {
      display: flex;
      justify-content: flex-start;
      margin-bottom: 15px;
    }

    #search {
      width: 250px;
      padding: 10px 14px;
      border-radius: 25px;
      border: 1px solid rgba(0, 255, 204, 0.5);
      background: rgba(255, 255, 255, 0.05);
      color: #00ffcc;
      font-size: 14px;
      outline: none;
      transition: 0.3s;
    }

    #search::placeholder {
      color: rgba(255, 255, 255, 0.77);
    }

    #search:focus {
      border-color: #00ffcc;
      box-shadow: 0 0 8px rgba(0, 255, 204, 0.4);
      background: rgba(0, 0, 0, 0.4);
    }

    /* Modal */
    .modal {
      display: none;
      position: fixed;
      z-index: 2000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.7);
    }

    .modal-content {
      background-color: #1a1a1a;
      margin: 6% auto;
      padding: 25px;
      border-radius: 10px;
      width: 65%;
      color: white;
    }

    .close {
      color: #ccc;
      margin-bottom: 20px;
      float: right;
      font-size: 28px;
      cursor: pointer;
    }

    .close:hover {
      color: #fff;
    }

    a.file-link {
      color: #00ffcc;
      text-decoration: none;
    }

    a.file-link:hover {
      text-decoration: underline;
    }

    .request-status-box {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid #00ffcc;
      border-radius: 10px;
      padding: 18px 22px;
      margin: 20px 0;
      box-shadow: 0 0 10px rgba(0, 255, 255, 0.15);
    }

    .request-status-box h3 {
      color: #00ffcc;
      font-size: 18px;
      margin-bottom: 10px;
      font-weight: 600;
    }

    .status-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 6px 0;
      font-size: 16px;
    }

    .status-text {
      font-weight: 600;
    }

    .status-text.approved {
      color: #00e676;
    }

    .status-text.pending {
      color: #3bbcff;
    }

    .status-text.rejected {
      color: #ff5252;
    }

    .status-date {
      color: #aaa;
      font-size: 14px;
    }

    .reason-row {
      margin-left: 20px;
      font-size: 15px;
      color: #ffaaaa;
    }

    .admin-update-row {
      margin-left: 20px;
      font-size: 15px;
      color: #ccc;
      padding-bottom: 4px;
    }

    .status-date,
    .update-date {
      color: #ccc;
      font-size: 14px;
    }

    .update-label {
      font-weight: 600;
      color: #0ea5ff;
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

  <div class="container">
    <h1>Transactions</h1>
    <div class="search-container">
      <input type="text" id="search" placeholder="Search transactions...">
    </div>

    <?php if ($forms->num_rows > 0): ?>
      <table id="transactionTable">
        <thead>
          <tr>
            <th>Transaction #</th>
            <th>Request Type</th>
            <th>Purpose</th>
            <th>Files</th>
            <th>Area</th>
            <th>Date Requested</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $forms->fetch_assoc()): ?>
            <tr class="clickable-row" data-id="<?= $row['id']; ?>">
              <td><?= htmlspecialchars($row['transaction_number']); ?></td>
              <td><?= htmlspecialchars($row['type']); ?></td>
              <td>
                <?php
                  $displayPurpose = '';
                  if ($row['type'] === 'Land Survey') {
                    if (!empty($row['ls_purpose'])) {
                      if ($row['ls_purpose'] === 'Others' && !empty($row['ls_specify_text'])) {
                        $displayPurpose = $row['ls_specify_text'];
                      } elseif ($row['ls_purpose'] === 'Others') {
                        $displayPurpose = '';
                      } else {
                        $displayPurpose = $row['ls_purpose'];
                      }
                    }
                  } elseif ($row['type'] === 'Sketch Plan') {
                    if (!empty($row['sp_use'])) {
                      if ($row['sp_use'] === 'Others' && !empty($row['sp_specify_text'])) {
                        $displayPurpose = $row['sp_specify_text'];
                      } elseif ($row['sp_use'] === 'Others') {
                        $displayPurpose = '';
                      } else {
                        $displayPurpose = $row['sp_use'];
                      }
                    }
                  }

                  if (empty($displayPurpose)) {
                    $displayPurpose = $row['purpose'] ?? 'N/A';
                  }

                  echo htmlspecialchars($displayPurpose);
                ?>
              </td>
              <td>
                <?php
                if (!empty($row['file_paths'])) {
                  $files = json_decode($row['file_paths'], true);
                  if ($files && is_array($files)) {
                    foreach ($files as $file) {
                      echo '<a href="' . htmlspecialchars($file) . '" class="file-link" target="_blank">Download</a><br>';
                    }
                  } else echo 'No file';
                } else echo 'No file';
                ?>
              </td>
              <td><?= htmlspecialchars($row['ls_area'] ?? $row['area'] ?? '—'); ?></td>
              <td><?= date("m/d/Y H:i", strtotime($row['created_at'])); ?></td>
              <td>
                          <?php
                          $statusToUse = !empty($row['latest_tracking_status']) ? $row['latest_tracking_status'] : ($row['status'] ?? 'Pending');
                          $lower = strtolower(trim($statusToUse));
                          $statusClass = 'pending';
                          if ($lower === 'approved') $statusClass = 'approved';
                          elseif ($lower === 'rejected') $statusClass = 'rejected';
                          elseif (!empty($row['latest_tracking_status'])) $statusClass = 'admin-updated';
                          ?>
                          <span class="status <?= $statusClass; ?>"><?= htmlspecialchars(ucfirst($statusToUse)); ?></span>
                <?php if (!empty($row['latest_admin_remark'])): ?>
                  <div class="admin-update-row"><span class="reason-text"><?= htmlspecialchars($row['latest_admin_remark']); ?></span></div>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <div style="text-align:center; padding:50px 0; color:#ccc; font-size:18px;">
        <strong>No Transactions found</strong>
      </div>
    <?php endif; ?>
    </tbody>
    </table>
    <p id="noResultsMessage" style="text-align:center; color:rgba(255, 255, 255, 0.77);; font-size:16px; margin-top:20px; display:none;"></p>
  </div>

  <!-- Modal -->
  <div id="statusModal" class="modal">
    <div class="modal-content">
      <span class="close">&times;</span>

        <?php if ($form): ?>
          <div class="request-status-box">
            <h3>Request Details</h3>
            <p><strong>Type:</strong> <?= htmlspecialchars($form['type'] ?? '—'); ?></p>
            <p><strong>Purpose:</strong> <?php
              $formPurpose = '';
              if ($form['type'] === 'Land Survey') {
                if (!empty($form['ls_purpose'])) {
                  if ($form['ls_purpose'] === 'Others' && !empty($form['ls_specify_text'])) {
                    $formPurpose = $form['ls_specify_text'];
                  } elseif ($form['ls_purpose'] === 'Others') {
                    $formPurpose = '';
                  } else {
                    $formPurpose = $form['ls_purpose'];
                  }
                }
              } elseif ($form['type'] === 'Sketch Plan') {
                if (!empty($form['sp_use'])) {
                  if ($form['sp_use'] === 'Others' && !empty($form['sp_specify_text'])) {
                    $formPurpose = $form['sp_specify_text'];
                  } elseif ($form['sp_use'] === 'Others') {
                    $formPurpose = '';
                  } else {
                    $formPurpose = $form['sp_use'];
                  }
                }
              }
              if (empty($formPurpose)) $formPurpose = $form['purpose'] ?? '—';
              echo htmlspecialchars($formPurpose);
            ?></p>
            <p><strong>Date Requested:</strong> <?= date("M d, Y H:i", strtotime($form['created_at'] ?? 'now')); ?></p>
            <hr style="border: 0.5px solid rgba(0,255,204,0.3); margin: 10px 0;">

            <h3>Request Status</h3>

            <?php
            $allRows = [];
            while ($r = $tracking->fetch_assoc()) {
              $allRows[] = $r;
            }

            if (count($allRows) === 0) {
              echo '<p style="text-align:center;color:#ccc;">No status updates yet.</p>';
            } else {
            $prevStatus = null;
            $prevSignature = null;
            foreach ($allRows as $row) {
              $s = strtolower(trim($row['status']));
              $remarks = trim($row['remarks'] ?? '');
              $dt = date_create($row['updated_at']);
              $dateStr = $dt ? date_format($dt, 'm/d/Y H:i') : $row['updated_at'];

              $sig = $s . '|' . $remarks;
              if ($prevSignature === $sig) {
                continue;
              }

              if (!empty($s)) {
                $showStatus = ($prevStatus === null || $prevStatus !== $s);
                if ($showStatus) {
                  echo '<div class="status-row">';
                  echo '<div class="status-item">';
                  if ($s === 'approved') {
                    echo '<span class="status-text approved">Approved</span>';
                  } elseif ($s === 'rejected') {
                    echo '<span class="status-text rejected">Rejected</span>';
                  } else {
                    echo '<span class="status-text pending">' . htmlspecialchars(ucfirst($row['status'])) . '</span>';
                  }
                  echo '</div>';
                  echo '<div class="status-date">' . htmlspecialchars($dateStr) . '</div>';
                  echo '</div>';
                  $prevStatus = $s;
                }
                if (!empty($remarks)) {
                  echo '<div class="admin-update-row"><span class="reason-text">' . htmlspecialchars($remarks) . '</span></div>';
                }
              } else {
                if (!empty($remarks)) {
                  echo '<div class="status-row">';
                  echo '<div class="status-item"><span class="update-label">Admin update</span></div>';
                  echo '<div class="update-date">' . htmlspecialchars($dateStr) . '</div>';
                  echo '</div>';
                  echo '<div class="admin-update-row"><span class="reason-text">' . htmlspecialchars($remarks) . '</span></div>';
                }
              }

              $prevSignature = $sig;
            }
            }
            ?>

          </div>
        <?php endif; ?>
    </div>

    <script>
      // 🔍 Live Search
      document.getElementById("search").addEventListener("input", function() {
        const query = this.value.toLowerCase().trim();
        const rows = document.querySelectorAll("#transactionTable tbody tr");
        const noResultsMessage = document.getElementById("noResultsMessage");
        let matchCount = 0;

        rows.forEach(row => {
          const text = row.textContent.toLowerCase();
          const match = text.includes(query);
          row.style.display = match ? "" : "none";
          if (match) matchCount++;
        });

        if (query && matchCount === 0) {
          noResultsMessage.textContent = `Search not found.`;
          noResultsMessage.style.display = "block";
        } else {
          noResultsMessage.style.display = "none";
        }
      });

      document.querySelectorAll(".clickable-row").forEach(row => {
        row.addEventListener("click", () => {
          const formId = row.dataset.id;
          window.location.href = `client-side_tracking.php?form_id=${formId}`;
        });
      });

      // 🪟 Modal
      const modal = document.getElementById("statusModal");
      const closeBtn = document.querySelector(".close");

      <?php if ($form_id): ?>
        modal.style.display = "block";
      <?php endif; ?>

      closeBtn.addEventListener("click", () => {
        modal.style.display = "none";
        window.history.replaceState(null, "", "client-side_tracking.php");
      });

      window.addEventListener("click", (e) => {
        if (e.target === modal) {
          modal.style.display = "none";
          window.history.replaceState(null, "", "client-side_tracking.php");
        }
      });
    </script>

</body>

</html>