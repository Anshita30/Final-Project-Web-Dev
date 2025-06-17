<?php
session_start();

$_SESSION = []; // empty the superglobal session variablle

session_destroy();

// This removes the seession cookie
if (isset($_COOKIE['user_session'])) {
    setcookie('user_session', '', time() - 3600, '/'); // Set expiration in the past to delete the cookie
}

// This redirect to the login page
header("Location: index.php");
exit();
?>
