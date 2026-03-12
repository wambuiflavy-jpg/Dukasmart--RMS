<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_name = $_SESSION['full_name'] ?? $_SESSION['username'];

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle adding to cart
if (isset($_POST['add_to_cart'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity'] ?? 1);

    // Fetch product details from database
    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if ($product && $quantity > 0) {
        // Check if product already in cart
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['product_id'] == $product_id) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $_SESSION['cart'][] = [
                'product_id' => $product['product_id'],
                'name' => $product['product_name'],
                'price' => $product['unit_price'],
                'quantity' => $quantity
            ];
        }
    }
}

// Handle quantity updates / removal
if (isset($_GET['action']) && isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    if ($_GET['action'] == 'remove') {
        foreach ($_SESSION['cart'] as $key => $item) {
            if ($item['product_id'] == $product_id) {
                unset($_SESSION['cart'][$key]);
                break;
            }
        }
        $_SESSION['cart'] = array_values($_SESSION['cart']); // re-index
    } elseif ($_GET['action'] == 'increase') {
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['product_id'] == $product_id) {
                $item['quantity']++;
                break;
            }
        }
    } elseif ($_GET['action'] == 'decrease') {
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['product_id'] == $product_id) {
                if ($item['quantity'] > 1) {
                    $item['quantity']--;
                } else {
                    // Remove if quantity becomes 0
                    // We'll handle via remove action separately
                }
                break;
            }
        }
    }
}

// Handle checkout
if (isset($_POST['checkout'])) {
    $payment_method = $_POST['payment_method'] ?? 'Cash';
    $customer_id = $_POST['customer_id'] ?? null; // optional

    // Calculate total
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }

    if ($total > 0 && !empty($_SESSION['cart'])) {
        try {
            $pdo->beginTransaction();

            // Insert sale
            $stmt = $pdo->prepare("INSERT INTO sales (customer_id, user_id, total_amount, payment_method) VALUES (?, ?, ?, ?)");
            $stmt->execute([$customer_id ?: null, $_SESSION['user_id'], $total, $payment_method]);
            $sale_id = $pdo->lastInsertId();

            // Insert sale items and update stock
            foreach ($_SESSION['cart'] as $item) {
                $stmt = $pdo->prepare("INSERT INTO sale_items (sale_id, product_id, quantity_sold, unit_price_at_sale) VALUES (?, ?, ?, ?)");
                $stmt->execute([$sale_id, $item['product_id'], $item['quantity'], $item['price']]);

                // Update product stock
                $stmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
                $stmt->execute([$item['quantity'], $item['product_id']]);
            }

            $pdo->commit();

            // Clear cart
            $_SESSION['cart'] = [];
            $success = "Sale completed! Receipt #SALE" . str_pad($sale_id, 5, '0', STR_PAD_LEFT);
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Transaction failed: " . $e->getMessage();
        }
    } else {
        $error = "Cart is empty.";
    }
}

// Fetch all products for dropdown (or display list)
$products = $pdo->query("SELECT * FROM products WHERE stock_quantity > 0 ORDER BY product_name")->fetchAll();

// Fetch customers for dropdown (optional)
$customers = $pdo->query("SELECT customer_id, customer_name FROM customers ORDER BY customer_name")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Point of Sale - DukaSmart RMS</title>
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
        .btn-success {
            background-color: #28a745;
            color: white;
        }
        .btn-success:hover {
            background-color: #218838;
        }
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        .btn-warning {
            background-color: #ffc107;
            color: #212529;
        }
        .pos-container {
            display: flex;
            gap: 20px;
        }
        .product-panel {
            flex: 2;
        }
        .cart-panel {
            flex: 1;
        }
        .product-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 20px;
        }
        .product-item {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 10px;
            width: 180px;
            text-align: center;
        }
        .product-item h4 {
            font-size: 16px;
            margin-bottom: 5px;
        }
        .product-item .price {
            color: #28a745;
            font-weight: bold;
        }
        .product-item form {
            margin-top: 10px;
        }
        .product-item input[type="number"] {
            width: 60px;
            padding: 4px;
        }
        .cart-table {
            width: 100%;
            background: white;
            border-collapse: collapse;
        }
        .cart-table th {
            background-color: #007bff;
            color: white;
            padding: 8px;
        }
        .cart-table td {
            padding: 8px;
            border: 1px solid #dee2e6;
        }
        .cart-total {
            font-size: 20px;
            font-weight: bold;
            text-align: right;
            margin-top: 15px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
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
    <h1>🛒 DukaSmart RMS - Point of Sale</h1>
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
            <li class="active"><a href="pos.php">💰 POS</a></li>
            <li><a href="sales.php">📈 Sales</a></li>
        </ul>
    </div>

    <div class="content">
        <h2>💰 Point of Sale</h2>

        <?php if (isset($success)): ?>
            <div class="alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="pos-container">
            <!-- Product Panel -->
            <div class="product-panel">
                <h3>Products</h3>
                <div class="product-grid">
                    <?php foreach ($products as $product): ?>
                    <div class="product-item">
                        <h4><?php echo htmlspecialchars($product['product_name']); ?></h4>
                        <div class="price">KES <?php echo number_format($product['unit_price'], 2); ?></div>
                        <div>Stock: <?php echo $product['stock_quantity']; ?></div>
                        <form method="POST" action="">
                            <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                            <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>" required>
                            <button type="submit" name="add_to_cart" class="btn btn-primary">Add to Cart</button>
                        </form>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Cart Panel -->
            <div class="cart-panel">
                <h3>Current Sale</h3>
                <?php if (empty($_SESSION['cart'])): ?>
                    <p>Cart is empty.</p>
                <?php else: ?>
                    <table class="cart-table">
                        <thead>
                            <tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            <?php $total = 0; ?>
                            <?php foreach ($_SESSION['cart'] as $item): ?>
                                <?php $line_total = $item['price'] * $item['quantity']; $total += $line_total; ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td>
                                        <a href="?action=decrease&id=<?php echo $item['product_id']; ?>" class="btn btn-warning btn-sm">-</a>
                                        <?php echo $item['quantity']; ?>
                                        <a href="?action=increase&id=<?php echo $item['product_id']; ?>" class="btn btn-success btn-sm">+</a>
                                    </td>
                                    <td>KES <?php echo number_format($item['price'], 2); ?></td>
                                    <td>KES <?php echo number_format($line_total, 2); ?></td>
                                    <td><a href="?action=remove&id=<?php echo $item['product_id']; ?>" class="btn btn-danger btn-sm">Remove</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="cart-total">
                        Total: KES <?php echo number_format($total, 2); ?>
                    </div>

                    <!-- Checkout Form -->
                    <form method="POST" action="" style="margin-top:20px;">
                        <div class="form-group">
                            <label for="customer_id">Customer (optional):</label>
                            <select name="customer_id" id="customer_id">
                                <option value="">Walk-in Customer</option>
                                <?php foreach ($customers as $customer): ?>
                                    <option value="<?php echo $customer['customer_id']; ?>"><?php echo htmlspecialchars($customer['customer_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="payment_method">Payment Method:</label>
                            <select name="payment_method" id="payment_method">
                                <option value="Cash">Cash</option>
                                <option value="M-Pesa">M-Pesa</option>
                                <option value="Card">Card</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <button type="submit" name="checkout" class="btn btn-success" style="width:100%;">Complete Sale</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="footer">
            DukaSmart RMS – Developed by Wambui Flavian | Diploma Project 2026
        </div>
    </div>
</div>
</body>
</html>