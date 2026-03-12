<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$sale_id = $_GET['id'] ?? 0;
if (!$sale_id) {
    header("Location: sales.php");
    exit();
}

// Fetch sale details
$stmt = $pdo->prepare("
    SELECT s.*, c.customer_name, u.username 
    FROM sales s
    LEFT JOIN customers c ON s.customer_id = c.customer_id
    LEFT JOIN users u ON s.user_id = u.user_id
    WHERE s.sale_id = ?
");
$stmt->execute([$sale_id]);
$sale = $stmt->fetch();

if (!$sale) {
    header("Location: sales.php");
    exit();
}

// Fetch sale items
$stmt = $pdo->prepare("
    SELECT si.*, p.product_name 
    FROM sale_items si
    JOIN products p ON si.product_id = p.product_id
    WHERE si.sale_id = ?
");
$stmt->execute([$sale_id]);
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Receipt #SALE<?php echo str_pad($sale_id, 5, '0', STR_PAD_LEFT); ?> - DukaSmart RMS</title>
    <style>
        body { font-family: Arial; background: #f8f9fa; padding: 20px; }
        .receipt {
            max-width: 400px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        h2 { text-align: center; color: #007bff; }
        .details { margin: 15px 0; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #007bff; color: white; padding: 5px; }
        td { padding: 5px; border: 1px solid #dee2e6; }
        .total { font-size: 18px; font-weight: bold; text-align: right; margin-top: 15px; }
        .btn {
            display: inline-block;
            padding: 8px 15px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <h2>🛒 DukaSmart RMS</h2>
        <p style="text-align:center;">Receipt #SALE<?php echo str_pad($sale_id, 5, '0', STR_PAD_LEFT); ?></p>
        <p>Date: <?php echo date('d/m/Y H:i', strtotime($sale['sale_date'])); ?></p>
        <p>Cashier: <?php echo htmlspecialchars($sale['username']); ?></p>
        <p>Customer: <?php echo htmlspecialchars($sale['customer_name'] ?? 'Walk-in'); ?></p>
        <p>Payment: <?php echo $sale['payment_method']; ?></p>
        <hr>
        <table>
            <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                    <td><?php echo $item['quantity_sold']; ?></td>
                    <td>KES <?php echo number_format($item['unit_price_at_sale'], 2); ?></td>
                    <td>KES <?php echo number_format($item['quantity_sold'] * $item['unit_price_at_sale'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="total">
            Total: KES <?php echo number_format($sale['total_amount'], 2); ?>
        </div>
        <a href="javascript:window.print()" class="btn">Print Receipt</a>
        <a href="sales.php" class="btn" style="background:#6c757d;">Back to Sales</a>
    </div>
</body>
</html>