<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'lunch');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_SESSION['user_id']) && isset($_POST['item_id']) && isset($_POST['cart_id'])) {
    $cart_id = $_POST['cart_id'];
    $item_id = $_POST['item_id'];
    
    $delete_query = "DELETE FROM cart_item WHERE cart_id = ? AND item_id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("ii", $cart_id, $item_id);
    
    if ($delete_stmt->execute()) {
        header("Location: /lunch/cart/cart.php");
        exit();
    } else {
        echo "Error removing item: " . $delete_stmt->error;
    }
} else {
    echo "Invalid request";
}

$conn->close();
?>