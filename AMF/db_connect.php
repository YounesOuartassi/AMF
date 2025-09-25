<?php
$servername = "sql205.infinityfree.com"; 
$username = "if0_38962896";        
$password = "Omwgr1nd";           
$dbname = "if0_38962896_amf"; 

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
