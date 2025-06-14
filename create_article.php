<?php
require 'connect.php'; // This includes the database connection page
session_start();

// Here, we check if the user is an admin or nnot
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Access denied.");
}

// Here, we fetch the categories from the database
$stmt = $pdo->prepare("SELECT id, name FROM categories");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Here we use Driver functions to handle the form submission and insert movies into the database.
function handleFormSubmission($pdo) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Here we collect and sanitize the form input
        $title = htmlspecialchars($_POST['title']); // add html chars to avoid xss and sql injection
        $description = $_POST['description']; // didnt add html chars here since we are using a wysiqg editor and it needs to add raw html to the description part
        $release_year = intval($_POST['release_year']); // treat as int
        $category_id = intval($_POST['category_id']); // treat as int
        $duration = intval($_POST['duration']); // treat as int
        $director = htmlspecialchars($_POST['director']); // add html chars to avoid xss
        $rating = floatval($_POST['rating']); // treat as float

        try {
            // WE prepare the SQL statement to avoid injection
            $stmt = $pdo->prepare("INSERT INTO movies (title, description, release_year, category_id, duration, director, rating) VALUES (:title, :description, :release_year, :category_id, :duration, :director, :rating)");
            // Here we execute the statement with placeholders
            $stmt->execute([
                ':title' => $title,
                ':description' => $description,
                ':release_year' => $release_year,
                ':category_id' => $category_id,
                ':duration' => $duration,
                ':director' => $director,
                ':rating' => $rating
            ]);

            // Get the ID of the newly created movie
            $page_id = $pdo->lastInsertId();

            if (!empty($_FILES['image']['name'])) {
                $image = $_FILES['image'];

                // alllowed image types
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                if (in_array($image['type'], $allowed_types) && $image['error'] === 0) {

                    $image_path = 'uploads/' . uniqid() . '-' . basename($image['name']);
                    if (move_uploaded_file($image['tmp_name'], $image_path)) {
                        // save image fille name in db
                        $stmt = $pdo->prepare("INSERT INTO images (movie_id, filename) VALUES (:movie_id, :filename)");
                        $stmt->execute([
                            ':movie_id' => $page_id,
                            ':filename' => $image_path
                        ]);
                    } else {
                        echo "Failed to move the uploaded file.";
                    }
                } else {
                    echo "Invalid image file.";
                }
            }

            echo "Movie created successfully.";
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}

// Call the form handling function
handleFormSubmission($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Movie - Movie Vault</title>
    <!-- <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script> --> 
     <!-- Hardcoded API key, do not reveal. -->
    <script src="https://cdn.tiny.cloud/1/zegq2nwkmz8bcmhymlxtp6vuie13giy59fya59v2hy7ldhcf/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: '#description',
            setup: function (editor) {
                editor.on('change', function () {
                    editor.save();
                });
            }
        });
    </script>
</head>
<body>
    <h1>Create a New Movie</h1>
    
    <!-- HTML Form to Create New Movie -->
    <form action="create_article.php" method="POST" enctype="multipart/form-data">
        <label for="title">Title:</label>
        <input type="text" id="title" name="title" required><br><br>

        <label for="description">Description:</label>
        <textarea id="description" name="description" ></textarea><br><br>

        <label for="release_year">Release Year:</label>
        <input type="number" id="release_year" name="release_year" min="1900" max="2100" required><br><br>

        <label for="category_id">Category (Genre):</label>
        <select id="category_id" name="category_id" required>
            <option value="">Select a Category</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo $category['id']; ?>">
                    <?php echo htmlspecialchars($category['name']); ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="duration">Duration (minutes):</label>
        <input type="number" id="duration" name="duration" min="1" required><br><br>

        <label for="director">Director:</label>
        <input type="text" id="director" name="director"><br><br>

        <label for="rating">Rating (0-10):</label>
        <input type="number" id="rating" name="rating" min="0" max="10" step="0.1"><br><br>

        <label for="image">Upload Movie Poster (optional):</label>
        <input type="file" id="image" name="image" accept="image/*"><br><br>
        <!-- take any type of image as inpout (png, jpg etc) -->

        <button type="submit">Create Movie</button>
    </form>
</body>
</html>
