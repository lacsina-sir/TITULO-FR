<?php
// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$db = "titulo_db";
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle delete request
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM survey_files WHERE id = $id");
    header("Location: transaction_files.php");
    exit;
}

// Fetch survey files
$search = "";
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
    $search = $conn->real_escape_string($search);
    $query = "
        SELECT * FROM survey_files 
        WHERE client_name LIKE '%$search%' 
          OR location LIKE '%$search%'
          OR survey_type LIKE '%$search%'
        ORDER BY date_submitted DESC
    ";
} else {
    $query = "SELECT * FROM survey_files ORDER BY date_submitted DESC";
}

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Survey Files | Admin Panel</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(to bottom, #0f2027, #203a43, #2c5364);
      color: #fff;
      height: 100vh;
    }

    .navbar {
      background-color: #1a1a1a;
      padding: 15px;
      color: white;
      text-align: center;
      font-size: 22px;
      font-weight: bold;
      position: fixed;
      width: 100%;
      top: 0;
      z-index: 1000;
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
    .sidebar a:hover{
      background-color: #333;
      color: #fff;
    }
    .sidebar a.active {
      background-color: #00ffff;
      color: black;
    }

    .container {
      margin-left: 220px;
      padding: 80px 20px 20px;
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }

    .header h1 {
      font-size: 26px;
      border-bottom: 2px solid #00ffff;
      padding-bottom: 8px;
      margin: 0;
    }

    #searchBar {
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

    #searchBar::placeholder {
      color: rgba(255, 255, 255, 0.77);
    }

    #searchBar:focus {
      border-color: #00ffff;
      box-shadow: 0 0 8px rgba(0, 255, 255, 0.4);
      background: rgba(0, 0, 0, 0.4);
    }

    .no-data-row {
      text-align: center;
      padding: 40px 0;
      color: #ccc;
      font-size: 16px;
    }

    .table-container {
      background: #1f2b38;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 10px rgba(0, 255, 255, 0.1);
    }

    table {
      width: 100%;
      border-collapse: collapse;
      color: #fff;
      border: 1px solid #00bcd4;
      border-radius: 8px;
      overflow: hidden;
    }

    table th, table td {
      border: 1px solid rgba(0, 255, 255, 0.3);
      text-align: center;
      padding: 12px;
    }

    table th {
      background-color: #00bcd4;
      color: #000;
      font-weight: bold;
    }

    table tr:nth-child(even) {
      background-color: #263646;
    }

    table tr:nth-child(odd) {
      background-color: #1f2b38;
    }

    table tr:hover {
      background-color: #33475b;
    }
    
    table th, table td {
      border: 1px solid #00ffff;
    }

  </style>
