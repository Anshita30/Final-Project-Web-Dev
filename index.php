<?php 
require 'connect.php';
session_start();

// Handle users login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Fetch users details from the database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    if ($user && $password === $user['password']) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];

        // Save session in a cookie
        setcookie("user_session", session_id(), time() + (86400 * 30), "/"); // 30 days

        if ($user['role'] === 'admin') {
            header("Location: admin_dashboard.php"); // Redirect to admin dashboard
            exit();
        } else {
            header("Location: user_dashboard.php"); // Redirect to user dashboard
            exit();
        }
    } else {
        $error_message = "Invalid username or password.";
    }
}

// Handle user sign-up
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $signup_error = "Passwords do not match. Please try again.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, 'user')");
            $stmt->execute([
                ':username' => $name,   
                ':email' => $email,
                ':password' => $password,
            ]);
            $signup_success = "Account created successfully. Please log in. Auto Redirecting in 2 Seconds";
        } catch (PDOException $e) {
            $signup_error = "Error: " . $e->getMessage();
        }

        header('Refresh: 2; URL=index.php'); //redirect to index for sign in after signup of a new users, after 2 sec. it doesnt work with 'location' header? why?
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie Vault - Login & Sign Up</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #ecd9ba;
            color: #333;
        }
        .container {
            width: 100%;
            max-width: 400px;
            margin: 50px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo img {
            width: auto;
            height: 200px;
        }
        h1 {
            text-align: center;
            font-size: 24px;
            margin-bottom: 20px;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-bottom: 5px;
            font-weight: bold;
        }
        input {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            padding: 10px;
            font-size: 16px;
            background-color: #dbf3ff;
            border: 1px solid #333;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #2961eb;
        }
        .toggle-link {
            text-align: center;
            margin-top: 10px;
        }
        .toggle-link a {
            text-decoration: none;
            color: #007185;
            font-weight: bold;
        }
        .error-message, .success-message {
            text-align: center;
            font-size: 14px;
            margin-bottom: 15px;
        }
        .error-message {
            color: red;
        }
        .success-message {
            color: green;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="moviemax_logo.png" alt="Logo">
        </div>
        <?php if (!isset($_GET['signup'])): ?>
            <h1>Sign In</h1>
            <?php if (isset($error_message)): ?>
                <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
            <?php endif; ?>
            <form method="POST" action="index.php">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>

                <button type="submit" name="login">Continue</button>
            </form>
            <div class="toggle-link">
                New to Movie Vault? <a href="index.php?signup=true">Create your account</a>
            </div>
        <?php else: ?>
            <h1>Create Account</h1>
            <?php if (isset($signup_success)): ?>
                <p class="success-message"><?php echo htmlspecialchars($signup_success); ?></p>
            <?php elseif (isset($signup_error)): ?>
                <p class="error-message"><?php echo htmlspecialchars($signup_error); ?></p>
            <?php endif; ?>
            <form method="POST" action="index.php?signup=true">
                <label for="name">Your username</label>
                <input type="text" id="name" name="name" required>

                <label for="email">Email address</label>
                <input type="email" id="email" name="email" required>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>

                <label for="confirm_password">Re-enter Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>

                <button type="submit" name="signup">Continue</button>
            </form>
            <div class="toggle-link">
                Already have an account? <a href="index.php">Sign in</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
