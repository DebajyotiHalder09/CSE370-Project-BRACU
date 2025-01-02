<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'lunch');

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    if (!isset($_POST['seller_id'])) {
        echo "Seller ID is missing!";
        exit;
    }
    $seller_id = $_POST['seller_id'];

    // First, check if the user is trying to follow themselves
    $self_check_query = "SELECT s.seller_id 
                        FROM seller s 
                        WHERE s.seller_userid = ? AND s.seller_id = ?";
    $self_check_stmt = $conn->prepare($self_check_query);
    $self_check_stmt->bind_param("ii", $user_id, $seller_id);
    $self_check_stmt->execute();
    $self_check_result = $self_check_stmt->get_result();

    if ($self_check_result->num_rows > 0) {
        echo "You cannot follow yourself!";
        exit;
    }

    // If not following self, proceed with regular follow process
    $check_query = "SELECT ff.*, u.name as seller_name 
                   FROM fav_follower ff
                   JOIN seller s ON ff.seller_id = s.seller_id
                   JOIN user u ON s.seller_userid = u.id
                   WHERE ff.user_id = ? AND ff.seller_id = ?";
    
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("ii", $user_id, $seller_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $row = $check_result->fetch_assoc();
        echo "You are already following " . htmlspecialchars($row['seller_name']) . "!";
    } else {
        // Get seller name before insertion
        $seller_query = "SELECT u.name as seller_name 
                        FROM seller s 
                        JOIN user u ON s.seller_userid = u.id 
                        WHERE s.seller_id = ?";
        $seller_stmt = $conn->prepare($seller_query);
        $seller_stmt->bind_param("i", $seller_id);
        $seller_stmt->execute();
        $seller_result = $seller_stmt->get_result();
        
        if ($seller_result->num_rows > 0) {
            $seller_row = $seller_result->fetch_assoc();
            
            // Insert into fav_follower table
            $insert_query = "INSERT INTO fav_follower (user_id, seller_id) VALUES (?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("ii", $user_id, $seller_id);

            if ($insert_stmt->execute()) {
                //echo "Successfully followed " . htmlspecialchars($seller_row['seller_name']);
                header("Location: /lunch/home/home.php");
                exit();
            } else {
                echo "Error following seller: " . $insert_stmt->error;
            }
        } else {
            echo "Seller not found!";
        }
    }
} else {
    echo "Please log in to follow sellers.";
}

$conn->close();
?>