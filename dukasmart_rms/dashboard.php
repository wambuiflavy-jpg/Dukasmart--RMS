<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_name = $_SESSION['full_name'] ?? $_SESSION['username'];
$user_role = $_SESSION['role'] ?? 'staff';

// Get real stats from database
// Total products
$stmt = $pdo->query("SELECT COUNT(*) FROM products");
$total_products = $stmt->fetchColumn();

// Total customers
$stmt = $pdo->query("SELECT COUNT(*) FROM customers");
$total_customers = $stmt->fetchColumn();

// Low stock products (stock <= 5)
$stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE stock_quantity <= 5 AND stock_quantity > 0");
$low_stock = $stmt->fetchColumn();

// Out of stock products
$stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE stock_quantity = 0");
$out_of_stock = $stmt->fetchColumn();

// Today's sales total
$today = date('Y-m-d');
$stmt = $pdo->prepare("SELECT COALESCE(SUM(total_amount), 0) FROM sales WHERE DATE(sale_date) = ?");
$stmt->execute([$today]);
$today_sales = $stmt->fetchColumn();

// Total sales overall
$stmt = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM sales");
$total_sales = $stmt->fetchColumn();

// Recent sales (last 5)
$stmt = $pdo->query("
    SELECT s.*, c.customer_name 
    FROM sales s
    LEFT JOIN customers c ON s.customer_id = c.customer_id
    ORDER BY s.sale_id DESC
    LIMIT 5
");
$recent_sales = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - DukaSmart RMS</title>
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
        .cards {
            display: flex;
            gap: 20px;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        .card {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            width: 220px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .card h3 {
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .card .value {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }
        .card-sales { border-left: 4px solid #007bff; }
        .card-products { border-left: 4px solid #6f42c1; }
        .card-customers { border-left: 4px solid #28a745; }
        .card-lowstock { border-left: 4px solid #fd7e14; }
        .card-outstock { border-left: 4px solid #dc3545; }
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
        .status-completed {
            background-color: #d4edda;
            color: #155724;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
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
        👤 <?php echo htmlspecialchars($user_name); ?> (<?php echo $user_role; ?>) |
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <div class="sidebar">
        <ul>
            <li class="active"><a href="dashboard.php">📊 Dashboard</a></li>
            <li><a href="products.php">📦 Products</a></li>
            <li><a href="customers.php">👥 Customers</a></li>
            <li><a href="pos.php">💰 POS</a></li>
            <li><a href="sales.php">📈 Sales</a></li>
        </ul>
    </div>

    <div class="content">
        <h2>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h2>

        <div class="cards">
            <div class="card card-sales">
                <h3>Today's Sales</h3>
                <div class="value">KES <?php echo number_format($today_sales, 2); ?></div>
            </div>
            <div class="card card-products">
                <h3>Total Products</h3>
                <div class="value"><?php echo $total_products; ?></div>
            </div>
            <div class="card card-customers">
                <h3>Total Customers</h3>
                <div class="value"><?php echo $total_customers; ?></div>
            </div>
            <div class="card card-lowstock">
                <h3>Low Stock (≤5)</h3>
                <div class="value"><?php echo $low_stock; ?></div>
            </div>
            <div class="card card-outstock">
                <h3>Out of Stock</h3>
                <div class="value"><?php echo $out_of_stock; ?></div>
            </div>
        </div>

        <h3>Recent Sales</h3>
        <?php if (empty($recent_sales)): ?>
            <p>No sales yet. <a href="pos.php">Make your first sale</a>.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr><th>Receipt</th><th>Date</th><th>Customer</th><th>Total</th><th>Payment</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_sales as $sale): ?>
                    <tr>
                        <td>SALE<?php echo str_pad($sale['sale_id'], 5, '0', STR_PAD_LEFT); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($sale['sale_date'])); ?></td>
                        <td><?php echo htmlspecialchars($sale['customer_name'] ?? 'Walk-in'); ?></td>
                        <td>KES <?php echo number_format($sale['total_amount'], 2); ?></td>
                        <td><?php echo $sale['payment_method']; ?></td>
                        <td><span class="status-completed">Completed</span></td>
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