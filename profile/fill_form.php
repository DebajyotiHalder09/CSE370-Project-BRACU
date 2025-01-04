<?php
session_start();

$conn = new mysqli('localhost', 'root', '', 'lunch');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    die("Please log in to continue.");
}

$userid = $_SESSION['user_id'];
$username = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $item_name = trim($conn->real_escape_string($_POST['item_name']));
    $price = filter_var($_POST['price'], FILTER_VALIDATE_INT);
    $category = trim($conn->real_escape_string($_POST['category']));
    
    if ($price === false || $price < 0) {
        die("Invalid price value");
    }
    
    $seller_query = "SELECT seller_id FROM seller WHERE seller_userid = ?";
    $stmt = $conn->prepare($seller_query);
    $stmt->bind_param("i", $userid);
    $stmt->execute();
    $seller_result = $stmt->get_result();
    
    if ($seller_result->num_rows > 0) {
        $seller_row = $seller_result->fetch_assoc();
        $seller_id = $seller_row['seller_id'];
        
        $conn->begin_transaction();
        
        try {
            $item_query = "INSERT INTO item (seller_id, item_name, item_price) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($item_query);
            $stmt->bind_param("isi", $seller_id, $item_name, $price);
            
            if (!$stmt->execute()) {
                throw new Exception("Error inserting item: " . $stmt->error);
            }
            
            $item_id = $conn->insert_id;
            
            $cat_query = "SELECT cat_id FROM item_catagory WHERE cat_name = ?";
            $stmt = $conn->prepare($cat_query);
            $stmt->bind_param("s", $category);
            $stmt->execute();
            $cat_result = $stmt->get_result();
            
            if ($cat_result->num_rows > 0) {
                $cat_row = $cat_result->fetch_assoc();
                $cat_id = $cat_row['cat_id'];
            } else {
                $cat_insert_query = "INSERT INTO item_catagory (cat_name) VALUES (?)";
                $stmt = $conn->prepare($cat_insert_query);
                $stmt->bind_param("s", $category);
                
                if (!$stmt->execute()) {
                    throw new Exception("Error inserting category: " . $stmt->error);
                }
                
                $cat_id = $conn->insert_id;
            }
            
            $store_query = "INSERT INTO catagory_store (seller_id, cat_id, item_id) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($store_query);
            $stmt->bind_param("iii", $seller_id, $cat_id, $item_id);
            
            if (!$stmt->execute()) {
                throw new Exception("Error inserting into category store: " . $stmt->error);
            }
            
            $conn->commit();
            
            define('BASE_URL', '/lunch/');
            header("Location:" . BASE_URL . "profile/profile.php");
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            die($e->getMessage());
        }
    } else {
        die("Seller not found for the logged-in user.");
    }
}

$conn->close();
?>