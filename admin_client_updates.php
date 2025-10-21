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

    .search-container {
      display: flex;
      justify-content: flex-start;
      margin-bottom: 20px;
    }

    #searchUpdates {
      width: 280px;
      padding: 10px 14px;
      border-radius: 25px;
      border: 1px solid rgba(0, 255, 255, 0.5);
      background: rgba(255, 255, 255, 0.05);
      color: #00ffff;
      font-size: 14px;
      outline: none;
      transition: 0.3s;
    }

    #searchUpdates::placeholder {
      color: rgba(255, 255, 255, 0.77);
    }

    #searchUpdates:focus {
      border-color: #00ffff;
      box-shadow: 0 0 8px rgba(0, 255, 255, 0.4);
      background: rgba(0, 0, 0, 0.4);
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

    #rejectedSection {
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    #rejectedSection.show {
      opacity: 1;
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
    <div class="search-container">
      <input type="text" id="searchUpdates" placeholder="Search Updates...">
    </div>

    <?php if ($result && $result->num_rows > 0): ?>
      <table id="updatesTable">
        <thead>
          <tr>
            <th>Client Name</th>
            <th>Type</th>
            <th>Purpose</th>
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
            <?php
              // compute a display-friendly Purpose (mirror dashboard/tracking logic)
              $displayPurpose = '';
              if (!empty($details)) {
                if (($details['type'] ?? $row['request_type']) === 'Land Survey') {
                  if (!empty($details['ls_purpose'])) {
                    if ($details['ls_purpose'] === 'Others' && !empty($details['ls_specify_text'])) {
                      $displayPurpose = $details['ls_specify_text'];
                    } elseif ($details['ls_purpose'] === 'Others') {
                      $displayPurpose = '';
                    } else {
                      $displayPurpose = $details['ls_purpose'];
                    }
                  }
                } elseif (($details['type'] ?? $row['request_type']) === 'Sketch Plan') {
                  if (!empty($details['sp_use'])) {
                    if ($details['sp_use'] === 'Others' && !empty($details['sp_specify_text'])) {
                      $displayPurpose = $details['sp_specify_text'];
                    } elseif ($details['sp_use'] === 'Others') {
                      $displayPurpose = '';
                    } else {
                      $displayPurpose = $details['sp_use'];
                    }
                  }
                }
                if (empty($displayPurpose)) $displayPurpose = $details['purpose'] ?? '';
              }
            ?>
            <tr 
              data-id="<?= $row['id'] ?>" 
              data-name="<?= htmlspecialchars($row['client_name']) ?>"
              data-type="<?= htmlspecialchars($row['request_type']) ?>"
              data-purpose="<?= htmlspecialchars($displayPurpose) ?>"
              data-details='<?= htmlspecialchars($row['details'], ENT_QUOTES) ?>'
            >
              <td><?= htmlspecialchars($row['client_name']) ?></td>
              <td><?= htmlspecialchars($row['request_type']) ?></td>
              <td><?= htmlspecialchars($displayPurpose) ?></td>
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
              <td><?= date('m/d/Y, g:i a', strtotime($row['last_updated'])) ?></td>
              <td data-full="<?= htmlspecialchars($row['status']) ?>" title="<?= htmlspecialchars($row['status']) ?>">
                <?= truncateWords($row['status'], 20) ?>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
      <p id="noResultsMessage" style="text-align:center; color:rgba(255, 255, 255, 0.77);; font-size:16px; margin-top:20px; display:none;"></p>

    <?php else: ?>
      <p class="container-one">No Pending Updates found.</p>
    <?php endif; ?>

    <div class="button-row">
      <button id="toggleRejectedBtn">Show Rejected Requests</button>
    </div>

    <div id="rejectedSection" style="display:none;">
      <h2>Rejected Requests</h2>

      <?php
      if ($rejected_result && $rejected_result->num_rows > 0): ?>
        <table>
          <thead>
            <tr>
              <th>Client Name</th>
              <th>Type</th>
              <th>Files</th>
              <th>Reason</th>
              <th>Rejected At</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $rejected_result->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($row['client_name']); ?></td>
                <td><?= htmlspecialchars($row['type']); ?></td>
                <td>
                  <?php 
                    $files = [];
                    if (!empty($row['file_paths'])) {
                        $files = json_decode($row['file_paths'], true);
                        if (!is_array($files)) {
                            $files = explode(',', $row['file_paths']);
                        }
                    }

                    if (!empty($files)):
                        foreach ($files as $file):
                            $file = trim($file);
                            if ($file):
                  ?>
                    <a href="<?= htmlspecialchars($file) ?>" target="_blank" style="color: #00ffcc; text-decoration: none;">View File</a><br>
                  <?php 
                            endif;
                        endforeach;
                    else: 
                  ?>
                    <em>No files</em>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($row['reason']); ?></td>
                <td><?= date('F j, Y, g:i a', strtotime($row['rejected_at'])); ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      <?php else: ?>
      <p style="color:#00ffff; font-style:italic; margin-top:10px;">No rejected updates found.</p>
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
              <label>Purpose</label>
              <input type="text" id="editPurpose" name="purpose">
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
  document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById("editModal");
    const editForm = document.getElementById("editForm");
    const adminBody = document.getElementById("adminUpdatesBody");
    const toggleBtn = document.getElementById("toggleRejectedBtn");
    const rejectedSection = document.getElementById("rejectedSection");
    const searchInput = document.getElementById("searchUpdates");
    const updatesTable = document.getElementById("updatesTable");

    function truncateText(text, limit = 20) {
      const words = String(text || '').trim().split(/\s+/);
      return words.length > limit ? words.slice(0, limit).join(' ') + ' ...' : text || '';
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

    // Format datetime (mm/dd/YYYY, h:mm am/pm)
    function formatDateTime(input) {
      let date;
      if (!input) return '—';
      if (input instanceof Date) date = input;
      else date = new Date(String(input));
      if (isNaN(date.getTime())) return String(input);
      const mm = String(date.getMonth() + 1).padStart(2, '0');
      const dd = String(date.getDate()).padStart(2, '0');
      const yyyy = date.getFullYear();
      let hours = date.getHours();
      const minutes = String(date.getMinutes()).padStart(2, '0');
      const ampm = hours >= 12 ? 'pm' : 'am';
      hours = hours % 12;
      hours = hours ? hours : 12; // the hour '0' should be '12'
      return `${mm}/${dd}/${yyyy}, ${hours}:${minutes} ${ampm}`;
    }

    // Live search
    if (searchInput) {
      const noResultsMessage = document.getElementById('noResultsMessage');
      searchInput.addEventListener('input', function () {
        const query = this.value.toLowerCase().trim();
        const rows = document.querySelectorAll('#updatesTable tbody tr');
        let matchCount = 0;

        rows.forEach(row => {
          const cells = Array.from(row.querySelectorAll('td'));
          const rowText = cells.map(td => td.textContent.toLowerCase()).join(' ');
          const match = rowText.includes(query);
          row.style.display = match ? '' : 'none';
          if (match) matchCount++;
        });

        if (query && matchCount === 0) {
          noResultsMessage.textContent = `Search not found`;
          noResultsMessage.style.display = 'block';
        } else {
          noResultsMessage.style.display = 'none';
        }
      });
    }

    // Set title for long status cells
    document.querySelectorAll('#updatesTable td[data-full]').forEach(td => {
      td.title = td.getAttribute('data-full');
    });

    // Prevent link clicks from opening modal
    document.querySelectorAll('#updatesTable tbody tr a').forEach(a => {
      a.addEventListener('click', e => e.stopPropagation());
    });

    // Row click: open modal and load admin updates for that request
    if (updatesTable) {
      updatesTable.querySelectorAll('tbody tr').forEach(row => {
        row.addEventListener('click', () => {
          const id = row.dataset.id;
          const data = (() => { try { return JSON.parse(row.dataset.details || '{}'); } catch (e) { return {}; } })();

          document.getElementById('editId').value = id || '';
          document.getElementById('editDateProcessed').value = data.date_processed && data.date_processed !== '0000-00-00' ? data.date_processed : '';
          try {
            let clientNameValue = '';
            if (data.name && data.last_name) {
              const nameStr = String(data.name);
              const lastStr = String(data.last_name);
              if (nameStr.endsWith(lastStr)) {
                clientNameValue = nameStr;
              } else {
                clientNameValue = [nameStr, lastStr].filter(Boolean).join(' ');
              }
            } else {
              clientNameValue = (data.name || data.last_name || '');
            }
            document.getElementById('editClientName').value = clientNameValue;
          } catch (e) {
            document.getElementById('editClientName').value = [data.name, data.last_name].filter(Boolean).join(' ');
          }
          document.getElementById('editArea').value = data.ls_area || '';
          document.getElementById('editLot').value = data.ls_lot || '';
          document.getElementById('editLocation').value = data.ls_location || '';
          document.getElementById('editPurpose').value = data.purpose || row.dataset.purpose || '';
          document.getElementById('editType').value = data.type || '';
          document.getElementById('editSurveyPlanHidden').value = data.surveyplan || '';
          document.getElementById('editDescriptionHidden').value = data.description || '';
          // status is now in the last column (index 7)
          document.getElementById('editStatus').value = row.cells[7] ? row.cells[7].getAttribute('data-full') : '';

          // Load admin updates from server for this id
          loadAdminUpdates(id);

          // Restore any locally saved rows, but don't overwrite server-loaded rows.
          const saved = localStorage.getItem('updates_' + id);
          if (saved) {
            try {
              const placeholder = adminBody.querySelector('td[colspan]');
              if (placeholder) {
                adminBody.innerHTML = saved;
              } else {
                adminBody.insertAdjacentHTML('beforeend', saved);
              }
            } catch (e) {
              adminBody.innerHTML = saved;
            }
          }

          modal.style.display = 'flex';
        });
      });
    }

    function loadAdminUpdates(id) {
      if (!id) {
        adminBody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:#ccc">No updates</td></tr>';
        return;
      }
      adminBody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:#ccc">Loading...</td></tr>';
      fetch('get_admin_updates.php?id=' + encodeURIComponent(id))
        .then(res => res.json())
        .then(dbData => {
          adminBody.innerHTML = '';
          if (dbData && dbData.success && Array.isArray(dbData.updates) && dbData.updates.length) {
            dbData.updates.forEach(update => {
              const tr = document.createElement('tr');
              const d = update.date ? (update.date.indexOf(',') === -1 ? update.date : update.date) : update.date;
              const dateText = d ? formatDateTime(d) : '—';
              tr.innerHTML = `
                <td>${dateText}</td>
                <td>${update.status}</td>
                <td>${update.remarks}</td>
                <td>${update.expenses}</td>
              `;
              adminBody.appendChild(tr);
            });
          } else {
            adminBody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:#ccc">No updates</td></tr>';
          }
        })
        .catch(err => {
          console.error('loadAdminUpdates error', err);
          adminBody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:#f88">Failed to load</td></tr>';
        });
    }

  function closeModal() { if (modal) modal.style.display = 'none'; }
  window.closeModal = closeModal;

    // Add new remark row
    const addRowBtn = document.querySelector('.addRowBtn');
    if (addRowBtn) {
      addRowBtn.addEventListener('click', () => {
        const newStatusEl = document.getElementById('newStatus');
        const newRemarksEl = document.getElementById('newRemarks');
        const newExpensesEl = document.getElementById('newExpenses');
        const editIdEl = document.getElementById('editId');
        const status = newStatusEl ? newStatusEl.value.trim() : '';
        const remarks = newRemarksEl ? newRemarksEl.value.trim() : '';
        const expenses = newExpensesEl ? newExpensesEl.value.trim() : '';
        const currentId = editIdEl ? editIdEl.value : '';
        if (!status && !remarks && !expenses) return;
        const tr = document.createElement('tr');
        const nowStr = formatDateTime(new Date());
        tr.innerHTML = `
          <td>${nowStr}</td>
          <td>${status || '—'}</td>
          <td>${remarks || '—'}</td>
          <td>${expenses || '—'}</td>
        `;
        if (adminBody) {
          adminBody.querySelectorAll('tr').forEach(r => {
            const td = r.querySelector('td[colspan]');
            if (td) {
              const txt = (td.textContent || '').toLowerCase();
              if (txt.includes('no updates') || txt.includes('loading')) r.remove();
            }
          });
          adminBody.appendChild(tr);
        }
        if (status && document.getElementById('editStatus')) document.getElementById('editStatus').value = status;
        try { localStorage.setItem('updates_' + currentId, adminBody ? adminBody.innerHTML : ''); } catch (e) { /* ignore storage errors */ }
        if (newStatusEl) newStatusEl.value = '';
        if (newRemarksEl) newRemarksEl.value = '';
        if (newExpensesEl) newExpensesEl.value = '';
      });
    }

    function showNotification(message, type = 'success') {
      const old = document.getElementById('notification-bar'); if (old) old.remove();
      const n = document.createElement('div'); n.id = 'notification-bar'; n.textContent = message;
      n.style.position = 'fixed'; n.style.top = '-60px'; n.style.left = '50%'; n.style.transform = 'translateX(-50%)';
      n.style.background = type === 'success' ? '#00b894' : '#d63031'; n.style.color = '#fff';
      n.style.padding = '14px 30px'; n.style.borderRadius = '8px'; n.style.boxShadow = '0 2px 10px rgba(0,0,0,0.2)';
      n.style.fontSize = '16px'; n.style.fontWeight = '500'; n.style.transition = 'top 0.5s ease, opacity 0.5s ease'; n.style.opacity = '0.95'; n.style.zIndex = '9999';
      document.body.appendChild(n);
      setTimeout(() => { n.style.top = '20px'; }, 50);
      setTimeout(() => { n.style.top = '-60px'; n.style.opacity = '0'; setTimeout(() => n.remove(), 500); }, 3000);
    }

    // Save button
    if (editForm) {
      editForm.addEventListener('submit', e => {
        e.preventDefault();
        const formData = new FormData(editForm);
        const id = document.getElementById('editId') ? document.getElementById('editId').value : '';
        const rows = adminBody ? [...adminBody.querySelectorAll('tr')] : [];
        formData.append('updates', JSON.stringify(rows.map(tr => ({
          date: tr.cells[0] ? tr.cells[0].textContent : '',
          status: tr.cells[1] ? tr.cells[1].textContent : '',
          remarks: tr.cells[2] ? tr.cells[2].textContent : '',
          expenses: tr.cells[3] ? tr.cells[3].textContent : ''
        }))));
        // ensure id is present
        formData.append('id', id);
        fetch('update_pending_status.php', { method: 'POST', body: formData })
          .then(res => res.text().then(text => ({ ok: res.ok, status: res.status, text })))
          .then(({ ok, status, text }) => {
            let data;
            try {
              data = JSON.parse(text || '{}');
            } catch (parseErr) {
              console.error('update_pending_status returned non-JSON:', text);
              showNotification('Server error: see console for details.', 'error');
              return;
            }
            if (data.success) {
              const row = document.querySelector(`tr[data-id='${id}']`);
              const fullStatus = data.updated_status || (document.getElementById('editStatus') ? document.getElementById('editStatus').value : '');
              if (row) {
                row.cells[0].textContent = data.updated_name || row.cells[0].textContent;
                row.cells[1].textContent = formData.get('type') || row.cells[1].textContent;
                if (row.cells[2]) row.cells[2].textContent = formData.get('purpose') || row.dataset.purpose || row.cells[2].textContent;
                row.cells[3].textContent = formData.get('transaction_number') || row.cells[3].textContent;
                row.cells[5].textContent = dateToReadable(data.updated_date_processed) || row.cells[5].textContent;
                row.cells[6].textContent = data.last_updated ? formatDateTime(data.last_updated) : row.cells[6].textContent;
                if (row.cells[7]) {
                  row.cells[7].textContent = truncateText(fullStatus, 20);
                  row.cells[7].setAttribute('data-full', fullStatus);
                  row.cells[7].title = fullStatus;
                }
                const oldData = JSON.parse(row.dataset.details || '{}');
                const updatedDetails = Object.assign({}, oldData, {
                  name: formData.get('client_name') || oldData.name,
                  ls_area: formData.get('area') || oldData.ls_area,
                  ls_lot: formData.get('lot') || oldData.ls_lot,
                  ls_location: formData.get('location') || oldData.ls_location,
                  type: formData.get('type') || oldData.type,
                  surveyplan: formData.get('surveyplan') || oldData.surveyplan,
                  description: formData.get('description') || oldData.description,
                  date_processed: formData.get('date_processed') || oldData.date_processed,
                  purpose: formData.get('purpose') || oldData.purpose || row.dataset.purpose || ''
                });
                row.dataset.details = JSON.stringify(updatedDetails);
                row.dataset.purpose = updatedDetails.purpose || '';
              }
              try { localStorage.removeItem('updates_' + id); } catch (e) { /* ignore storage errors */ }
              loadAdminUpdates(id);
              showNotification('Saved successfully!', 'success');
              closeModal();
            } else {
              console.error('update_pending_status response:', data);
              const msg = data && data.error ? data.error : 'Failed to save. Please try again.';
              showNotification(msg, 'error');
            }
          })
          .catch(err => { console.error('Fetch error:', err); showNotification('Network error: check console.', 'error'); });
      });
    } else {
      console.warn('editForm not found on this page; save handler not attached.');
    }

    // Toggle rejected section
    if (toggleBtn && rejectedSection) {
      toggleBtn.addEventListener('click', function () {
        const isHidden = rejectedSection.style.display === 'none' || rejectedSection.style.display === '';
        rejectedSection.style.display = isHidden ? 'block' : 'none';
        rejectedSection.classList.toggle('show', isHidden);
        this.textContent = isHidden ? 'Hide Rejected Requests' : 'Show Rejected Requests';
      });
    }

    // Close controls
    document.getElementById('closeUpdateModal') && document.getElementById('closeUpdateModal').addEventListener('click', closeModal);
    if (modal) {
      modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
    }
  });
  </script>

  </body>
</html>

<?php $conn->close(); ?>