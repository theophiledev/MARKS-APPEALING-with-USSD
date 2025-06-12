<?php
require 'db.php';
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

// Setup
$perPage = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$startAt = ($page - 1) * $perPage;

$statusFilter = $_GET['status'] ?? '';
$searchRegno = $_GET['search'] ?? '';

$params = [];
$where = "";

if ($statusFilter && in_array($statusFilter, ['pending', 'under review', 'resolved'])) {
    $where .= " AND a.status = ?";
    $params[] = $statusFilter;
}

if ($searchRegno) {
    $where .= " AND s.regno LIKE ?";
    $params[] = "%$searchRegno%";
}

$sql = "SELECT a.id, s.name AS student_name, s.regno, m.module_name, a.reason, a.status, mk.mark
        FROM appeals a 
        JOIN students s ON a.student_regno = s.regno 
        JOIN modules m ON a.module_id = m.id 
        LEFT JOIN marks mk ON mk.student_regno = s.regno AND mk.module_id = m.id
        WHERE 1 $where
        LIMIT $startAt, $perPage";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$appeals = $stmt->fetchAll();

// Total count for pagination
$countStmt = $pdo->prepare("SELECT COUNT(*) 
                            FROM appeals a 
                            JOIN students s ON a.student_regno = s.regno 
                            WHERE 1 $where");
$countStmt->execute($params);
$totalAppeals = $countStmt->fetchColumn();
$totalPages = ceil($totalAppeals / $perPage);
?>


<!DOCTYPE html>
<html>
<head>
    <title>Admin - Appeals</title>
    <style>
        body {
            margin: 0;
            background: #f4f8fb;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 220px;
            background: linear-gradient(135deg, #1590c1 60%, #0d5c8c 100%);
            color: #fff;
            padding: 30px 20px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            box-shadow: 2px 0 8px rgba(0,0,0,0.04);
        }
        .sidebar h2 {
            margin-bottom: 30px;
            font-size: 1.6em;
            letter-spacing: 1px;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar ul li {
            margin-bottom: 18px;
        }
        .sidebar ul li a {
            color: #fff;
            font-size: 1.1em;
            text-decoration: none;
            transition: color 0.2s;
        }
        .sidebar ul li a:hover {
            color: #ffe082;
        }
        .main-content {
            flex: 1;
            padding: 40px 50px;
        }
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .dashboard-header h1 {
            color: #1590c1;
            font-size: 2em;
            margin: 0;
        }
        .admin-welcome {
            font-size: 1.1em;
            color: #333;
            background: #e3f2fd;
            padding: 8px 18px;
            border-radius: 20px;
        }
        .dashboard-summary {
            display: flex;
            gap: 25px;
            margin-bottom: 35px;
        }
        .summary-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            padding: 22px 30px;
            min-width: 140px;
            text-align: center;
        }
        .summary-title {
            font-size: 1em;
            color: #888;
            margin-bottom: 8px;
            display: block;
        }
        .summary-value {
            font-size: 1.7em;
            font-weight: bold;
            color: #1590c1;
        }
        .summary-card.pending .summary-value { color: #ff9800; }
        .summary-card.review .summary-value { color: #2196f3; }
        .summary-card.resolved .summary-value { color: #43a047; }
        .filter-bar {
            display: flex;
            gap: 12px;
            margin-bottom: 25px;
        }
        .filter-bar input[type="text"], .filter-bar select {
            padding: 10px 14px;
            border: 1px solid #b0bec5;
            border-radius: 6px;
            font-size: 1em;
            background: #f7fafc;
        }
        .filter-bar button {
            background: #1590c1;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 10px 22px;
            font-size: 1em;
            cursor: pointer;
            transition: background 0.2s;
        }
        .filter-bar button:hover {
            background: #0d5c8c;
        }
        .appeals-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        .appeals-table th, .appeals-table td {
            padding: 14px 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        .appeals-table th {
            background: #f1f8ff;
            color: #1590c1;
            font-weight: 600;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.95em;
            font-weight: 500;
            color: #fff;
        }
        .status-badge.pending { background: #ff9800; }
        .status-badge.under-review { background: #2196f3; }
        .status-badge.resolved { background: #43a047; }
        .appeals-table tr:last-child td {
            border-bottom: none;
        }
        .appeals-table td form {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .pagination {
            margin-top: 30px;
            text-align: center;
        }
        .pagination a {
            padding: 8px 16px;
            margin: 0 4px;
            background: #e3f2fd;
            color: #1590c1;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.2s;
        }
        .pagination a.active, .pagination a:hover {
            background: #1590c1;
            color: #fff;
        }
        @media (max-width: 900px) {
            .dashboard-container { flex-direction: column; }
            .sidebar { width: 100%; min-height: unset; flex-direction: row; justify-content: space-between; }
            .main-content { padding: 20px 5vw; }
            .dashboard-summary { flex-direction: column; gap: 12px; }
        }
    </style>
</head>
<body>
<div class="dashboard-container">
    <aside class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="admin.php">Dashboard</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </aside>
    <main class="main-content">
        <header class="dashboard-header">
            <h1>Student Appeals Management System</h1>
            <div class="admin-welcome">Welcome, <?= $_SESSION['admin'] ?></div>
        </header>
        <section class="dashboard-summary">
            <div class="summary-card total">
                <span class="summary-title">Total Appeals</span>
                <span class="summary-value"><?= $totalAppeals ?></span>
            </div>
            <div class="summary-card pending">
                <span class="summary-title">Pending</span>
                <span class="summary-value">
                    <?php
                    $pendingStmt = $pdo->query("SELECT COUNT(*) FROM appeals WHERE status = 'pending'");
                    echo $pendingStmt->fetchColumn();
                    ?>
                </span>
            </div>
            <div class="summary-card review">
                <span class="summary-title">Under Review</span>
                <span class="summary-value">
                    <?php
                    $reviewStmt = $pdo->query("SELECT COUNT(*) FROM appeals WHERE status = 'under review'");
                    echo $reviewStmt->fetchColumn();
                    ?>
                </span>
            </div>
            <div class="summary-card resolved">
                <span class="summary-title">Resolved</span>
                <span class="summary-value">
                    <?php
                    $resolvedStmt = $pdo->query("SELECT COUNT(*) FROM appeals WHERE status = 'resolved'");
                    echo $resolvedStmt->fetchColumn();
                    ?>
                </span>
            </div>
        </section>
        <form method="get" class="filter-bar">
            <input type="text" name="search" placeholder="Search by RegNo" value="<?= htmlspecialchars($searchRegno) ?>">
            <select name="status">
                <option value="">-- Status Filter --</option>
                <option value="pending" <?= $statusFilter == 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="under review" <?= $statusFilter == 'under review' ? 'selected' : '' ?>>Under Review</option>
                <option value="resolved" <?= $statusFilter == 'resolved' ? 'selected' : '' ?>>Resolved</option>
            </select>
            <button type="submit">Filter</button>
        </form>
        <table class="appeals-table">
            <tr>
                <th>#</th>
                <th>Student</th>
                <th>Module</th>
                <th>Marks</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php foreach ($appeals as $i => $a): ?>
            <tr>
                <td><?= $i+1 + $startAt ?></td>
                <td><?= htmlspecialchars($a['student_name']) ?> (<?= $a['regno'] ?>)</td>
                <td><?= htmlspecialchars($a['module_name']) ?></td>
                <td><?= is_numeric($a['mark']) ? $a['mark'] : 'N/A' ?></td>
                <td><?= htmlspecialchars($a['reason']) ?></td>
                <td><span class="status-badge <?= str_replace(' ', '-', $a['status']) ?>"><?= ucfirst($a['status']) ?></span></td>
                <td>
                    <form method="post" action="update_status.php">
                        <input type="hidden" name="id" value="<?= $a['id'] ?>">
                        <select name="status">
                            <option <?= $a['status'] == 'pending' ? 'selected' : '' ?>>pending</option>
                            <option <?= $a['status'] == 'under review' ? 'selected' : '' ?>>under review</option>
                            <option <?= $a['status'] == 'resolved' ? 'selected' : '' ?>>resolved</option>
                        </select>
                        <button type="submit">Update</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?>&status=<?= urlencode($statusFilter) ?>&search=<?= urlencode($searchRegno) ?>" class="<?= $i == $page ? 'active' : '' ?>"> <?= $i ?> </a>
            <?php endfor; ?>
        </div>
    </main>
</div>

</body>
</html>
