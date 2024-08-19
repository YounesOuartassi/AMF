<?php
// Include database configuration
include('db_connect.php'); // Ensure this file contains your database connection settings

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect and sanitize form data
    $first_name = htmlspecialchars(trim($_POST['first_name']));
    $last_name = htmlspecialchars(trim($_POST['last_name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = htmlspecialchars(trim($_POST['password']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $address = htmlspecialchars(trim($_POST['address']));
    $postal_code = htmlspecialchars(trim($_POST['postal_code']));
    $city = htmlspecialchars(trim($_POST['city']));
    
    // Hash the password
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    
    // Prepare SQL statement to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, password_hash, email, phone, address, postal_code, city) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $first_name, $last_name, $password_hash, $email, $phone, $address, $postal_code, $city);

    // Execute the statement
    if ($stmt->execute()) {
        echo "Registration successful!";
    } else {
        echo "Error: " . $stmt->error;
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
    <title>Register</title>
	<link rel="stylesheet" href="css/style.css"></head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<body>
    <!-- <div id="registration-form">
        <h2>Register</h2>
        <form action="register.php" method="post">
            <label for="first_name">First Name:</label>
            <input type="text" id="first_name" name="first_name" required>
            
            <label for="last_name">Last Name:</label>
            <input type="text" id="last_name" name="last_name" required>
            
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            
            <label for="phone">Phone (optional):</label>
            <input type="text" id="phone" name="phone">
            
            <label for="address">Address (optional):</label>
            <textarea id="address" name="address"></textarea>
            
            <label for="postal_code">Postal Code (optional):</label>
            <input type="text" id="postal_code" name="postal_code">
            
            <label for="city">City (optional):</label>
            <input type="text" id="city" name="city">
            
            <input type="submit" value="Register">
        </form>
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div> -->
    <div class="container">
    <h2>Popup Contact Form with Email</h2>
    
    <!-- Trigger/Open The Modal -->
    <button id="mbtn" class="btn btn-primary turned-button">Contact Us</button>
    
    <!-- The Modal -->
    <div id="modalDialog" class="modal">
        <div class="modal-content animate-top">
            <div class="modal-header">
                <h5 class="modal-title">Contact Us</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form method="post" id="contactFrm">
            <div class="modal-body">
                <!-- Form submission status -->
                <div class="response"></div>
                
                <!-- Contact form -->
                <div class="form-group">
                    <label>Name:</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="Enter your name" required="">
                </div>
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="Enter your email" required="">
                </div>
                <div class="form-group">
                    <label>Message:</label>
                    <textarea name="message" id="message" class="form-control" placeholder="Your message here" rows="6"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <!-- Submit button -->
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
            </form>
        </div>
    </div>
</div>
    <script>
/*
 * Modal popup
 */
// Get the modal
var modal = $('#modalDialog');

// Get the button that opens the modal
var btn = $("#mbtn");

// Get the  element that closes the modal
var span = $(".close");

$(document).ready(function(){
    // When the user clicks the button, open the modal 
    btn.on('click', function() {
        modal.show();
    });
    
    // When the user clicks on  (x), close the modal
    span.on('click', function() {
        modal.hide();
    });
});

// When the user clicks anywhere outside of the modal, close it
$('body').bind('click', function(e){
    if($(e.target).hasClass("modal")){
        modal.hide();
    }
});
</script>
</body>
</html>
