<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'lunch');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    if (!isset($_POST['item_id'])) {
        echo "Item ID is missing!";
        exit;
    }
    $item_id = $_POST['item_id'];

    $cart_query = "SELECT cart_id FROM cart WHERE user_id = ?";
    $cart_stmt = $conn->prepare($cart_query);
    $cart_stmt->bind_param("i", $user_id);
    $cart_stmt->execute();
    $cart_result = $cart_stmt->get_result();

    if ($cart_result->num_rows == 0) {
        $create_cart = "INSERT INTO cart (user_id) VALUES (?)";
        $create_stmt = $conn->prepare($create_cart);
        $create_stmt->bind_param("i", $user_id);
        $create_stmt->execute();
        $cart_id = $conn->insert_id;
    } else {
        $cart_row = $cart_result->fetch_assoc();
        $cart_id = $cart_row['cart_id'];
    }

    $check_query = "SELECT quantity FROM cart_item WHERE cart_id = ? AND item_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("ii", $cart_id, $item_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $update_query = "UPDATE cart_item SET quantity = quantity + 1 WHERE cart_id = ? AND item_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ii", $cart_id, $item_id);
        
        if ($update_stmt->execute()) {
            header("Location: /lunch/home/home.php");
            exit();
        } else {
            echo "Error updating cart: " . $update_stmt->error;
        }
    } else {
        $price_query = "SELECT item_price FROM item WHERE item_id = ?";
        $price_stmt = $conn->prepare($price_query);
        $price_stmt->bind_param("i", $item_id);
        $price_stmt->execute();
        $price_result = $price_stmt->get_result();
        
        if ($price_result->num_rows > 0) {
            $price_row = $price_result->fetch_assoc();
            
            $insert_query = "INSERT INTO cart_item (cart_id, item_id, quantity, item_price) VALUES (?, ?, 1, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("iid", $cart_id, $item_id, $price_row['item_price']);
            
            if ($insert_stmt->execute()) {
                header("Location: /lunch/home/home.php");
                exit();
            } else {
                echo "Error adding to cart: " . $insert_stmt->error;
            }
        } else {
            echo "Item not found!";
        }
    }
} else {
    echo "Please log in to add items to cart.";
}

$conn->close();
?>