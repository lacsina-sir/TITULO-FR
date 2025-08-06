<?php
session_start();
if (!isset($_SESSION['client_id'])) {
    header("Location: index.php");
    exit();
}

include 'db_connection.php';

$client_id = $_SESSION['client_id'];

$query = "SELECT * FROM progress_tracker WHERE client_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Client-Side Tracking</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to right, #0f2027, #203a43, #2c5364);
            color: #fff;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: rgba(0, 0, 0, 0.7);
            padding: 15px 30px;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .logo-section {
            display: flex;
            align-items: center;
        }
        .logo {
            font-size: 30px;
            color: #ffcc00;
            font-weight: bold;
            margin-right: 10px;
        }
        .title-text {
            font-size: 24px;
            font-weight: 600;
            color: #fff;
        }
        .nav-pills {
            display: flex;
            gap: 15px;
        }
        .nav-pill {
            color: #fff;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 25px;
            transition: background-color 0.3s;
        }
        .nav-pill:hover, .nav-pill.active {
            background-color: #ffcc00;
            color: #000;
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
        h1 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 36px;
            color: #ffcc00;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 15px;
            border-bottom: 1px solid #ddd;
            text-align: center;
        }
        th {
            background-color: #ffcc00;
            color: #000;
        }
        td {
            background-color: rgba(255, 255, 255, 0.1);
        }
        @keyframes fadeIn {
            0% { opacity: 0; transform: translateY(-10px); }
            100% { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="logo-section">
            <div class="logo">C</div>
            <div class="title-text">TITULO</div>
        </div>
        <nav class="nav-pills">
            <a href="client_dashboard.php" class="nav-pill">Home</a>
            <a href="client_files.php" class="nav-pill">Files</a>
            <a href="client_profile.php" class="nav-pill">Profiles</a>
            <a href="client_chat.php" class="nav-pill">Chat</a>
            <a href="client-side_tracking.php" class="nav-pill active">Track</a>
            <a href="index.php" class="nav-pill">Log Out</a>
        </nav>
    </header>

    <div class="container">
        <h1>Project Progress Tracker</h1>
        <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Milestone</th>
                    <th>Status</th>
                    <th>Expected Completion</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['milestone_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                    <td><?php echo htmlspecialchars($row['expected_completion']); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p style="text-align: center;">No progress updates available yet.</p>
        <?php endif; ?>
    </div>
</body>
</html>
