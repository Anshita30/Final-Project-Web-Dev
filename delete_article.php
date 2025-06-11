<?php
require 'connect.php'; // Here, we include the database connections
session_start();

// Here, we check if the user is logged in and an admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Access denied.");
}

// This is done to get the movie ID from the URL
$movie_id = intval($_GET['id']);

// We handle all the deletion process in a try-catch block here
try {
    $stmt = $pdo->prepare("DELETE FROM movies WHERE id = :id");
    $stmt->execute([':id' => $movie_id]);

    echo "Movie deleted successfully.";
    // From here, we can Redirect to the admin dashboard or another page
    header("Location: admin_dashboard.php");
    exit();
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
