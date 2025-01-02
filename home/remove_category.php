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

// Ensure that the user is logged in and cat_id is provided
if (isset($_SESSION['user_id']) && isset($_POST['cat_id'])) {
    $user_id = $_SESSION['user_id'];     // Logged-in user ID
    $cat_id = $_POST['cat_id'];          // Category ID to unfollow

    $delete_query = "DELETE FROM fav_cat WHERE user_id = ? AND cat_id = ?";

    // Prepare and bind the statement
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("ii", $user_id, $cat_id);  // Bind user_id and cat_id

    // Debug values
    error_log("Attempting to delete from fav_cat: user_id=$user_id, cat_id=$cat_id");

    if ($stmt->execute()) {
        error_log("Successfully deleted from fav_cat");
        // Redirect to home.php after successful unfollowing
        header("Location: /lunch/home/home.php");
        exit();
    } else {
        // Handle failure (optional)
        error_log("Error deleting from fav_cat: " . $stmt->error);
        echo "Error unfollowing category: " . $stmt->error;
    }

    $stmt->close();
} else {
    error_log("Missing required parameters: user_id=" . 
              (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set') . 
              ", cat_id=" . (isset($_POST['cat_id']) ? $_POST['cat_id'] : 'not set'));
}

$conn->close();
?>
