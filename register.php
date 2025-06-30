<?php
session_start();

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    header("Location: index.php");
    exit();
}

if (!empty($_POST['signup'])) {
    include("db_connect.php");

    $e = trim($_POST['email']);
    $p = trim($_POST['password']);
    $hash = password_hash($p, PASSWORD_DEFAULT);

    $e_safe = mysqli_real_escape_string($con, $e);
    $check = "SELECT email FROM users WHERE email = '$e_safe'";
    $result = mysqli_query($con, $check);

    if (mysqli_num_rows($result) === 0) {
        $hash_safe = mysqli_real_escape_string($con, $hash);
        $insert = "INSERT INTO users (email, password, type) VALUES ('$e_safe', '$hash_safe', 0)";
        mysqli_query($con, $insert);
        $message = "Registration successful! You can now login.";
    } else {
        $error = "Email already exists";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>
    <h1>Register</h1>
    <?php if (isset($error)) { ?>
        <p style="color: red;"><?php print $error; ?></p>
    <?php } ?>
    <?php if (isset($message)) { ?>
        <p style="color: green;"><?php print $message; ?></p>
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
                <td colspan="2">
                    <input type="submit" name="signup" value="Register">
                </td>
            </tr>
        </table>
    </form>
    <p><a href="login.php">Already have an account? Login here</a></p>
</body>
</html>