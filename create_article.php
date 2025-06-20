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
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #ecd9ba;
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.2em;
        }

        .back-button {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 20px;
            background-color: #5bb656;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .back-button:hover {
            background-color: #29b948;
        }

        .back-button::before {
            content: "← ";
            margin-right: 5px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        label {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="number"],
        input[type="file"],
        select,
        textarea {
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #5bb656;
        }

        select {
            background-color: white;
            cursor: pointer;
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        button[type="submit"] {
            background-color: #2ecc71;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
            align-self: center;
            margin-top: 20px;
        }

        button[type="submit"]:hover {
            background-color: #27ae60;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-row {
            display: flex;
            gap: 20px;
        }

        .form-row .form-group {
            flex: 1;
        }

        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
            }
            
            .container {
                margin: 10px;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="javascript:history.back()" class="back-button">Back</a>
        <h1>Create a New Movie</h1>
        
        <!-- HTML Form to Create New Movie -->
        <form action="create_article.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" required>
            </div>

            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description"></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="release_year">Release Year:</label>
                    <input type="number" id="release_year" name="release_year" min="1900" max="2100" required>
                </div>

                <div class="form-group">
                    <label for="duration">Duration (minutes):</label>
                    <input type="number" id="duration" name="duration" min="1" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="category_id">Category (Genre):</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">Select a Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="rating">Rating (0-10):</label>
                    <input type="number" id="rating" name="rating" min="0" max="10" step="0.1">
                </div>
            </div>

            <div class="form-group">
                <label for="director">Director:</label>
                <input type="text" id="director" name="director">
            </div>

            <div class="form-group">
                <label for="image">Upload Movie Poster (optional):</label>
                <input type="file" id="image" name="image" accept="image/*">
            </div><br><br>
        <!-- take any type of image as inpout (png, jpg etc) -->

            <button type="submit">Create Movie</button>
        </form>
    </div>
</body>
</html>
