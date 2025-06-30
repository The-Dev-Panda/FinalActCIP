<?php
session_start();
include("db_connect.php");

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: login.php");
    exit();
}

// Prevent back button after logout
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$user_email = $_SESSION['user_email'];
$user_type = $_SESSION['user_type'];

$items = array(
    1 => "Laptop",
    2 => "Mouse",
    3 => "Keyboard",
    4 => "Monitor",
    5 => "Headphones",
    6 => "Webcam",
    7 => "Speaker",
    8 => "Tablet",
    9 => "Phone",
    10 => "Charger",
    11 => "Cable",
    12 => "Router",
    13 => "Switch",
    14 => "Hard Drive",
    15 => "SSD",
    16 => "RAM",
    17 => "Graphics Card",
    18 => "Processor",
    19 => "Motherboard",
    20 => "Power Supply"
);

// Handle order deletion (users can only delete their own orders)
if (!empty($_POST['delete_order'])) {
    $order_date = $_POST['order_date'];
    $order_item = $_POST['order_item'];
    
    $delete_query = "DELETE FROM orders WHERE email='$user_email' AND order_date='$order_date' AND item=$order_item";
    mysqli_query($con, $delete_query);
    $delete_message = "Order deleted successfully!";
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>My Orders - Shopping System</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .nav { margin-bottom: 20px; }
        .nav a { margin-right: 15px; text-decoration: none; color: #007bff; }
        .nav a:hover { text-decoration: underline; }
        .message { color: green; font-weight: bold; }
        .search-form { margin-bottom: 20px; }
        .search-form input[type="text"] { padding: 5px; margin-right: 10px; }
    </style>
</head>
<body>
    <h1>My Orders</h1>
    <p>Welcome, <?php echo $user_email; ?>!</p>
    <p>User Type: <?php echo ($user_type == 0) ? "Customer" : "Admin"; ?></p>
    
    <div class="nav">
        <a href="order.php">Place New Order</a>
        <?php if ($user_type == 1) { ?>
            <a href="admin.php">Admin Panel</a>
        <?php } ?>
        <a href="logout.php">Logout</a>
    </div>
    
    <?php if (isset($delete_message)) { ?>
        <p class="message"><?php echo $delete_message; ?></p>
    <?php } ?>

    <!-- Search Form -->
    <div class="search-form">
        <form method="post">
            <label>Search by Item Name:</label>
            <input type="text" name="search_item" value="<?php echo isset($_POST['search_item']) ? $_POST['search_item'] : ''; ?>" placeholder="Enter item name">
            <input type="submit" name="search_orders" value="Search">
            <input type="submit" name="show_all" value="Show All">
        </form>
    </div>

    <?php
    // Build search query for user's orders only
    $orders_query = "SELECT * FROM orders WHERE email='$user_email'";
    
    // Add search filters
    if (!empty($_POST['search_orders'])) {
        if (!empty($_POST['search_item'])) {
            $search_item = $_POST['search_item'];
            $item_search_ids = array();
            foreach ($items as $id => $name) {
                if (stripos($name, $search_item) !== false) {
                    $item_search_ids[] = $id;
                }
            }
            if (!empty($item_search_ids)) {
                $orders_query .= " AND item IN (" . implode(',', $item_search_ids) . ")";
            } else {
                $orders_query .= " AND item = -1"; // No results
            }
        }
    }
    
    $orders_query .= " ORDER BY order_date DESC";
    $orders_result = mysqli_query($con, $orders_query);
    ?>

    <h2>Order History</h2>
    <table>
        <tr>
            <th>Order Date</th>
            <th>Item</th>
            <th>Cost (₱)</th>
            <th>Quantity</th>
            <th>Total Amount (₱)</th>
            <th>Action</th>
        </tr>
        <?php
        if (mysqli_num_rows($orders_result) > 0) {
            $total_amount = 0;
            while ($order = mysqli_fetch_array($orders_result, MYSQLI_ASSOC)) {
                $total_amount += $order['amount'];
                ?>
                <tr>
                    <td><?php echo $order['order_date']; ?></td>
                    <td><?php echo $items[$order['item']]; ?></td>
                    <td><?php echo number_format($order['cost']); ?></td>
                    <td><?php echo $order['quantity']; ?></td>
                    <td><?php echo number_format($order['amount']); ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="order_date" value="<?php echo $order['order_date']; ?>">
                            <input type="hidden" name="order_item" value="<?php echo $order['item']; ?>">
                            <input type="submit" name="delete_order" value="Delete">
                        </form>
                    </td>
                </tr>
                <?php
            }
            ?>
            <tr style="background-color: #f8f9fa; font-weight: bold;">
                <td colspan="4">Total Spent:</td>
                <td><?php echo number_format($total_amount); ?></td>
                <td></td>
            </tr>
            <?php
        } else {
            ?>
            <tr>
                <td colspan="6">No orders found</td>
            </tr>
            <?php
        }
        ?>
    </table>
</body>
</html>