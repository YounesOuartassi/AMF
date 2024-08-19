<?php

include('db_connect.php'); 

session_start(); // Start a session to store user data

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect and sanitize form data
    $email = htmlspecialchars(trim($_POST['email']));
    $password = htmlspecialchars(trim($_POST['password']));
    
    // Prepare SQL statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT user_id, password_hash FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows == 1) {
        // Bind result variables
        $stmt->bind_result($user_id, $password_hash);
        $stmt->fetch();
        
        // Verify password
        if (password_verify($password, $password_hash)) {
            // Password is correct
            $_SESSION['user_id'] = $user_id;
            $_SESSION['email'] = $email;
            
            // Redirect to a different page (e.g., homepage)
            header("Location: index.php"); // Change this to the desired page
            exit();
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "No account found with that email address.";
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
	<link rel="stylesheet" href="css/style.css"></head>
<body>
    <div id="login-form">
        <h2>Login</h2>
        <form action="login.php" method="post">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            
            <input type="submit" value="Login">
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</body>
</html>
