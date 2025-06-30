<?php
session_start();

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    // Redirect based on user type
    if ($_SESSION['user_type'] == 1) {
        header("Location: admin.php");
    } else {
        header("Location: order.php");
    }
    exit();
}

if (!empty($_POST['login'])) {
    include("db_connect.php");

    $e = trim($_POST['email']);
    $p = trim($_POST['password']);

    $e_safe = mysqli_real_escape_string($con, $e);
    $query = "SELECT password, type FROM users WHERE email = '$e_safe' LIMIT 1";
    $result = mysqli_query($con, $query);

    if (mysqli_num_rows($result) === 0) {
        $error = "Email doesn't exist";
    } else {
        $row = mysqli_fetch_assoc($result);
        $fpw = $row['password'];
        $user_type = $row['type'];

        if (password_verify($p, $fpw)) {
            $_SESSION['logged_in'] = true;
            $_SESSION['user_email'] = $e;
            $_SESSION['user_type'] = $user_type;

            // Redirect based on user type
            if ($user_type == 1) {
                header("Location: admin.php");
            } else {
                header("Location: order.php");
            }
            exit();
        } else {
            $error = "Wrong password";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 50px auto; width: 400px; }
        table { width: 100%; }
        td { padding: 10px; }
        input[type="email"], input[type="password"] { width: 100%; padding: 8px; }
        input[type="submit"] { padding: 10px 20px; background-color: 
#007bff; color: white; border: none; cursor: pointer; }
        input[type="submit"]:hover { background-color: 
#0056b3; }
        .error { color: red; font-weight: bold; }
        .center { text-align: center; }
    </style>
</head>
<body>
    <h1 class="center">Login Page</h1>
    <?php if (isset($error)) { ?>
        <p class="error"><?php echo $error; ?></p>
    <?php } ?>
    <form method="post">
        <table>
            <tr>
                <td>Email:</td>
                <td><input type="email" name="email" required></td>
            </tr>
            <tr>
                <td>Password:</td>
                <td><input type="password" name="password" required></td>
            </tr>
            <tr>
                <td colspan="2" class="center">
                    <input type="submit" name="login" value="Login">
                </td>
            </tr>
        </table>
    </form>
    <p class="center"><a href="register.php">Don't have an account? Register here</a></p>
</body>
</html>