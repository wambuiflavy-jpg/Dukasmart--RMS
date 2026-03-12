<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_name = $_SESSION['full_name'] ?? $_SESSION['username'];

// Fetch customers from database
$stmt = $pdo->query("SELECT * FROM customers ORDER BY customer_id DESC");
$customers = $stmt->fetchAll();

// Handle success messages
$success = $_GET['success'] ?? '';
$success_message = '';
if ($success == 'added') {
    $success_message = "Customer added successfully!";
} elseif ($success == 'updated') {
    $success_message = "Customer updated successfully!";
} elseif ($success == 'deleted') {
    $success_message = "Customer deleted successfully!";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Customers - DukaSmart RMS</title>
    <style>
        /* Copy the same styles as products.php for consistency */
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
        .btn {
            display: inline-block;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
            margin: 2px;
        }
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .btn-warning {
            background-color: #ffc107;
            color: #212529;
        }
        .btn-warning:hover {
            background-color: #e0a800;
        }
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        .customers-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin: 20px 0;
        }
        .customer-card {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            width: 300px;
        }
        .customer-id {
            background-color: #e9ecef;
            padding: 4px 8px;
            display: inline-block;
            border-radius: 4px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .customer-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .customer-info {
            margin: 5px 0;
            color: #495057;
        }
        .stats-box {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
        }
        .stats-row {
            display: flex;
            gap: 15px;
            margin-top: 10px;
        }
        .stat-item {
            flex: 1;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            text-align: center;
        }
        .stat-value {
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
            <li class="active"><a href="customers.php">👥 Customers</a></li>
            <li><a href="sales.php">💰 Sales</a></li>
            <li><a href="reports.php">📈 Reports</a></li>
        </ul>
    </div>

    <div class="content">
        <h2>👥 Customer Management</h2>
        <div style="margin:20px 0;">
            <a href="add_customer.php" class="btn btn-primary">➕ Add New Customer</a>
        </div>

        <?php if ($success_message): ?>
            <div class="alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <div class="customers-grid">
            <?php if (empty($customers)): ?>
                <p>No customers found. <a href="add_customer.php">Add your first customer</a>.</p>
            <?php else: ?>
                <?php foreach ($customers as $customer): ?>
                <div class="customer-card">
                    <div class="customer-id">CUST-<?php echo str_pad($customer['customer_id'], 3, '0', STR_PAD_LEFT); ?></div>
                    <div class="customer-name"><?php echo htmlspecialchars($customer['customer_name']); ?></div>
                    <div class="customer-info">📞 <?php echo htmlspecialchars($customer['phone_number']); ?></div>
                    <div class="customer-info">✉️ <?php echo htmlspecialchars($customer['email'] ?? ''); ?></div>
                    <div class="customer-info">💰 Total Spent: KES <?php echo number_format($customer['total_spent'], 2); ?></div>
                    <div style="margin-top:10px;">
                        <a href="edit_customer.php?id=<?php echo $customer['customer_id']; ?>" class="btn btn-primary">Edit</a>
                        <a href="delete_customer.php?id=<?php echo $customer['customer_id']; ?>" class="btn btn-danger" onclick="return confirm('Delete this customer?')">Delete</a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <h3>Customer List</h3>
        <table>
            <thead>
                <tr><th>ID</th><th>Name</th><th>Phone</th><th>Email</th><th>Total Spent</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $customer): ?>
                <tr>
                    <td>CUST-<?php echo str_pad($customer['customer_id'], 3, '0', STR_PAD_LEFT); ?></td>
                    <td><?php echo htmlspecialchars($customer['customer_name']); ?></td>
                    <td><?php echo htmlspecialchars($customer['phone_number']); ?></td>
                    <td><?php echo htmlspecialchars($customer['email'] ?? ''); ?></td>
                    <td>KES <?php echo number_format($customer['total_spent'], 2); ?></td>
                    <td>
                        <a href="edit_customer.php?id=<?php echo $customer['customer_id']; ?>" class="btn btn-primary">Edit</a>
                        <a href="delete_customer.php?id=<?php echo $customer['customer_id']; ?>" class="btn btn-danger" onclick="return confirm('Delete?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php
        // Calculate totals for stats
        $total_customers = count($customers);
        $total_spent = array_sum(array_column($customers, 'total_spent'));
        ?>

        <div class="stats-box">
            <h3>Customer Statistics</h3>
            <div class="stats-row">
                <div class="stat-item">
                    <div class="stat-value"><?php echo $total_customers; ?></div>
                    <div>Total Customers</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">KES <?php echo number_format($total_spent, 2); ?></div>
                    <div>Total Spent</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">KES <?php echo $total_customers ? number_format($total_spent / $total_customers, 2) : 0; ?></div>
                    <div>Average per Customer</div>
                </div>
            </div>
        </div>

        <div class="footer">
            DukaSmart RMS – Developed by Wambui Flavian | Diploma Project 2026
        </div>
    </div>
</div>
</body>
</html>