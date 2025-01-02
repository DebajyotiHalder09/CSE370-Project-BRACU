<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'lunch');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    header("Location: /lunch/reglog/reglog.html");
    exit();
}

$userId = $_SESSION['user_id'];

// Check if the user is already a seller
function checkIfSeller($conn, $userId) {
    $query = "SELECT * FROM seller WHERE seller_userid = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

if (!checkIfSeller($conn, $userId)) {
    $query = "INSERT INTO seller (seller_userid) VALUES (?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);

    if ($stmt->execute()) {
        // Redirect to profile page after successfully adding the user as a seller
        header("Location: /lunch/profile/profile.php");
        exit();
    } else {
        // Handle insertion error
        echo "Error: Unable to add you as a seller.";
    }
} else {
    // Redirect if the user is already a seller
    header("Location: /lunch/profile/profile.php");
    exit();
}
?>
