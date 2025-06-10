<?php
require 'connect.php'; // Include database connection
session_start();

// Check if the user is logged in and is an admin from the session cookies
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Access denied.");
}

// Pagination variables
$default_results_per_page = 5;
$results_per_page = isset($_GET['results_per_page']) ? intval($_GET['results_per_page']) : $default_results_per_page;
$current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($current_page - 1) * $results_per_page;

// Fetch 1. search, 2. sort variables
$search_query = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; // add html speciall characters in search query
$sort_by = isset($_GET['sort']) ? htmlspecialchars($_GET['sort']) : 'title'; // html special char not really needed since we are only allowing from a dropdown
$allowed_sorts = ['title', 'created_at', 'updated_at', 'genre'];
if (!in_array($sort_by, $allowed_sorts)) {
    $sort_by = 'title';
}

// query with LEFT JOIN to fetch associated image filename
$sql = "
    SELECT 
        movies.*, 
        categories.name AS genre, 
        images.filename AS image -- file name from upload follder
    FROM movies
    LEFT JOIN categories ON movies.category_id = categories.id
    LEFT JOIN images ON movies.id = images.movie_id
    WHERE 1=1
";
$params = [];

// concatenated search query if present
if (!empty($search_query)) {
    $sql .= " AND (movies.title LIKE :search_query OR movies.description LIKE :search_query)";
    $params[':search_query'] = '%' . $search_query . '%';
}

// count total rows for pagination
$count_sql = "SELECT COUNT(*) as total FROM ($sql) as count_query";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_rows = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_rows / $results_per_page);

// Apply pagination and sorting
$sql .= " ORDER BY $sort_by ASC LIMIT $results_per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Movie Vault</title>
    <link rel="stylesheet" href="styles/admin_dashboard.css">
    <style>
    .article-item {
        position: relative;
        border: 1px solid #ccc;
        border-radius: 10px;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        padding: 15px;
        background-size: cover;
        background-position: center;
        color: #fff; /* Default text color */
        overflow: hidden;
    }

    .article-item.no-image {
        background-color: #333; 
        color: #fff;
    }

    .article-item::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5); /* set the backgroudn using rgba for alpha trans*/
        z-index: 1;
    }

    .article-item h2,
    .article-item p,
    .article-item a,
    .buttons {
        position: relative;
        z-index: 2;
    }

    .article-item h2 {
        font-size: 1.6em;
        margin-bottom: 10px;
        font-weight: bold;
        color: #fff600; 
    }

    .article-item p {
        margin: 5px 0;
        font-size: 1em;
        color: #e4f1fe; 
    }

    .article-item a {
        color: #2950bf; 
        font-weight: bold;
        text-decoration: underline;
    }

    .buttons {
        display: flex;
        gap: 10px;
        margin-top: 10px;
    }

    .buttons button {
        padding: 10px 15px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-weight: bold;
        font-size: 1em;
    }

    .buttons button:hover {
        opacity: 0.9;
    }

    .pagination {
        text-align: center;
        margin-top: 20px;
    }

    .pagination a {
        margin: 0 5px;
        padding: 8px 12px;
        text-decoration: none;
        color: #5bb656;
        border: 1px solid #5bb656;
        border-radius: 5px;
        transition: background 0.3s, color 0.3s;
    }

    .pagination a.active {
        background: #5bb656;
        color: white;
    }

    .pagination a:hover {
        background: #29b948;
        color: white;
    }
</style>

</head>

<body>
    <div class="menu-bar">
        <a href="admin_dashboard.php">Home</a>
        <a href="admin_user_management.php">User Management</a>
        <a href="logout.php">Logout</a>
    </div>

    <h1>Admin Dashboard</h1>

    <button onclick="window.location.href='create_article.php'" style="margin: 10px auto; display: block;">Create New Movie</button>
    <button onclick="window.location.href='edit_category.php'" style="margin: 10px auto; display: block;">Edit categories/Genres</button>

    <!-- Search, Sort, and Results Per Page Form -->
    <form method="GET" action="">
        <input type="text" name="search" placeholder="Search by keyword" value="<?php echo htmlspecialchars($search_query); ?>">
        <label for="sort">Sort By:</label>
        <select name="sort" id="sort">
            <option value="title" <?php if ($sort_by === 'title') echo 'selected'; ?>>Title</option>
            <option value="created_at" <?php if ($sort_by === 'created_at') echo 'selected'; ?>>Creation Date</option>
            <option value="updated_at" <?php if ($sort_by === 'updated_at') echo 'selected'; ?>>Update Date</option>
            <option value="genre" <?php if ($sort_by === 'genre') echo 'selected'; ?>>Genre</option>
        </select>
        <label for="results_per_page">Results Per Page:</label>
        <select name="results_per_page" id="results_per_page">
            <option value="5" <?php if ($results_per_page == 5) echo 'selected'; ?>>5</option>
            <option value="10" <?php if ($results_per_page == 10) echo 'selected'; ?>>10</option>
            <option value="20" <?php if ($results_per_page == 20) echo 'selected'; ?>>20</option>
        </select>
        <button type="submit">Apply</button>
    </form>

    <!-- movies Grid -->
    <div class="articles-grid">
        <?php foreach ($movies as $movie): ?>
            <div class="article-item <?php echo empty($movie['image']) ? 'no-image' : ''; ?>"
                style="<?php echo !empty($movie['image']) ? 'background-image: url(\'' . htmlspecialchars($movie['image']) . '\');' : ''; ?>"> <!-- concat file name and escape it -->
                <h2><?php echo htmlspecialchars($movie['title']); ?></h2>
                <p><?php echo nl2br(substr($movie['description'], 0, 100)); ?>...</p>
                <p><strong>Release Year:</strong> <?php echo htmlspecialchars($movie['release_year']); ?></p>
                <p><strong>Duration:</strong> <?php echo htmlspecialchars($movie['duration']); ?> minutes</p>
                <p><strong>Director:</strong> <?php echo htmlspecialchars($movie['director'] ?? 'Unknown'); ?></p>
                <p><strong>Rating:</strong> <?php echo htmlspecialchars($movie['rating'] ?? 'N/A'); ?>/10</p>
                <p><strong>Genre:</strong> <?php echo htmlspecialchars($movie['genre'] ?? 'Unknown'); ?></p>
                <a href="view_article.php?id=<?php echo $movie['id']; ?>">View Details</a>
                <div class="buttons">
                    <button onclick="window.location.href='edit_article.php?id=<?php echo $movie['id']; ?>'">Edit</button>
                    <button onclick="if(confirm('Are you sure you want to delete this movie?')) { window.location.href='delete_article.php?id=<?php echo $movie['id']; ?>' }">Delete</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <div class="pagination">
        <?php if ($current_page > 1): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $current_page - 1])); ?>">&laquo; Prev</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" class="<?php echo ($i === $current_page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>

        <?php if ($current_page < $total_pages): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $current_page + 1])); ?>">Next &raquo;</a>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; 2024 Movie Vault. All Rights Reserved.</p>
    </footer>
</body>

</html>