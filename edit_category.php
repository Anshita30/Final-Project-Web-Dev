<?php
require 'connect.php';
session_start();

// Check if the user is logged in and has either the admin or user role since the rubrik requires users to edit categories (genre in this project case)
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'user'])) {
    die("Access denied.");
}

$stmt = $pdo->prepare("SELECT * FROM categories");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $category_id = intval($_POST['category_id']);
    $new_name = htmlspecialchars($_POST['new_name']);

    try {
        $update_stmt = $pdo->prepare("UPDATE categories SET name = :name WHERE id = :id");
        $update_stmt->execute([
            ':name' => $new_name,
            ':id' => $category_id
        ]);

        echo "Category updated successfully.";
        header("Location: edit_category.php");
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $category_id = intval($_POST['category_id']);

    try {
        $delete_stmt = $pdo->prepare("DELETE FROM categories WHERE id = :id");
        $delete_stmt->execute([':id' => $category_id]);

        echo "Category deleted successfully.";
        header("Location: edit_category.php");
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
    $new_category_name = htmlspecialchars($_POST['new_category_name']);

    try {
        $create_stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (:name)");
        $create_stmt->execute([':name' => $new_category_name]);

        echo "Category created successfully.";
        header("Location: edit_category.php");
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit categories/Genres - Movie Max</title>
    <style>
        body {
            font-family: Roboto, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f8ff;
            color: #333;
        }

        h1 {
            text-align: center;
            margin-top: 20px;
            font-size: 2.5rem;
            color: #dd5701;
        }

        .container {
            width: 80%;
            max-width: 900px;
            margin: 30px auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .back-button, .create-button {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 15px;
            background-color: #5bb656;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .back-button:hover, .create-button:hover {
            background-color: #29b948;
        }

        .create-category-form {
            margin-bottom: 30px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #ecd9ba;
        }

        .category-form {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #ecd9ba;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .category-form label {
            font-weight: bold;
            margin-right: 10px;
            flex: 1;
        }

        .category-form input[type="text"] {
            flex: 2;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
        }

        .category-form .buttons {
            display: flex;
            gap: 10px;
        }

        .category-form button {
            padding: 10px 15px;
            background-color: #5bb656;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .category-form button:hover {
            background-color: #29b948;
            transform: scale(1.05);
        }

        .category-form .delete-button {
            background-color: #e74c3c;
        }

        .category-form .delete-button:hover {
            background-color: #c0392b;
        }

        footer {
            text-align: center;
            padding: 20px;
            background-color: #dd5701;
            color: white;
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="javascript:history.back()" class="back-button">&laquo; Back</a>
        <h1>Edit categories/Genres</h1>

        <form action="edit_category.php" method="POST" class="create-category-form">
            <label for="new_category_name">New Category Name:</label>
            <input type="text" id="new_category_name" name="new_category_name" required>
            <button type="submit" name="create" class="create-button">Create Category</button>
        </form>
        
        <!-- Edit/Delete categories -->
        <?php foreach ($categories as $category): ?>
            <form action="edit_category.php" method="POST" class="category-form">
                <label for="new_name_<?php echo $category['id']; ?>">Category Name:</label>
                <input type="text" id="new_name_<?php echo $category['id']; ?>" name="new_name" 
                       value="<?php echo htmlspecialchars($category['name']); ?>" required>
                <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                <div class="buttons">
                    <button type="submit" name="update">Update</button>
                    <button type="submit" name="delete" class="delete-button">Delete</button>
                </div>
            </form>
        <?php endforeach; ?>
    </div>
    <footer>
        <p>&copy; 2025 Movie Max. All Rights Reserved.</p>
    </footer>
</body>
</html>
