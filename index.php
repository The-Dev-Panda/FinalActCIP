<?php
// index.php
session_start();
include("db_connect.php");

// Check if user is logged in
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

// Sample items array
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

$item_costs = array(
    1 => 50000, 2 => 1500, 3 => 3000, 4 => 15000, 5 => 5000,
    6 => 8000, 7 => 4000, 8 => 25000, 9 => 30000, 10 => 1000,
    11 => 500, 12 => 8000, 13 => 12000, 14 => 6000, 15 => 8000,
    16 => 4000, 17 => 25000, 18 => 15000, 19 => 12000, 20 => 7000
);

// Handle item ordering
if (!empty($_POST['order_item'])) {
    $item_id = $_POST['item_id'];
    $quantity = $_POST['quantity'];
    $cost = $item_costs[$item_id];
    $amount = $cost * $quantity;
    
    $insert_order = "INSERT INTO orders (order_date, email, item, cost, quantity, amount) 
                     VALUES (CURDATE(), '$user_email', $item_id, $cost, $quantity, $amount)";
    mysqli_query($con, $insert_order);
    $order_message = "Order placed successfully!";
}

// Handle order deletion
if (!empty($_POST['delete_order'])) {
    $order_email = $_POST['order_email'];
    $order_date = $_POST['order_date'];
    $order_item = $_POST['order_item'];
    
    if ($user_type == 0) {
        // User can only delete their own orders
        $delete_query = "DELETE FROM orders WHERE email='$user_email' AND order_date='$order_date' AND item=$order_item";
    } else {
        // Admin can delete any order
        $delete_query = "DELETE FROM orders WHERE email='$order_email' AND order_date='$order_date' AND item=$order_item";
    }
    mysqli_query($con, $delete_query);
    $delete_message = "Order deleted successfully!";
}

// Handle admin bulk actions
if ($user_type == 1) {
    if (!empty($_POST['delete_all'])) {
        $delete_all_query = "DELETE FROM orders";
        mysqli_query($con, $delete_all_query);
        $bulk_message = "All orders deleted!";
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Shopping System</title>
</head>
<body>
    <h1>Welcome, <?php print $user_email; ?>!</h1>
    <p>User Type: <?php print ($user_type == 0) ? "Customer" : "Admin"; ?></p>
    
    <nav>
        <a href="logout.php">Logout</a>
    </nav>
    
    <?php if (isset($order_message)) { ?>
        <p style="color: green;"><?php print $order_message; ?></p>
    <?php } ?>
    
    <?php if (isset($delete_message)) { ?>
        <p style="color: green;"><?php print $delete_message; ?></p>
    <?php } ?>
    
    <?php if (isset($bulk_message)) { ?>
        <p style="color: green;"><?php print $bulk_message; ?></p>
    <?php } ?>

    <!-- Items Section -->
    <h2>Available Items</h2>
    <table border="1">
        <tr>
            <th>Item ID</th>
            <th>Item Name</th>
            <th>Cost</th>
            <th>Quantity</th>
            <th>Action</th>
        </tr>
        <?php foreach ($items as $id => $name) { ?>
        <tr>
            <form method="post">
                <td><?php print $id; ?></td>
                <td><?php print $name; ?></td>
                <td><?php print $item_costs[$id]; ?></td>
                <td>
                    <input type="number" name="quantity" value="1" min="1" max="100">
                    <input type="hidden" name="item_id" value="<?php print $id; ?>">
                </td>
                <td>
                    <input type="submit" name="order_item" value="Order">
                </td>
            </form>
        </tr>
        <?php } ?>
    </table>

    <!-- View Orders Section -->
    <h2><?php print ($user_type == 0) ? "My Orders" : "All Orders"; ?></h2>
    
    <!-- Search Form -->
    <form method="post">
        <table>
            <tr>
                <td>Search by Item Name:</td>
                <td><input type="text" name="search_item" value="<?php print isset($_POST['search_item']) ? $_POST['search_item'] : ''; ?>"></td>
                <?php if ($user_type == 1) { ?>
                <td>Search by Email:</td>
                <td><input type="text" name="search_email" value="<?php print isset($_POST['search_email']) ? $_POST['search_email'] : ''; ?>"></td>
                <?php } ?>
                <td><input type="submit" name="search_orders" value="Search"></td>
            </tr>
        </table>
    </form>

    <?php if ($user_type == 1) { ?>
    <!-- Admin Bulk Actions -->
    <form method="post">
        <input type="submit" name="delete_all" value="Delete All Orders" onclick="return confirm('Are you sure you want to delete all orders?')">
    </form>
    <?php } ?>

    <?php
    // Build search query
    if ($user_type == 0) {
        $orders_query = "SELECT * FROM orders WHERE email='$user_email'";
    } else {
        $orders_query = "SELECT * FROM orders WHERE 1=1";
    }
    
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
        
        if ($user_type == 1 && !empty($_POST['search_email'])) {
            $search_email = $_POST['search_email'];
            $orders_query .= " AND email LIKE '%$search_email%'";
        }
    }
    
    $orders_query .= " ORDER BY order_date DESC";
    $orders_result = mysqli_query($con, $orders_query);
    ?>

    <table border="1">
        <tr>
            <th>Order Date</th>
            <?php if ($user_type == 1) { ?>
            <th>Customer Email</th>
            <?php } ?>
            <th>Item</th>
            <th>Cost</th>
            <th>Quantity</th>
            <th>Total Amount</th>
            <th>Action</th>
        </tr>
        <?php
        if (mysqli_num_rows($orders_result) > 0) {
            while ($order = mysqli_fetch_array($orders_result, MYSQLI_ASSOC)) {
                ?>
                <tr>
                    <td><?php print $order['order_date']; ?></td>
                    <?php if ($user_type == 1) { ?>
                    <td><?php print $order['email']; ?></td>
                    <?php } ?>
                    <td><?php print $items[$order['item']]; ?></td>
                    <td><?php print $order['cost']; ?></td>
                    <td><?php print $order['quantity']; ?></td>
                    <td><?php print $order['amount']; ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="order_email" value="<?php print $order['email']; ?>">
                            <input type="hidden" name="order_date" value="<?php print $order['order_date']; ?>">
                            <input type="hidden" name="order_item" value="<?php print $order['item']; ?>">
                            <input type="submit" name="delete_order" value="Delete" onclick="return confirm('Are you sure?')">
                        </form>
                    </td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr>
                <td colspan="<?php print ($user_type == 1) ? '7' : '6'; ?>">No orders found</td>
            </tr>
            <?php
        }
        ?>
    </table>
</body>
</html>