<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$conn = new mysqli('localhost', 'root', '', 'lunch');

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure that the user is logged in and seller_id is provided
if (isset($_SESSION['user_id']) && isset($_POST['seller_id'])) {
    $user_id = $_SESSION['user_id'];     // Logged-in user ID
    $seller_id = $_POST['seller_id'];    // Seller ID to unfollow
    
    // Changed table name from 'favorites' to 'fav_follower'
    $delete_query = "DELETE FROM fav_follower WHERE user_id = ? AND seller_id = ?";
    
    // Prepare and bind the statement
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("ii", $user_id, $seller_id);  // Bind user_id and seller_id
    
    // Debug values
    error_log("Attempting to delete from fav_follower: user_id=$user_id, seller_id=$seller_id");
    
    if ($stmt->execute()) {
        error_log("Successfully deleted from fav_follower");
        // Redirect to home.php after successful unfollowing
        header("Location: /lunch/home/home.php");
        exit();
    } else {
        // Handle failure (optional)
        error_log("Error deleting from fav_follower: " . $stmt->error);
        echo "Error unfollowing seller: " . $stmt->error;
    }
    
    $stmt->close();
} else {
    error_log("Missing required parameters: user_id=" . 
              (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set') . 
              ", seller_id=" . (isset($_POST['seller_id']) ? $_POST['seller_id'] : 'not set'));
}

$conn->close();
?>