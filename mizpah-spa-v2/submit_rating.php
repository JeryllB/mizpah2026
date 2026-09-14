<?php
include 'includes/db.php';

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $name = trim(mysqli_real_escape_string($conn, $_POST['name']));
    $rating = (int) $_POST['rating'];
    $message = trim(mysqli_real_escape_string($conn, $_POST['message']));

    // validation
    if($name == "" || $rating < 1 || $rating > 5 || $message == ""){
        header("Location: index.php?error=invalid");
        exit;
    }

    mysqli_query($conn, "
        INSERT INTO ratings (name, rating, message, created_at, status)
        VALUES ('$name', '$rating', '$message', NOW(), 'approved')
    ");

    header("Location: index.php?success=1");
    exit;
}
?>