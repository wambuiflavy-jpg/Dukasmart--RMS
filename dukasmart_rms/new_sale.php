<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_name = $_SESSION['full_name'] ?? $_SESSION['username'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>New Sale - DukaSmart RMS</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }
        body {
            background-color: #f8f9fa;
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            font-size: 24px;
        }
        .user-info a {
            color: white;
            text-decoration: none;
            margin-left: 10px;
            padding: 5px 10px;
            background-color: rgba(255,255,255,0.2);
            border-radius: 4px;
        }
        .user-info a:hover {
            background-color: rgba(255,255,255,0.3);
        }
        .container {
            display: flex;
            min-height: calc(100vh - 70px);
        }
        .sidebar {
            width: 200px;
            background-color: #e9ecef;
            padding: 20px 0;
        }
        .sidebar ul {
            list-style: none;
        }
        .sidebar li {
            padding: 12px 20px;
        }
        .sidebar li a {
            text-decoration: none;
            color: #212529;
            display: block;
        }
        .sidebar li.active {
            background-color: #007bff;
        }
        .sidebar li.active a {
            color: white;
        }
        .sidebar li:hover:not(.active) {
            background-color: #dee2e6;
        }
        .content {
            flex: 1;
            padding: 20px;
        }
        h2 {
            color: #007bff;
            margin-bottom: 20px;
        }
        .back-link {
            text-decoration: none;
            color: #007bff;
            margin-bottom: 20px;
            display: inline-block;
        }
        .message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
            padding: 20px;
            text-align: center;
            margin-top: 20px;
        }
        .btn {
            display: inline-block;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            margin: 5px;
        }
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding: 15px;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
            font-size: 14px;
        }
    </style>
</head>
<body>
<div class="header">
    <h1>🛒 DukaSmart RMS</h1>
    <div class="user-info">
        👤 <?php echo htmlspecialchars($user_name); ?> | <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <div class="sidebar">
        <ul>
            <li><a href="dashboard.php">📊 Dashboard</a></li>
            <li><a href="products.php">📦 Products</a></li>
            <li><a href="customers.php">👥 Customers</a></li>
            <li class="active"><a href="sales.php">💰 Sales</a></li>
            <li><a href="reports.php">📈 Reports</a></li>
        </ul>
    </div>

    <div class="content">
        <a href="sales.php" class="back-link">← Back to Sales</a>
        <h2>➕ New Sale</h2>

        <div class="message">
            <p style="font-size: 18px; margin-bottom: 10px;">✨ New sale form is under development</p>
            <p>This page will allow you to select products, add to cart, and complete transactions.</p>
            <p style="margin-top: 20px;">
                <a href="sales.php" class="btn btn-primary">Return to Sales</a>
            </p>
        </div>

        <div class="footer">
            DukaSmart RMS – Developed by Wambui Flavian | Diploma Project 2026
        </div>
    </div>
</div>
</body>
</html>