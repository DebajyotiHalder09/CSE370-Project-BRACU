<?php
session_start();

$conn = new mysqli('localhost', 'root', '', 'lunch');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    echo "Please log in to place an order.";
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['cart_items']) && !empty($_POST['cart_items'])) {
    $cart_items = json_decode($_POST['cart_items'], true);
    
    $conn->begin_transaction();

    try {
        $order_query = "INSERT INTO orders (user_id, order_date) VALUES (?, NOW())";
        $stmt = $conn->prepare($order_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $order_id = $stmt->insert_id;

        $total_earnings_by_seller = [];

        $order_items_query = "INSERT INTO order_item (order_id, item_id, quantity, item_price) VALUES (?, ?, ?, ?)";
        $order_items_stmt = $conn->prepare($order_items_query);

        foreach ($cart_items as $cart_item) {
            $item_id = $cart_item['item_id'];
            $quantity = $cart_item['quantity'];
            $price = $cart_item['price'];
            $seller_id = $cart_item['seller_id'];

            $order_items_stmt->bind_param("iiid", $order_id, $item_id, $quantity, $price);
            $order_items_stmt->execute();

            $total_earnings = $quantity * $price;
            if (!isset($total_earnings_by_seller[$seller_id])) {
                $total_earnings_by_seller[$seller_id] = 0;
            }
            $total_earnings_by_seller[$seller_id] += $total_earnings;
        }

        foreach ($total_earnings_by_seller as $seller_id => $earnings) {
            $update_earnings_query = "UPDATE seller SET total_earnings = total_earnings + ? WHERE seller_id = ?";
            $update_earnings_stmt = $conn->prepare($update_earnings_query);
            $update_earnings_stmt->bind_param("di", $earnings, $seller_id);
            $update_earnings_stmt->execute();
        }

        $conn->commit();

        $cart_query = "DELETE FROM cart_item WHERE cart_id IN (SELECT cart_id FROM cart WHERE user_id = ?)";
        $cart_stmt = $conn->prepare($cart_query);
        $cart_stmt->bind_param("i", $user_id);
        $cart_stmt->execute();

        echo "Your order has been placed successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error placing order: " . $e->getMessage();
    }
} else {
    echo "No items in the cart to place an order.";
}

$conn->close();
?>
