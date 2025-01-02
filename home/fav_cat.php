<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'lunch');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    if (!isset($_POST['cat_id']) && !isset($_GET['cat_id'])) {
        header("Location: /lunch/home/home.php");
        exit;
    }
    $cat_id = isset($_POST['cat_id']) ? $_POST['cat_id'] : $_GET['cat_id'];

    // Check if category already exists in fav_cat
    $check_query = "SELECT * FROM fav_cat WHERE user_id = ? AND cat_id = ?";
    $check_stmt = $conn->prepare($check_query);
    if (!$check_stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $check_stmt->bind_param("ii", $user_id, $cat_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows == 0) {
        // Insert into fav_cat
        $insert_query = "INSERT INTO fav_cat (user_id, cat_id) VALUES (?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        if (!$insert_stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $insert_stmt->bind_param("ii", $user_id, $cat_id);
        
        if ($insert_stmt->execute()) {
            header("Location: /lunch/home/home.php");
            exit();
        } else {
            echo "Error adding to favorites: " . $insert_stmt->error;
        }
    } else {
        echo "Category already in favorites!";
    }
} else {
    echo "Please log in to add favorites.";
}

$conn->close();
?>