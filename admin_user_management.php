<?php
require 'connect.php'; 
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Access denied.");
}

// Fetching all the registered users which are sorted by their ID
$user_stmt = $pdo->prepare("SELECT id, username, email, role FROM users ORDER BY id ASC");
$user_stmt->execute();
$users = $user_stmt->fetchAll(PDO::FETCH_ASSOC);


$user_id = null;
$username = '';
$email = '';
$role = 'user';
$password_placeholder = '';

// Handling edit button submission
if (isset($_GET['edit_user_id'])) {
    $edit_user_id = intval($_GET['edit_user_id']);
    $edit_stmt = $pdo->prepare("SELECT id, username, email, role FROM users WHERE id = :id");
    $edit_stmt->execute([':id' => $edit_user_id]);
    $user_to_edit = $edit_stmt->fetch();

    if ($user_to_edit) {
        $user_id = $user_to_edit['id'];
        $username = $user_to_edit['username'];
        $email = $user_to_edit['email'];
        $role = $user_to_edit['role'];
        $password_placeholder = 'Leave blank to keep existing password.';
    }
}

// Handling the user deletion processes
if (isset($_GET['delete_user_id'])) {
    $delete_user_id = intval($_GET['delete_user_id']);
    try {
        $delete_stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $delete_stmt->execute([':id' => $delete_user_id]);
        header("Location: admin_user_management.php");
        exit();
    } catch (PDOException $e) {
        echo "Error deleting user: " . $e->getMessage();
    }
}

// Handling the  adding or updating of the users
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_user'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $password = $_POST['password'];
    $user_id = isset($_POST['user_id']) && !empty($_POST['user_id']) ? intval($_POST['user_id']) : null;

    try {
        if ($user_id) {
            // Updating an existing user
            $update_stmt = $pdo->prepare("UPDATE users SET username = :username, email = :email, role = :role WHERE id = :id");
            $update_stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':role' => $role,
                ':id' => $user_id,
            ]);
            if (!empty($password)) {
                $password_stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
                $password_stmt->execute([
                    ':password' => $password,
                    ':id' => $user_id,
                ]);
            }
        } else {
            // Adding a new user here
            $insert_stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)");
            $insert_stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password' => $password,
                ':role' => $role,
            ]);
        }

        header("Location: admin_user_management.php");
        exit();
    } catch (PDOException $e) {
        echo "Error saving user: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - User Management</title>
    <link rel="stylesheet" href="styles/admin_dashboard.css">
    <style>
        body {
            font-family: Roboto, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #ecd9ba;
        }

        h1 {
            text-align: center;
            margin-top: 20px;
            color: #dd5701;
        }

        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background-color: white;
        }

        table th, table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }

        table th {
            background-color: #dd5701;
            color: white;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .action-buttons button {
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .edit-button {
            background-color: #4CAF50;
            color: white;
        }

        .edit-button:hover {
            background-color: #45a049;
        }

        .delete-button {
            background-color: #f44336;
            color: white;
        }

        .delete-button:hover {
            background-color: #e53935;
        }

        .form-container {
            width: 60%;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .form-container h2 {
            text-align: center;
            color: #dd5701;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        form label {
            font-weight: bold;
        }

        form input, form select, form button {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        form button {
            background-color: #dd5701;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        form button:hover {
            background-color: #34495e;
        }
    </style>
</head>
<body>
    <div class="menu-bar">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>

    <h1>User Management</h1>

    <h2>Registered users</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                    <td>
                        <div class="action-buttons">
                            <a href="admin_user_management.php?edit_user_id=<?php echo $user['id']; ?>">
                                <button class="edit-button">Edit</button>
                            </a>
                            <a href="admin_user_management.php?delete_user_id=<?php echo $user['id']; ?>" onclick="return confirm('Are you sure you want to delete this user?')">
                                <button class="delete-button">Delete</button>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="form-container">
        <h2><?php echo $user_id ? 'Edit User' : 'Add New User'; ?></h2>
        <form method="POST" action="admin_user_management.php">
            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">

            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>

            <label for="role">Role</label>
            <select id="role" name="role" required>
                <option value="user" <?php if ($role === 'user') echo 'selected'; ?>>User</option>
                <option value="admin" <?php if ($role === 'admin') echo 'selected'; ?>>Admin</option>
            </select>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="<?php echo $password_placeholder; ?>">

            <button type="submit" name="save_user"><?php echo $user_id ? 'Update User' : 'Add User'; ?></button>
        </form>
    </div>
</body>
</html>
