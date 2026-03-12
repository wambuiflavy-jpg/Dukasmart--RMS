<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_name = $_SESSION['full_name'] ?? $_SESSION['username'];

// Fetch products from database
$stmt = $pdo->query("SELECT * FROM products ORDER BY product_id DESC");
$products = $stmt->fetchAll();

// Handle success messages
$success = $_GET['success'] ?? '';
$success_message = '';
if ($success == 'added') {
    $success_message = "Product added successfully!";
} elseif ($success == 'updated') {
    $success_message = "Product updated successfully!";
} elseif ($success == 'deleted') {
    $success_message = "Product deleted successfully!";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Products - DukaSmart RMS</title>
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
        .search-section {
            background-color: white;
            border: 1px solid #dee2e6;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .search-section input[type="text"] {
            width: 300px;
            padding: 8px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }
        .search-section button {
            padding: 8px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .search-section button:hover {
            background-color: #0056b3;
        }
        .products-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin: 20px 0;
        }
        .product-card {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            width: 250px;
        }
        .product-code {
            background-color: #e9ecef;
            padding: 4px 8px;
            display: inline-block;
            border-radius: 4px;
            font-weight: bold;
        }
        .product-name {
            font-size: 18px;
            margin: 10px 0 5px;
        }
        .product-price {
            color: #28a745;
            font-size: 20px;
        }
        .stock-high { color: #28a745; }
        .stock-low { color: #fd7e14; }
        .stock-out { color: #dc3545; }
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
    <script>
        function searchProducts() {
            var input = document.getElementById("productSearch");
            var filter = input.value.toLowerCase();
            var cards = document.getElementsByClassName("product-card");
            var rows = document.querySelectorAll("tbody tr");
            var found = 0;

            for (var i = 0; i < cards.length; i++) {
                var text = cards[i].innerText.toLowerCase();
                if (text.indexOf(filter) > -1) {
                    cards[i].style.display = "";
                    found++;
                } else {
                    cards[i].style.display = "none";
                }
            }
            for (var i = 0; i < rows.length; i++) {
                var text = rows[i].innerText.toLowerCase();
                if (text.indexOf(filter) > -1) {
                    rows[i].style.display = "";
                } else {
                    rows[i].style.display = "none";
                }
            }
            document.getElementById("searchResults").innerText = filter === "" ? "Showing all products" : "Found " + found + " matching products";
        }
        function clearSearch() {
            document.getElementById("productSearch").value = "";
            searchProducts();
        }
        window.onload = function() {
            searchProducts();
        }
    </script>
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
            <li class="active"><a href="products.php">📦 Products</a></li>
            <li><a href="customers.php">👥 Customers</a></li>
            <li><a href="sales.php">💰 Sales</a></li>
            <li><a href="reports.php">📈 Reports</a></li>
        </ul>
    </div>

    <div class="content">
        <h2>📦 Product Management</h2>
        <div style="margin:20px 0;">
            <a href="add_product.php" class="btn btn-primary">➕ Add New Product</a>
        </div>

        <?php if ($success_message): ?>
            <div class="alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <div class="search-section">
            <h3>🔍 Search Products</h3>
            <input type="text" id="productSearch" placeholder="Search by name, code..." onkeyup="searchProducts()">
            <button onclick="searchProducts()">Search</button>
            <button onclick="clearSearch()">Clear</button>
            <p id="searchResults" style="margin-top:10px;"></p>
        </div>

        <div class="products-grid" id="productGrid">
            <?php if (empty($products)): ?>
                <p>No products found. <a href="add_product.php">Add your first product</a>.</p>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-code">PROD-<?php echo str_pad($product['product_id'], 3, '0', STR_PAD_LEFT); ?></div>
                        <div class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></div>
                        <div>Category: <?php echo htmlspecialchars($product['category']); ?></div>
                        <div class="product-price">KES <?php echo number_format($product['unit_price'], 2); ?></div>
                        <div>Stock: 
                            <span class="<?php 
                                if ($product['stock_quantity'] <= 0) echo 'stock-out';
                                elseif ($product['stock_quantity'] < 5) echo 'stock-low';
                                else echo 'stock-high';
                            ?>"><?php echo $product['stock_quantity']; ?></span>
                        </div>
                        <p><?php echo htmlspecialchars($product['supplier_name'] ?? ''); ?></p>
                        <div>
                            <a href="edit_product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-primary">Edit</a>
                            <a href="delete_product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-danger" onclick="return confirm('Delete this product?')">Delete</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <h3>Detailed Product List</h3>
        <table>
            <thead>
                <tr><th>Code</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td>PROD-<?php echo str_pad($product['product_id'], 3, '0', STR_PAD_LEFT); ?></td>
                    <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                    <td><?php echo htmlspecialchars($product['category']); ?></td>
                    <td>KES <?php echo number_format($product['unit_price'], 2); ?></td>
                    <td><?php echo $product['stock_quantity']; ?></td>
                    <td>
                        <a href="edit_product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-primary">Edit</a>
                        <a href="delete_product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-danger" onclick="return confirm('Delete?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="footer">
            DukaSmart RMS – Developed by Wambui Flavian | Diploma Project 2026
        </div>
    </div>
</div>
</body>
</html>