</head>
<body>

  <div class="sidebar">
    <h2>Titulo Admin</h2>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="admin_client_request.php">Client Requests</a>
    <a href="admin_client_updates.php">Client Updates</a>
    <a href="transaction_files.php" class="active">Survey Files</a>
    <a href="admin_chat.php">Chat</a>
    <a href="index.php">Logout</a>
  </div>

  <div class="container">
    <div class="header">
      <h1>Survey Files</h1>
      <div style="display: flex; gap: 10px; align-items: center;">
        <div class="search-box">
          <input type="text" id="searchBar" placeholder="Search survey files..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <button onclick="openAddForm()" style="padding: 10px 16px; font-size: 16px; background-color: #00bcd4; color: #000; border: none; border-radius: 6px; cursor: pointer;">+</button>
      </div>
    </div>


    <div class="table-container">
      <table id="surveyTable">
        <thead>
          <tr>
            <th>Transaction #</th>
            <th>Client Name</th>
            <th>Survey Type</th>
            <th>Location</th>
            <th>Status</th>
            <th>Date Submitted</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
              // Split client name into first and last name
              $nameParts = explode(' ', $row['client_name'], 2);
              $firstName = $nameParts[0];
              $lastName = isset($nameParts[1]) ? $nameParts[1] : '';

              // Prepare row data
              $rowData = htmlspecialchars(json_encode([
                'id' => $row['id'],
                'first_name' => explode(' ', $row['client_name'])[0],
                'last_name' => explode(' ', $row['client_name'])[1] ?? '',
                'survey_type' => $row['survey_type'],
                'location' => $row['location'],
                'status' => $row['status'],
                'date_submitted' => $row['date_submitted']
              ]));

              echo "<tr>";
              echo "<td>" . htmlspecialchars($row['transaction_number'] ?? '—') . "</td>";
              echo "<td>" . htmlspecialchars($row['client_name']) . "</td>";
              echo "<td>" . htmlspecialchars($row['survey_type']) . "</td>";
              echo "<td>" . htmlspecialchars($row['location']) . "</td>";
              echo "<td>" . htmlspecialchars($row['status']) . "</td>";
              echo "<td>" . htmlspecialchars($row['date_submitted']) . "</td>";
              echo "<td style='text-align:center;'>
                <div style='display:inline-flex; gap:20px; justify-content:center; align-items:center;'>
                  
                  <!-- Edit button -->
                  <button class='edit-btn'
                    data-info='$rowData'
                    title='Edit'
                    style='background:none; border:none; cursor:pointer; padding:0; position:relative; top:2px;'>
                    <svg xmlns='http://www.w3.org/2000/svg' width='18' height='20' fill='white' viewBox='0 0 16 16'>
                      <path d='M12.146.854a.5.5 0 0 1 .708 0l2.292 2.292a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-4 1.5a.5.5 0 0 1-.65-.65l1.5-4a.5.5 0 0 1 .11-.168l10-10zM11.207 2L2 11.207V13h1.793L14 3.793 11.207 2z'/>
                    </svg>
                  </button>
                  
                  <!-- Delete link -->
                  <a href='?delete={$row['id']}'
                    onclick='return confirm(\"Are you sure you want to delete this survey file?\")'
                    title='Delete'
                    style='background:none; border:none; cursor:pointer; padding:0; display:inline-flex; align-items:center; justify-content:center; text-decoration:none;'>
                    <svg xmlns='http://www.w3.org/2000/svg' width='18' height='20' fill='white' viewBox='0 0 16 16'>
                      <path d='M5.5 5.5a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0v-6a.5.5 0 0 1 .5-.5zm2.5.5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0v-6zm2 .5a.5.5 0 0 1 .5-.5v6a.5.5 0 0 1-1 0v-6a.5.5 0 0 1 .5-.5z'/>
                      <path fill-rule='evenodd' d='M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1 0-2h3a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1h3a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3a.5.5 0 0 0 0 1H13.5a.5.5 0 0 0 0-1H2.5z'/>
                    </svg>
                  </a>

                </div>
              </td>";
              echo "</tr>";
                }
              } else {
              echo "<tr><td colspan='6' style='text-align:center; padding:40px 0; color:#ccc; font-size:16px;'>No Survey Files found</td></tr>";
              }
          ?>
        </tbody>
      </table>
      <p id="noResultsMessage" style="text-align:center; color:rgba(255, 255, 255, 0.77);; font-size:16px; margin-top:20px; display:none;"></p>
    </div>
  </div>

  <!-- Add/Edit Popup Form -->
  <div id="popupForm" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); justify-content:center; align-items:center; z-index:2000;">
    <div style="background:#1f2b38; padding:30px; border-radius:12px; width:400px; box-shadow:0 0 20px rgba(0,255,255,0.3);">
      <h2 style="color:#00bcd4; margin-bottom:20px;" id="formTitle">Add Survey File</h2>
      <form id="surveyForm">
        <input type="hidden" name="id" id="formId">
        <div style="margin-bottom:12px;">
          <label>Transaction #:</label><br>
          <input type="text" name="transaction_number" id="transaction_number" required style="width:100%; padding:8px; border-radius:6px; border:none;">
        </div>
        <div style="margin-bottom:12px;">
          <label>First Name:</label><br>
          <input type="text" name="first_name" id="first_name" required style="width:100%; padding:8px; border-radius:6px; border:none;">
        </div>
        <div style="margin-bottom:12px;">
          <label>Last Name:</label><br>
          <input type="text" name="last_name" id="last_name" required style="width:100%; padding:8px; border-radius:6px; border:none;">
        </div>
        <div style="margin-bottom:12px;">
          <label>Survey Type:</label><br>
          <input type="text" name="survey_type" id="survey_type" required style="width:100%; padding:8px; border-radius:6px; border:none;">
        </div>
        <div style="margin-bottom:12px;">
          <label>Location:</label><br>
          <input type="text" name="location" id="location" required style="width:100%; padding:8px; border-radius:6px; border:none;">
        </div>
        <div style="margin-bottom:12px;">
          <label>Status:</label><br>
          <select name="status" id="status" required style="width:100%; padding:8px; border-radius:6px; border:none;">
            <option value="Pending">Pending</option>
            <option value="In Progress">In Progress</option>
            <option value="Completed">Completed</option>
          </select>
        </div>
        <div style="margin-bottom:12px;">
          <label>Date Submitted:</label><br>
          <input type="date" name="date_submitted" id="date_submitted" required style="width:100%; padding:8px; border-radius:6px; border:none;">
        </div>
        <div style="text-align:right;">
          <button type="button" onclick="closeForm()" style="margin-right:10px; padding:8px 12px; background:#ccc; border:none; border-radius:6px;">Cancel</button>
          <button type="submit" style="padding:8px 12px; background:#00bcd4; border:none; border-radius:6px; color:#000;">Save</button>
        </div>
      </form>
    </div>
  </div>

  <script>

  // Search functionality
  document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchBar');
    const table = document.getElementById('surveyTable');
    const rows = table.querySelectorAll('tbody tr');
    const noResultsMessage = document.getElementById('noResultsMessage');

    searchInput.addEventListener('input', function () {
      const query = this.value.toLowerCase().trim();
      let matchCount = 0;

      rows.forEach(row => {
        const name = row.cells[0]?.textContent.toLowerCase() || '';
        const type = row.cells[1]?.textContent.toLowerCase() || '';
        const location = row.cells[2]?.textContent.toLowerCase() || '';

        const match = name.includes(query) || type.includes(query) || location.includes(query);
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
  });
  
  function openAddForm() {
    document.getElementById('formTitle').innerText = 'Add Survey File';
    document.getElementById('surveyForm').reset();
    document.getElementById('formId').value = '';
    document.getElementById('popupForm').style.display = 'flex';
  }

  function openEditForm(data) {
    document.getElementById('formTitle').innerText = 'Edit Survey File';
    document.getElementById('formId').value = data.id;
    document.getElementById('transaction_number').value = data.transaction_number || '';
    document.getElementById('first_name').value = data.first_name;
    document.getElementById('last_name').value = data.last_name;
    document.getElementById('survey_type').value = data.survey_type;
    document.getElementById('location').value = data.location;
    document.getElementById('status').value = data.status;
    document.getElementById('date_submitted').value = data.date_submitted;
    document.getElementById('popupForm').style.display = 'flex';
  }

  function closeForm() {
    document.getElementById('popupForm').style.display = 'none';
  }

  // Handle form submission
  document.getElementById('surveyForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const isEdit = formData.get('id') !== '';
    fetch(isEdit ? 'edit_file.php' : 'add_file.php', {
      method: 'POST',
      body: formData
    }).then(res => res.text()).then(response => {
      alert(response);
      location.reload();
    });
  });
  </script>

  <script>
    // Attach form to all edit buttons
    document.querySelectorAll('.edit-btn').forEach(btn => {
      btn.addEventListener('click', function () {
        const data = JSON.parse(this.getAttribute('data-info'));
        openEditForm(data);
      });
    });
  </script>
</body>
</html>
