<?php
// These are the Database connection variables
$host = 'localhost';
$db = 'final_project'; // Here, we set the database name as "serverside" according to the CRUD notes.
$username = 'root';  // Here, we set username as "serveruser" according to the CRUD notes.
$password = 'ups98ups';  // Here, we set password as "gorgonzola7!" according to the CRUD nots.

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // here, we Set the error mode to Exceptions.
} catch (PDOException $e) {
    // Here, we handle connection error
    die("Database connection failed: " . $e->getMessage());
}
?>
