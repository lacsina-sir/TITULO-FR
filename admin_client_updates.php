<?php
// DB connection
$host = "localhost";
$user = "root";
$password = "";
$dbname = "titulo_db";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Fetch pending updates
$sql = "SELECT * FROM pending_updates ORDER BY last_updated DESC";
$result = $conn->query($sql);

// Fetch rejected requests
$rejected_sql = "SELECT * FROM rejected_requests ORDER BY rejected_at DESC";
$rejected_result = $conn->query($rejected_sql);

// Truncate long text to first 20 words
function truncateWords($text, $limit = 20) {
    $words = preg_split('/\s+/', trim($text));
    return count($words) > $limit ? implode(' ', array_slice($words, 0, $limit)) . ' ...' : $text;
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
  <meta charset="UTF-8">
  <title>Client Updates | Admin Panel</title>
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

    .container {
      margin-left: 240px;
      padding: 80px 20px 20px;
      width: calc(100% - 240px);
    }

    h1 {
      font-size: 28px;
      margin-bottom: 20px;
      border-bottom: 2px solid #00ffff;
      padding-bottom: 10px;
    }

    .search-bar input {
      padding: 10px;
      width: 300px;
      border-radius: 5px;
      border: none;
      margin-bottom: 20px;
    }

    /* TABLE */
    table {
      width: 100%;
      border-collapse: collapse;
      background-color: #1f2b38;
      border-radius: 10px;
      overflow: hidden;
      border: 1px solid #00ffff;
    }

    thead {
      background-color: #00bcd4;
      color: #000;
    }

    th {
      padding: 12px 15px;
      text-align: center;
      border: 1px solid #00ffff;
    }

    
    td {
      padding: 12px 15px;
      text-align: left;
      border: 1px solid #00ffff;
    }

    tbody tr:nth-child(even) {
      background-color: #263646;
    }

    tbody tr:hover {
      background-color: #33475b;
      cursor: pointer;
    }

    /* BUTTONS */
    #toggleRejectedBtn {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: none;
      color: #00ffff;
      border: 2px solid #00ffff;
      padding: 8px 16px;
      font-size: 14px;
      border-radius: 5px;
      cursor: pointer;
      transition: all 0.3s ease;
      margin-top: 40px; /* creates space below the table */
    }

    #toggleRejectedBtn:hover {
      background-color: #00ffff;
      color: #000;
      box-shadow: 0 0 8px rgba(0, 255, 255, 0.6);
    }

    /* MODAL */
    #editModal {
      display: none;
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0, 0, 0, 0.75);
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }

    .modal-content {
      background-color: #1f2b38;
      padding: 25px;
      border-radius: 10px;
      width: 900px;
      box-shadow: 0 0 25px rgba(0,255,255,0.3);
      animation: fadeIn 0.3s ease-in-out;
    }

    .form-top {
      display: flex;
      gap: 30px;
      margin-bottom: 20px;
    }

    .left-fields {
      flex: 1;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 15px 20px;
    }

    .field-group label {
      font-weight: bold;
      font-size: 13px;
      color: #00ffff;
    }

    .field-group input {
      width: 100%;
      padding: 8px;
      border: 1px solid #00ffff;
      border-radius: 5px;
      background: #0e1a24;
      color: #fff;
    }

    .right-status {
      flex: 0.8;
      display: flex;
      flex-direction: column;
    }

    .right-status label {
      font-weight: bold;
      margin-bottom: 5px;
      color: #00ffff;
    }

    .right-status textarea {
      flex: 1;
      resize: none;
      background: #0e1a24;
      border: 1px solid #00ffff;
      border-radius: 5px;
      color: #fff;
      padding: 8px;
    }

    /* Admin mini table */
    .admin-table-section {
      max-height: 300px;
      overflow-y: auto;
    }

    /* Admin mini table */
    #adminUpdatesTable {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 10px;
      table-layout: fixed;
    }

    #adminUpdatesTable td {
      max-width: 150px; /* adjust as needed */
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    #updatesTable td[data-full] {
      max-width: 200px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }


    #adminUpdatesTable td:hover {
      white-space: normal;
    }

    #adminUpdatesTable th,
    #adminUpdatesTable td {
      border: 1px solid #00ffff;
      padding: 8px;
      font-size: 13px;
      text-align: left;
      word-wrap: break-word;
    }

    #adminUpdatesTable th:nth-child(1),
    #adminUpdatesTable td:nth-child(1) {
      width: 22%; /* Date of update */
    }

    #adminUpdatesTable th:nth-child(2),
    #adminUpdatesTable td:nth-child(2),
    #adminUpdatesTable th:nth-child(3),
    #adminUpdatesTable td:nth-child(3),
    #adminUpdatesTable th:nth-child(4),
    #adminUpdatesTable td:nth-child(4) {
      width: 26%;
    }

    /* Inputs aligned perfectly under each column */
    .admin-inputs {
      display: grid;
      grid-template-columns: 26% 26% 26% 26%;
      gap: 10px;
      margin-bottom: 10px;
    }

    .admin-inputs input {
      padding: 8px;
      border: 1px solid #00ffff;
      background: #0e1a24;
      border-radius: 5px;
      color: #fff;
      font-size: 13px;
      box-sizing: border-box;
    }

    .addRowBtn {
      background: #00ffff;
      color: #000;
      border: none;
      padding: 6px 10px;
      border-radius: 5px;
      cursor: pointer;
      font-size: 13px;
      justify-self: start;
      margin-left: 125px; 
    }

    .addRowBtn:hover, .save-btn:hover {
      box-shadow: 0 0 8px #00ffff;
    }

    .modal-buttons {
      text-align: right;
      margin-top: 15px;
    }

    .modal-buttons button {
      padding: 8px 15px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      margin-left: 8px;
    }

    .save-btn {
      background: #00ffff;
      color: #000;
    }

    .cancel-btn {
      background: #555;
      color: #fff;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: scale(0.95); }
      to { opacity: 1; transform: scale(1); }
    }
    </style>
  </head>
  <body>

  <div class="sidebar">
    <h2>Titulo Admin</h2>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="admin_client_request.php">Client Requests</a>
    <a href="admin_client_updates.php" class="active">Client Updates</a>
    <a href="transaction_files.php">Survey Files</a>
    <a href="admin_chat.php">Chat</a>
    <a href="index.php">Logout</a>
  </div>

  <div class="container">
    <h1>Client Updates</h1>
    <div class="search-bar">
      <input type="text" id="search" placeholder="Search pending updates...">
    </div>

    <?php if ($result && $result->num_rows > 0): ?>
      <table id="updatesTable">
        <thead>
          <tr>
            <th>Client Name</th>
            <th>Type</th>
            <th>Transaction #</th>
            <th>Files</th>
            <th>Date Processed</th>
            <th>Last Updated</th>
            <th>Current Status</th>
          </tr>
        </thead>

        <tbody>
          <?php while ($row = $result->fetch_assoc()): ?>
            <?php 
              $details = json_decode($row['details'], true); 
              $dateProcessed = isset($details['date_processed']) && strtotime($details['date_processed']) 
                ? date('m/d/Y', strtotime($details['date_processed'])) 
                : '—';
            ?>
            <tr 
              data-id="<?= $row['id'] ?>" 
              data-name="<?= htmlspecialchars($row['client_name']) ?>"
              data-type="<?= htmlspecialchars($row['request_type']) ?>"
              data-details='<?= htmlspecialchars($row['details'], ENT_QUOTES) ?>'
            >
              <td><?= htmlspecialchars($row['client_name']) ?></td>
              <td><?= htmlspecialchars($row['request_type']) ?></td>
              <td><?= htmlspecialchars($row['transaction_number']) ?></td>

              <!-- FILES COLUMN -->
              <td>
                <?php 
                  $files = [];
                  if (!empty($details['file_paths'])) {
                      $files = json_decode($details['file_paths'], true);
                      if (!is_array($files)) {
                          $files = explode(',', $details['file_paths']);
                      }
                  }

                  if (!empty($files)):
                      foreach ($files as $file):
                          $file = trim($file);
                          if ($file):
                ?>
                <a href="<?= htmlspecialchars($file) ?>" 
                  target="_blank" 
                  style="color: #00ffcc; text-decoration: none;" 
                  onclick="event.stopPropagation();">
                  View File
                </a><br>
                <?php 
                          endif;
                      endforeach;
                  else: 
                ?>
                    <em>No files</em>
                <?php endif; ?>
              </td>

              <td><?= $dateProcessed ?></td>
              <td><?= date('F j, Y, g:i a', strtotime($row['last_updated'])) ?></td>
              <td data-full="<?= htmlspecialchars($row['status']) ?>" title="<?= htmlspecialchars($row['status']) ?>">
                <?= truncateWords($row['status'], 20) ?>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p class="container-one">No pending updates found.</p>
    <?php endif; ?>

    <div class="button-row">
      <button id="toggleRejectedBtn">Show Rejected Requests</button>
    </div>

    <div id="rejectedSection" class="container-one" style="display:none;">
      <h2>Rejected Requests</h2>
      <?php if ($rejected_result && $rejected_result->num_rows > 0): ?>
        <table>
          <thead>
            <tr>
              <th>Client Name</th>
              <th>Type</th>
              <th>Reason</th>
              <th>Rejected At</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($r = $rejected_result->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($r['client_name']) ?></td>
              <td><?= htmlspecialchars($r['type']) ?></td>
              <td><?= htmlspecialchars($r['reason']) ?></td>
              <td><?= date('F j, Y, g:i a', strtotime($r['rejected_at'])) ?></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p>No rejected requests found.</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Edit Modal -->
  <div id="editModal">
    <div class="modal-content">
      <h2>Edit Client Update</h2>

      <form id="editForm">
        <input type="hidden" id="editId" name="id">

        <div class="form-top">
          <div class="left-fields">
            <div class="field-group">
              <label>Date Processed</label>
              <input type="date" id="editDateProcessed" name="date_processed">
            </div>
            <div class="field-group">
              <label>Name</label>
              <input type="text" id="editClientName" name="client_name">
            </div>
            <div class="field-group">
              <label>Location</label>
              <input type="text" id="editLocation" name="location">
            </div>
            <div class="field-group">
              <label>Area</label>
              <input type="text" id="editArea" name="area">
            </div>
            <div class="field-group">
              <label>Lot #</label>
              <input type="text" id="editLot" name="lot">
            </div>
            <div class="field-group">
              <label>Type</label>
              <input type="text" id="editType" name="type">
            </div>
            <div class="field-group">
              <label>Survey Plan</label>
              <input type="text" id="editSurveyPlanHidden" name="surveyplan">
            </div>
            <div class="field-group">
              <label>Description</label>
              <input type="text" id="editDescriptionHidden" name="description">
            </div>
          </div>

          <div class="right-status">
            <label>Current Status</label>
            <textarea id="editStatus" name="status" rows="10" readonly></textarea>
          </div>
        </div>

        <!-- Admin table for remarks & expenses -->
        <div class="admin-table-section" style="overflow-x:auto; max-width:100%;">
          <table id="adminUpdatesTable">
            <thead>
              <tr>
                <th>Date of Update</th>
                <th>Status</th>
                <th>Remarks</th>
                <th>Expenses</th>
              </tr>
            </thead>
            <tbody id="adminUpdatesBody">
              <!-- rows will appear dynamically -->
            </tbody>
          </table>
        </div>

        <div class="admin-inputs">
          <input type="text" id="newStatus" placeholder="Status">
          <input type="text" id="newRemarks" placeholder="Remarks">
          <input type="text" id="newExpenses" placeholder="Expenses">
          <button type="button" class="addRowBtn">Add</button>
        </div>

        <div class="modal-buttons">
          <button type="button" class="cancel-btn" onclick="closeModal()">Cancel</button>
          <button type="submit" class="save-btn">Save</button>
        </div>
      </form>
    </div>
  </div>

  <script>
  const modal = document.getElementById("editModal");
  const editForm = document.getElementById("editForm");
  const adminBody = document.getElementById("adminUpdatesBody");

  function truncateText(text, limit = 20) {
    const words = text.trim().split(/\s+/);
    return words.length > limit ? words.slice(0, limit).join(' ') + ' ...' : text;
  }
  
  function dateToReadable(dateStr) {
    if (!dateStr || dateStr === "—" || dateStr === "0000-00-00") return "—";

    const date = new Date(dateStr);
    if (isNaN(date.getTime())) return "—";

    const mm = String(date.getMonth() + 1).padStart(2, '0');
    const dd = String(date.getDate()).padStart(2, '0');
    const yyyy = date.getFullYear();

    return `${mm}/${dd}/${yyyy}`;
  }

  document.getElementById("search").addEventListener("input", function () {
    const query = this.value.toLowerCase().trim();
    const rows = document.querySelectorAll("#updatesTable tbody tr");

    rows.forEach(row => {
      const name = row.dataset.name?.toLowerCase() || '';
      const type = row.dataset.type?.toLowerCase() || '';
      const match = name.includes(query) || type.includes(query);
      row.style.display = match ? "" : "none";
    });
  });

  document.querySelectorAll("#updatesTable td[data-full]").forEach(td => {
    td.title = td.getAttribute("data-full");
  });

  document.querySelectorAll("#updatesTable tbody tr").forEach(row => {
    row.addEventListener("click", () => {
      const id = row.dataset.id;
      const data = JSON.parse(row.dataset.details);
      const saved = localStorage.getItem("updates_" + id);

      document.getElementById("editId").value = id;
      document.getElementById("editDateProcessed").value = 
          data.date_processed && data.date_processed !== "0000-00-00" ? data.date_processed : '';
      document.getElementById("editClientName").value = [data.name, data.last_name].filter(Boolean).join(" ");
      document.getElementById("editArea").value = data.ls_area || '';
      document.getElementById("editLot").value = data.ls_lot || '';
      document.getElementById("editLocation").value = data.ls_location || '';
      document.getElementById("editType").value = data.type || '';
      document.getElementById("editSurveyPlanHidden").value = data.surveyplan || '';
      document.getElementById("editDescriptionHidden").value = data.description || '';
      document.getElementById("editStatus").value = row.cells[6].getAttribute('data-full');

      // 🧠 Load saved table from localStorage (if exists)
      adminBody.innerHTML = saved || ""; // restore old rows

      modal.style.display = "flex";
    });
  });

  function closeModal() {
    modal.style.display = "none";
  }

  // Add new remark row
  document.querySelector(".addRowBtn").addEventListener("click", () => {
    const status = document.getElementById("newStatus").value.trim();
    const remarks = document.getElementById("newRemarks").value.trim();
    const expenses = document.getElementById("newExpenses").value.trim();
    const currentId = document.getElementById("editId").value;

    if (!status && !remarks && !expenses) return;

    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td>${new Date().toLocaleString()}</td>
      <td>${status || "—"}</td>
      <td>${remarks || "—"}</td>
      <td>${expenses || "—"}</td>
    `;
    adminBody.appendChild(tr);

    if (status) {
      document.getElementById("editStatus").value = status;
    }

    // Save updates locally per client
    localStorage.setItem("updates_" + currentId, adminBody.innerHTML);

    document.getElementById("newStatus").value = '';
    document.getElementById("newRemarks").value = '';
    document.getElementById("newExpenses").value = '';
  });

  function showNotification(message, type = "success") {
    // Remove any existing notification first
    const oldNotif = document.getElementById("notification-bar");
    if (oldNotif) oldNotif.remove();

    const notif = document.createElement("div");
    notif.id = "notification-bar";
    notif.textContent = message;

    // Basic styling
    notif.style.position = "fixed";
    notif.style.top = "-60px";
    notif.style.left = "50%";
    notif.style.transform = "translateX(-50%)";
    notif.style.background = type === "success" ? "#00b894" : "#d63031"; // blue-green or red
    notif.style.color = "#fff";
    notif.style.padding = "14px 30px";
    notif.style.borderRadius = "8px";
    notif.style.boxShadow = "0 2px 10px rgba(0,0,0,0.2)";
    notif.style.fontSize = "16px";
    notif.style.fontWeight = "500";
    notif.style.transition = "top 0.5s ease, opacity 0.5s ease";
    notif.style.opacity = "0.95";
    notif.style.zIndex = "9999";
    document.body.appendChild(notif);

    // Slide down animation
    setTimeout(() => { notif.style.top = "20px"; }, 50);

    // Auto hide
    setTimeout(() => {
      notif.style.top = "-60px";
      notif.style.opacity = "0";
      setTimeout(() => notif.remove(), 500);
    }, 3000);
  }

  // Save button
  editForm.addEventListener("submit", e => {
    e.preventDefault();
    const formData = new FormData(editForm);
    formData.append("updates", JSON.stringify([...adminBody.querySelectorAll("tr")].map(tr => ({
      date: tr.cells[0].textContent,
      status: tr.cells[1].textContent,
      remarks: tr.cells[2].textContent,
      expenses: tr.cells[3].textContent
    }))));

    fetch("update_pending_status.php", {
      method: "POST",
      body: formData
    })
    .then(res => {
      if (!res.ok) throw new Error("Network response was not ok");
      return res.json();
    })
    .then(data => {
      if (data.success) {
        // ✅ Update the table row
        const row = document.querySelector(`tr[data-id='${formData.get("id")}']`);
        const fullStatus = data.updated_status || document.getElementById("editStatus").value;

        row.cells[0].textContent = data.updated_name;
        row.cells[1].textContent = formData.get("type");
        row.cells[4].textContent = dateToReadable(data.updated_date_processed);
        row.cells[5].textContent = dateToReadable(data.last_updated) || "—";
        row.cells[6].textContent = truncateText(fullStatus, 20);
        row.cells[6].setAttribute('data-full', fullStatus);
        row.cells[6].title = fullStatus;

        // ✅ Update dataset
        const oldData = JSON.parse(document.querySelector(`tr[data-id='${formData.get("id")}']`).dataset.details || "{}");
        const updatedDetails = {
          name: formData.get("client_name"),
          ls_area: formData.get("area"),
          ls_lot: formData.get("lot"),
          ls_location: formData.get("location"),
          type: formData.get("type"),
          surveyplan: formData.get("surveyplan"),
          description: formData.get("description"),
          date_processed: formData.get("date_processed"),
          file_paths: oldData.file_paths || [] // 🟢 Keep existing files!
        };

        row.dataset.details = JSON.stringify(updatedDetails);

        showNotification("Saved successfully!", "success");
        closeModal();
      } else {
        showNotification("Failed to save. Please try again.", "error");
      }
    })
    .catch(err => {
      console.error("Fetch error:", err);
      showNotification("Something went wrong. Check console.", "error");
    });
  });

  fetch(`get_admin_updates.php?id=${id}`)
  .then(res => res.json())
  .then(dbData => {
    if (dbData.success && Array.isArray(dbData.updates)) {
      adminBody.innerHTML = "";
      dbData.updates.forEach(update => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
          <td>${update.date}</td>
          <td>${update.status}</td>
          <td>${update.remarks}</td>
          <td>${update.expenses}</td>
        `;
        adminBody.appendChild(tr);
      });
    }
  });

  document.getElementById("toggleRejectedBtn").addEventListener("click", function(){
    const section = document.getElementById("rejectedSection");
    if (section.style.display === "none") {
      section.style.display = "block";
      this.textContent = "Hide Rejected Requests";
    } else {
      section.style.display = "none";
      this.textContent = "Show Rejected Requests";
    }
  });
  </script>

  </body>
</html>

<?php $conn->close(); ?>
