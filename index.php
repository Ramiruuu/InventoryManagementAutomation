<?php
require_once 'config.php';

$message = '';
$error = '';

// Handle new order submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    try {
        $product_id = $_POST['product_id'];
        $quantity = $_POST['quantity'];

        // Get product current stock
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        if ($product['current_stock'] < $quantity) {
            throw new Exception("Not enough stock! Available: " . $product['current_stock']);
        }

        // Generate order number
        $order_number = "ORD-" . date('Ymd') . "-" . rand(100, 999);

        // Insert order - TRIGGER will automatically update stock
        $stmt = $pdo->prepare("INSERT INTO orders (order_number, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$order_number, $product_id, $quantity]);

        $message = "Order placed! Stock automatically decreased by $quantity";

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get all products with current stock
$products = $pdo->query("SELECT * FROM products ORDER BY id")->fetchAll();

// Get recent orders
$orders = $pdo->query("
    SELECT o.*, p.product_name
    FROM orders o
    JOIN products p ON o.product_id = p.id
    ORDER BY o.order_date DESC
    LIMIT 20
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management System | Automatic Stock Update</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Inventory Management System</h1>
            <p>Oclarit Remar G. | Yamson, Lordjustin</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error">
                ✗ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="grid">
            <!-- Place Order Form -->
            <div class="card">
                <h2>Place New Order</h2>
                <form method="POST" action="">
                    <div class="form-group">
                        <label>Select Product</label>
                        <select name="product_id" required>
                            <option value="">Choose product</option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?php echo $product['id']; ?>">
                                    <?php echo $product['product_name']; ?> - Stock:
                                    <?php echo $product['current_stock']; ?> -
                                    ₱<?php echo number_format($product['price'], 2); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Quantity</label>
                        <input type="number" name="quantity" min="1" required placeholder="Enter quantity">
                    </div>

                    <button type="submit" name="place_order">Place Order</button>
                </form>
            </div>

            <!-- Current Inventory -->
            <div class="card">
                <h2>Current Inventory</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Stock</th>
                            <th>Price</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo $product['product_name']; ?></td>
                                <td class="<?php echo $product['current_stock'] < 20 ? 'stock-low' : ''; ?>">
                                    <?php echo $product['current_stock']; ?>
                                </td>
                                <td>₱<?php echo number_format($product['price'], 2); ?></td>
                                <td>
                                    <?php if ($product['current_stock'] < 20): ?>
                                        <span class="badge badge-warning">Low Stock</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">In Stock</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="card">
            <h2>Recent Orders</h2>
            <table>
                <thead>
                    <tr>
                        <th>Order Number</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Order Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($orders) > 0): ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?php echo $order['order_number']; ?></td>
                                <td><?php echo $order['product_name']; ?></td>
                                <td><?php echo $order['quantity']; ?></td>
                                <td><?php echo date('Y-m-d H:i:s', strtotime($order['order_date'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center;">No orders yet</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>