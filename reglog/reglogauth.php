<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "lunch");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["register"])) {
    $name = $_POST["registerName"];
    $username = $_POST["registerUsername"];
    $password = password_hash($_POST["registerPassword"], PASSWORD_BCRYPT);

    $result = $mysqli->query("SELECT id FROM user WHERE username='$username'");
    if ($result->num_rows > 0) {
        echo "Username already exists!";
    } else {
        $stmt = $mysqli->prepare("INSERT INTO user (name,username, password) VALUES (?,?,?)");
        $stmt->bind_param("sss",$name, $username, $password);
        if ($stmt->execute()) {
            echo "<script>alert('Registration Successful!'); window.location.href='reglog.html';</script>";
            header("Location: reglog.html");
        } else {
            echo "Error: " . $stmt->error;
        }
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login"])) {
    $username = $_POST["loginUsername"];
    $password = $_POST["loginPassword"];

    
    $result = $mysqli->query("SELECT * FROM user WHERE username='$username'");
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            define('BASE_URL', '/lunch/');
            header("Location:" . BASE_URL . "home/home.php");
            exit();
        } else {
            echo "<script>alert('Invalid password!'); window.location.href='reglog.html';</script>";
        }
    } else {
        echo "<script>alert('User not found!'); window.location.href='reglog.html';</script>";
    }
}
?>
