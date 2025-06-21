<?php
require 'connect.php';
session_start();

// Function to resize an image
function resizeImage($source, $destination, $width, $height) {
    list($orig_width, $orig_height) = getimagesize($source);

    // Created a new image area with target dimensions
    $image_resized = imagecreatetruecolor($width, $height);
    $image_source = imagecreatefromjpeg($source); // Assume JPEG for simplicity; adjust if needed

    // Resized the source image o=in order to exactly fit the new area
    imagecopyresampled($image_resized, $image_source, 0, 0, 0, 0, $width, $height, $orig_width, $orig_height);

    imagejpeg($image_resized, $destination, 90); // 90 is the quality parameter

    // Freed up the memory
    imagedestroy($image_resized);
    imagedestroy($image_source);
}

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Access denied.");
}

$movie_id = intval($_GET['id']);
$stmt = $pdo->prepare("SELECT * FROM movies WHERE id = :id");
$stmt->execute([':id' => $movie_id]);
$movie = $stmt->fetch();

if (!$movie) {
    die("Movie not found.");
}

$category_stmt = $pdo->prepare("SELECT id, name FROM categories");
$category_stmt->execute();
$categories = $category_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetching  a current image, if any
$image_stmt = $pdo->prepare("SELECT filename FROM images WHERE movie_id = :movie_id");
$image_stmt->execute([':movie_id' => $movie_id]);
$current_image = $image_stmt->fetchColumn();

// Handle form submission for updating the movie
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = htmlspecialchars($_POST['title']);
    $description = $_POST['description']; // Here, removed htmlspecialchars to preserve HTML content from wysiwg editor
    $release_year = intval($_POST['release_year']);
    $category_id = intval($_POST['category_id']);
    $duration = intval($_POST['duration']);
    $director = htmlspecialchars($_POST['director']);
    $rating = floatval($_POST['rating']);
    $image_to_delete = isset($_POST['delete_image']); // Checked if the delete image checkbox is checked

    try { // prepared rhe sql query 
        $update_stmt = $pdo->prepare("UPDATE movies SET title = :title, description = :description, release_year = :release_year, category_id = :category_id, duration = :duration, director = :director, rating = :rating WHERE id = :id");
        $update_stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':release_year' => $release_year,
            ':category_id' => $category_id,
            ':duration' => $duration,
            ':director' => $director,
            ':rating' => $rating,
            ':id' => $movie_id
        ]);

        // Handle image upload or removal
        if ($image_to_delete && $current_image) {
            unlink("uploads/" . $current_image);
            $delete_image_stmt = $pdo->prepare("DELETE FROM images WHERE movie_id = :movie_id");
            $delete_image_stmt->execute([':movie_id' => $movie_id]);
        }

        if (!empty($_FILES['image']['name'])) {
            $uploaded_file = $_FILES['image'];
            $temp_file = $uploaded_file['tmp_name'];
            $image_filename = uniqid() . '-' . basename($uploaded_file['name']);
            $target_file = "uploads/" . $image_filename;

            // Resize the image to 800x600 pixels
            resizeImage($temp_file, $target_file, 800, 600);

            // Update or insert the new image into the database
            if ($current_image) {
                unlink("uploads/" . $current_image); // Remove the old image
                $update_image_stmt = $pdo->prepare("UPDATE images SET filename = :filename WHERE movie_id = :movie_id");
            } else {
                $update_image_stmt = $pdo->prepare("INSERT INTO images (movie_id, filename) VALUES (:movie_id, :filename)");
            }
            $update_image_stmt->execute([':movie_id' => $movie_id, ':filename' => $target_file]);
        }

        echo "Movie updated successfully.";
        header("Location: admin_dashboard.php");
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
    <title>Edit Movie - Movie Vault</title>
    <link rel="stylesheet" href="styles/edit_article.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script> --> 
     <!-- Hardcoded API key, do not reveal. -->
    <script src="https://cdn.tiny.cloud/1/zegq2nwkmz8bcmhymlxtp6vuie13giy59fya59v2hy7ldhcf/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: '#description',
            height: 300,
            plugins: 'advlist autolink lists link image charmap print preview anchor',
            toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help'
        });
    </script>
</head>
<body>
    <div class="container">
        <a href="admin_dashboard.php" class="back-link">Back to Dashboard</a>
        
        <h1>Edit Movie</h1>
        
        <form action="edit_article.php?id=<?php echo $movie_id; ?>" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Movie Title:</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($movie['title']); ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" placeholder="Enter movie description..." required><?php echo htmlspecialchars($movie['description']); ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="release_year">Release Year:</label>
                    <input type="number" id="release_year" name="release_year" min="1900" max="2100" value="<?php echo htmlspecialchars($movie['release_year']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="duration">Duration (minutes):</label>
                    <input type="number" id="duration" name="duration" min="1" value="<?php echo htmlspecialchars($movie['duration']); ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="category_id">Category (Genre):</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">Select a category...</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php if ($movie['category_id'] == $category['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="rating">Rating (0-10):</label>
                    <input type="number" id="rating" name="rating" min="0" max="10" step="0.1" value="<?php echo htmlspecialchars($movie['rating'] ?? ''); ?>" placeholder="e.g., 8.5">
                </div>
            </div>

            <div class="form-group">
                <label for="director">Director:</label>
                <input type="text" id="director" name="director" value="<?php echo htmlspecialchars($movie['director'] ?? ''); ?>" placeholder="Enter director's name">
            </div>

            <?php if ($current_image): ?>
                <div class="current-image">
                    <p>Current Movie Poster:</p>
                    <img src="<?php echo htmlspecialchars($current_image); ?>" alt="Current Movie Poster">
                    <div class="checkbox-group">
                        <input type="checkbox" id="delete_image" name="delete_image">
                        <label for="delete_image">Remove current image</label>
                    </div>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="image">Upload New Movie Poster:</label>
                <input type="file" id="image" name="image" accept="image/*">
                <small style="color: #666; font-size: 0.9em;">Supported formats: JPG, PNG, GIF. Image will be resized to 800x600 pixels.</small>
            </div>

            <button type="submit" class="submit-btn">Update Movie</button>
        </form>
    </div>

    <script>
        // Add loading state to submit button
        document.querySelector('form').addEventListener('submit', function() {
            const submitBtn = document.querySelector('.submit-btn');
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        });

        // File input preview
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Create preview if it doesn't exist
                    let preview = document.querySelector('.image-preview');
                    if (!preview) {
                        preview = document.createElement('div');
                        preview.className = 'image-preview';
                        preview.innerHTML = '<p>New Image Preview:</p><img style="max-width: 200px; border-radius: 8px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);">';
                        e.target.parentNode.appendChild(preview);
                    }
                    preview.querySelector('img').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
