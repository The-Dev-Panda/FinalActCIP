<?php
session_start();
include("db_connect.php");

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: login.php");
    exit();
}

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$user_email = $_SESSION['user_email'];
$user_type = $_SESSION['user_type'];

$items = array(
    1 => "Laptop", 2 => "Mouse", 3 => "Keyboard", 4 => "Monitor", 5 => "Headphones",
    6 => "Webcam", 7 => "Speaker", 8 => "Tablet", 9 => "Phone", 10 => "Charger",
    11 => "Cable", 12 => "Router", 13 => "Switch", 14 => "Hard Drive", 15 => "SSD",
    16 => "RAM", 17 => "Graphics Card", 18 => "Processor", 19 => "Motherboard", 20 => "Power Supply"
);

$item_costs = array(
    1 => 50000, 2 => 1500, 3 => 3000, 4 => 15000, 5 => 5000,
    6 => 8000, 7 => 4000, 8 => 25000, 9 => 30000, 10 => 1000,
    11 => 500, 12 => 8000, 13 => 12000, 14 => 6000, 15 => 8000,
    16 => 4000, 17 => 25000, 18 => 15000, 19 => 12000, 20 => 7000
);

if (!empty($_POST['order_item'])) {
    $item_id = $_POST['item_id'];
    $quantity = $_POST['quantity'];

    if (isset($item_costs[$item_id]) && $quantity > 0) {
        $cost = $item_costs[$item_id];
        $amount = $cost * $quantity;

        $insert_order = "INSERT INTO orders (order_date, email, item, cost, quantity, amount)
                         VALUES (CURDATE(), '$user_email', $item_id, $cost, $quantity, $amount)";
        mysqli_query($con, $insert_order);

        $order_message = "Order placed successfully!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Place Order - Shopping System</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .nav { margin-bottom: 20px; }
        .nav a { margin-right: 15px; text-decoration: none; color: #007bff; }
        .nav a:hover { text-decoration: underline; }
        .message { color: green; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Place Your Order</h1>

    <p>Welcome, <?php print $user_email; ?>!</p>
    <p>User Type: <?php print ($user_type == 0) ? "Customer" : "Admin"; ?></p>

    <div class="nav">
        <a href="view_orders.php">View My Orders</a>
        <?php if ($user_type == 1) { ?>
            <a href="admin.php">Admin Panel</a>
        <?php } ?>
        <a href="logout.php">Logout</a>
    </div>

    <?php if (isset($order_message)) { ?>
        <p class="message"><?php print $order_message; ?></p>
    <?php } ?>

    <h2>Available Items</h2>
    <table>
        <tr>
            <th>Item ID</th>
            <th>Item Name</th>
            <th>Cost (₱)</th>
            <th>Quantity</th>
            <th>Action</th>
        </tr>
        <?php foreach ($items as $id => $name) { ?>
        <tr>
            <form method="post">
                <td><?php print $id; ?></td>
                <td><?php print $name; ?></td>
                <td><?php print number_format($item_costs[$id]); ?></td>
                <td>
                    <input type="number" name="quantity" value="1" min="1" max="100" style="width: 60px;">
                    <input type="hidden" name="item_id" value="<?php print $id; ?>">
                </td>
                <td><input type="submit" name="order_item" value="Order"></td>
            </form>
        </tr>
        <?php } ?>
    </table>
</body>
</html>
