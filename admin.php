<?php
session_start();
include("db_connect.php");

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || $_SESSION['user_type'] != 1) {
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

if (!empty($_POST['delete_order'])) {
    $order_email = $_POST['order_email'];
    $order_date = $_POST['order_date'];
    $order_item = $_POST['order_item'];
    
    $delete_query = "DELETE FROM orders WHERE email='$order_email' AND order_date='$order_date' AND item=$order_item";
    mysqli_query($con, $delete_query);
    $delete_message = "Order deleted successfully!";
}

if (!empty($_POST['delete_all'])) {
    $delete_all_query = "DELETE FROM orders";
    mysqli_query($con, $delete_all_query);
    $bulk_message = "All orders deleted successfully!";
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel - Shopping System</title>
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
        .bulk-actions { margin-bottom: 20px; padding: 10px; background-color: #f8f9fa; border: 1px solid #ddd; }
        .danger-btn { background-color: #dc3545; color: white; padding: 8px 16px; border: none; cursor: pointer; }
        .danger-btn:hover { background-color: #c82333; }
        .stats { margin-bottom: 20px; padding: 15px; background-color: #e9ecef; border-radius: 5px; }
        .stats div { display: inline-block; margin-right: 30px; }
    </style>
</head>
<body>
    <h1>Admin Panel</h1>
    <p>Welcome, Administrator <?php echo $user_email; ?>!</p>
    
    <div class="nav">
        <a href="order.php">Place Order</a>
        <a href="view_orders.php">My Orders</a>
        <a href="logout.php">Logout</a>
    </div>
    
    <?php if (isset($delete_message)) { ?>
        <p class="message"><?php echo $delete_message; ?></p>
    <?php } ?>
    
    <?php if (isset($bulk_message)) { ?>
        <p class="message"><?php echo $bulk_message; ?></p>
    <?php } ?>

    <?php
    $total_orders_query = "SELECT COUNT(*) as total_orders FROM orders";
    $total_orders_result = mysqli_query($con, $total_orders_query);
    $total_orders = mysqli_fetch_array($total_orders_result)['total_orders'];

    $total_revenue_query = "SELECT SUM(amount) as total_revenue FROM orders";
    $total_revenue_result = mysqli_query($con, $total_revenue_query);
    $total_revenue = mysqli_fetch_array($total_revenue_result)['total_revenue'];

    $unique_customers_query = "SELECT COUNT(DISTINCT email) as unique_customers FROM orders";
    $unique_customers_result = mysqli_query($con, $unique_customers_query);
    $unique_customers = mysqli_fetch_array($unique_customers_result)['unique_customers'];
    ?>

    <div class="stats">
        <h3>System Statistics</h3>
        <div><strong>Total Orders:</strong> <?php echo $total_orders; ?></div>
        <div><strong>Total Revenue:</strong> ₱<?php echo number_format($total_revenue ? $total_revenue : 0); ?></div>
        <div><strong>Unique Customers:</strong> <?php echo $unique_customers; ?></div>
    </div>

    <div class="search-form">
        <form method="post">
            <label>Search by Item Name:</label>
            <input type="text" name="search_item" value="<?php echo isset($_POST['search_item']) ? $_POST['search_item'] : ''; ?>" placeholder="Enter item name">
            
            <label>Search by Email:</label>
            <input type="text" name="search_email" value="<?php echo isset($_POST['search_email']) ? $_POST['search_email'] : ''; ?>" placeholder="Enter customer email">
            
            <input type="submit" name="search_orders" value="Search">
            <input type="submit" name="show_all" value="Show All">
        </form>
    </div>

    <div class="bulk-actions">
        <h3>Bulk Actions</h3>
        <form method="post" style="display: inline;">
            <input type="submit" name="delete_all" value="Delete All Orders" class="danger-btn" onclick="return confirm('Are you sure you want to delete ALL orders? This action cannot be undone!');">
        </form>
    </div>

    <?php
    $orders_query = "SELECT * FROM orders WHERE 1=1";
    
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
                $orders_query .= " AND item = -1"; 
            }
        }
        
        if (!empty($_POST['search_email'])) {
            $search_email = $_POST['search_email'];
            $orders_query .= " AND email LIKE '%$search_email%'";
        }
    }
    
    $orders_query .= " ORDER BY order_date DESC";
    $orders_result = mysqli_query($con, $orders_query);
    ?>

    <h2>All System Orders</h2>
    <table>
        <tr>
            <th>Order Date</th>
            <th>Customer Email</th>
            <th>Item</th>
            <th>Cost (₱)</th>
            <th>Quantity</th>
            <th>Total Amount (₱)</th>
            <th>Action</th>
        </tr>
        <?php
        if (mysqli_num_rows($orders_result) > 0) {
            $displayed_total = 0;
            while ($order = mysqli_fetch_array($orders_result, MYSQLI_ASSOC)) {
                $displayed_total += $order['amount'];
                ?>
                <tr>
                    <td><?php echo $order['order_date']; ?></td>
                    <td><?php echo $order['email']; ?></td>
                    <td><?php echo $items[$order['item']]; ?></td>
                    <td><?php echo number_format($order['cost']); ?></td>
                    <td><?php echo $order['quantity']; ?></td>
                    <td><?php echo number_format($order['amount']); ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="order_email" value="<?php echo $order['email']; ?>">
                            <input type="hidden" name="order_date" value="<?php echo $order['order_date']; ?>">
                            <input type="hidden" name="order_item" value="<?php echo $order['item']; ?>">
                            <input type="submit" name="delete_order" value="Delete" onclick="return confirm('Are you sure you want to delete this order?');">
                        </form>
                    </td>
                </tr>
                <?php
            }
            ?>
            <tr style="background-color: #f8f9fa; font-weight: bold;">
                <td colspan="5">Displayed Total:</td>
                <td><?php echo number_format($displayed_total); ?></td>
                <td></td>
            </tr>
            <?php
        } else {
            ?>
            <tr>
                <td colspan="7">No orders found</td>
            </tr>
            <?php
        }
        ?>
    </table>
</body>
</html>