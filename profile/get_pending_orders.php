<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'lunch');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $pending_orders_query = "
        SELECT COUNT(oi.order_item_id) as pending_count
        FROM order_item oi
        JOIN item i ON oi.item_id = i.item_id
        JOIN seller s ON i.seller_id = s.seller_id
        WHERE s.seller_userid = ? 
        AND oi.status = 'pending'";
    
    $stmt = $conn->prepare($pending_orders_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo '<h3>Pending Orders</h3>';
        echo '<h2 class="text-danger">' . number_format($row['pending_count']) . '</h2>';
        
        if ($row['pending_count'] > 0) {
            echo '<script>
                    // Optional: Play a notification sound
                    // const audio = new Audio("notification.mp3");
                    // audio.play();
                  </script>';
        }
    }
}
?>