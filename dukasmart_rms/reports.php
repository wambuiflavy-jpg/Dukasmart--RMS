<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_name = $_SESSION['full_name'] ?? $_SESSION['username'];

// Handle date range filter (default: last 7 days)
$end_date = date('Y-m-d');
$start_date = date('Y-m-d', strtotime('-7 days'));

if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
}

// Sales summary for selected period
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as transaction_count,
        COALESCE(SUM(total_amount), 0) as total_sales,
        COALESCE(AVG(total_amount), 0) as avg_sale
    FROM sales 
    WHERE DATE(sale_date) BETWEEN ? AND ?
");
$stmt->execute([$start_date, $end_date]);
$summary = $stmt->fetch();

// Daily sales for chart/table
$stmt = $pdo->prepare("
    SELECT DATE(sale_date) as sale_day, COUNT(*) as transactions, SUM(total_amount) as daily_total
    FROM sales
    WHERE DATE(sale_date) BETWEEN ? AND ?
    GROUP BY DATE(sale_date)
    ORDER BY sale_day DESC
");
$stmt->execute([$start_date, $end_date]);
$daily_sales = $stmt->fetchAll();

// Low stock products
$stmt = $pdo->query("
    SELECT product_id, product_name, stock_quantity, unit_price
    FROM products
    WHERE stock_quantity <= 5
    ORDER BY stock_quantity ASC
");
$low_stock = $stmt->fetchAll();

// Best selling products (top 5)
$stmt = $pdo->query("
    SELECT p.product_name, SUM(si.quantity_sold) as total_sold
    FROM sale_items si
    JOIN products p ON si.product_id = p.product_id
    GROUP BY p.product_id
    ORDER BY total_sold DESC
    LIMIT 5
");
$best_sellers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reports - DukaSmart RMS</title>
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
        .filter-box {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .filter-box form {
            display: flex;
            gap: 10px;
            align-items: flex-end;
            flex-wrap: wrap;
        }
        .filter-box .form-group {
            display: flex;
            flex-direction: column;
        }
        .filter-box label {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 3px;
        }
        .filter-box input {
            padding: 8px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }
        .filter-box button {
            padding: 8px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .summary-cards {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .summary-card {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            flex: 1;
            min-width: 150px;
            text-align: center;
        }
        .summary-card h3 {
            color: #6c757d;
            font-size: 16px;
            margin-bottom: 10px;
        }
        .summary-card .value {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            margin-top: 20px;
        }
        th {
            background-color: #007bff;
            color: white;
            padding: 10px;
            text-align: left;
        }
        td {
            padding: 10px;
            border: 1px solid #dee2e6;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .low-stock-row {
            background-color: #fff3cd;
        }
        .section-title {
            margin: 30px 0 15px;
            color: #007bff;
            border-bottom: 2px solid #007bff;
            padding-bottom: 5px;
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
            <li><a href="pos.php">💰 POS</a></li>
            <li class="active"><a href="reports.php">📈 Reports</a></li>
        </ul>
    </div>

    <div class="content">
        <h2>📈 Business Reports</h2>

        <!-- Date Filter -->
        <div class="filter-box">
            <form method="GET" action="">
                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                </div>
                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                </div>
                <div>
                    <button type="submit">Generate Report</button>
                </div>
            </form>
        </div>

        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card">
                <h3>Total Sales</h3>
                <div class="value">KES <?php echo number_format($summary['total_sales'], 2); ?></div>
            </div>
            <div class="summary-card">
                <h3>Transactions</h3>
                <div class="value"><?php echo $summary['transaction_count']; ?></div>
            </div>
            <div class="summary-card">
                <h3>Average Sale</h3>
                <div class="value">KES <?php echo number_format($summary['avg_sale'], 2); ?></div>
            </div>
        </div>

        <!-- Daily Sales Table -->
        <h3 class="section-title">Daily Sales</h3>
        <?php if (empty($daily_sales)): ?>
            <p>No sales in this period.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr><th>Date</th><th>Transactions</th><th>Total Sales</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($daily_sales as $day): ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($day['sale_day'])); ?></td>
                        <td><?php echo $day['transactions']; ?></td>
                        <td>KES <?php echo number_format($day['daily_total'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- Low Stock Report -->
        <h3 class="section-title">Low Stock Alert (≤5 items)</h3>
        <?php if (empty($low_stock)): ?>
            <p>No low stock items. Good job!</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr><th>Product</th><th>Stock</th><th>Price</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($low_stock as $item): ?>
                    <tr class="low-stock-row">
                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td><?php echo $item['stock_quantity']; ?></td>
                        <td>KES <?php echo number_format($item['unit_price'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- Best Sellers -->
        <h3 class="section-title">Best Selling Products (Top 5)</h3>
        <?php if (empty($best_sellers)): ?>
            <p>No sales data yet.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr><th>Product</th><th>Quantity Sold</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($best_sellers as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td><?php echo $item['total_sold']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <div class="footer">
            DukaSmart RMS – Developed by Wambui Flavian | Diploma Project 2026
        </div>
    </div>
</div>
</body>
</html>