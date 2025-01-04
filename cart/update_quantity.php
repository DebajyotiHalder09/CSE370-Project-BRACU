<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /lunch/cart/cart.php");
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'lunch');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if necessary POST data is present
if (isset($_POST['cart_id'], $_POST['item_id'], $_POST['action'])) {
    $cart_id = (int)$_POST['cart_id'];
    $item_id = (int)$_POST['item_id'];
    $action = $_POST['action'];

    // Verify the cart belongs to the logged-in user
    $verify_query = "SELECT c.cart_id 
                    FROM cart c 
                    WHERE c.cart_id = ? AND c.user_id = ?";
    $verify_stmt = $conn->prepare($verify_query);
    $verify_stmt->bind_param("ii", $cart_id, $_SESSION['user_id']);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();

    if ($verify_result->num_rows > 0) {
        // Get current quantity
        $quantity_query = "SELECT quantity FROM cart_item WHERE cart_id = ? AND item_id = ?";
        $quantity_stmt = $conn->prepare($quantity_query);
        $quantity_stmt->bind_param("ii", $cart_id, $item_id);
        $quantity_stmt->execute();
        $quantity_result = $quantity_stmt->get_result();
        
        if ($quantity_result->num_rows > 0) {
            $current_quantity = $quantity_result->fetch_assoc()['quantity'];
            $new_quantity = $current_quantity;

            if ($action === 'increase' && $current_quantity < 99) {
                $new_quantity = $current_quantity + 1;
            } elseif ($action === 'decrease' && $current_quantity > 1) {
                $new_quantity = $current_quantity - 1;
            }

            // Update the quantity in the database
            if ($new_quantity !== $current_quantity) {
                $update_query = "UPDATE cart_item SET quantity = ? WHERE cart_id = ? AND item_id = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param("iii", $new_quantity, $cart_id, $item_id);
                
                if ($update_stmt->execute()) {
                    // Success
                    header("Location: /lunch/cart/cart.php");
                    exit();
                } else {
                    // Database error
                    $_SESSION['error'] = "Error updating quantity";
                }
            }
        }
    }
}

header("Location: /lunch/cart/cart.php");
exit();