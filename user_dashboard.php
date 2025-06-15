<?php
require 'connect.php'; // Include database connection
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php"); // Redirect to login page if not logged in
    exit();
}

// Pagination variables
$default_results_per_page = 5;
$results_per_page = isset($_GET['results_per_page']) ? intval($_GET['results_per_page']) : $default_results_per_page;
$current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($current_page - 1) * $results_per_page;

// Fetch all categories for the category filter dropdown
$category_stmt = $pdo->prepare("SELECT id, name FROM categories");
$category_stmt->execute();
$categories = $category_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch 1. search, 2. category, and 3. sort variables
$search_query = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? 'all';
$sort_by = $_GET['sort'] ?? 'created_at';
$allowed_sorts = ['title', 'created_at', 'updated_at', 'release_year'];
if (!in_array($sort_by, $allowed_sorts)) {
    $sort_by = 'created_at';
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

// Apply search filter
if (!empty($search_query)) {
    $sql .= " AND (movies.title LIKE :search_query OR movies.description LIKE :search_query)";
    $params[':search_query'] = '%' . $search_query . '%';
}

// Apply category filter
if ($category_filter !== 'all') {
    $sql .= " AND categories.id = :category_id";
    $params[':category_id'] = $category_filter;
}

// Count total rows for pagination
$count_sql = "SELECT COUNT(*) as total FROM ($sql) as count_query";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_rows = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_rows / $results_per_page);

// Apply sorting and pagination
$sql .= " ORDER BY $sort_by ASC LIMIT $results_per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Movie Max</title>
    <link rel="stylesheet" href="styles/user_dashboard.css">
    <style>
    .article-item {
        position: relative;
        border: 1px solid #ddd;
        border-radius: 10px;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        padding: 20px;
        background-size: cover;
        background-position: center;
        overflow: hidden;
        color: #fff;
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
        background: rgba(0, 0, 0, 0.4); /* set the background color */
        z-index: 1;
    }

    .article-item h2,
    .article-item p,
    .article-item a {
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

    .article-item:hover {
        transform: translateY(-5px);
        transition: transform 0.3s;
    }

    .edit-category-btn {
        margin: 20px auto;
        display: block;
        background-color: #5bb656;
        color: white;
        padding: 10px 20px;
        text-align: center;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .edit-category-btn:hover {
        background-color: #29b948;
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
        <a href="user_dashboard.php">Home</a>
        <a href="logout.php">Logout</a>
    </div>

    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>

    <!-- Edit categories Button -->
    <button class="edit-category-btn" onclick="window.location.href='edit_category.php'">Edit categories/Genres</button>

    <!-- Search, Filter, and Sort Form -->
    <form class="filter-form" method="GET" action="user_dashboard.php">
        <input type="text" name="search" placeholder="Search by keyword" value="<?php echo htmlspecialchars($search_query); ?>">
        <label for="category">Filter by Category:</label>
        <select name="category" id="category">
            <option value="all" <?php if ($category_filter === 'all') echo 'selected'; ?>>All</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo $category['id']; ?>" <?php if ($category_filter == $category['id']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($category['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="sort">Sort By:</label>
        <select name="sort" id="sort">
            <option value="title" <?php if ($sort_by === 'title') echo 'selected'; ?>>Title</option>
            <option value="created_at" <?php if ($sort_by === 'created_at') echo 'selected'; ?>>Creation Date</option>
            <option value="updated_at" <?php if ($sort_by === 'updated_at') echo 'selected'; ?>>Update Date</option>
            <option value="release_year" <?php if ($sort_by === 'release_year') echo 'selected'; ?>>Movie Release Date</option>
        </select>

        <label for="results_per_page">Results per Page:</label>
        <select name="results_per_page" id="results_per_page">
            <option value="5" <?php if ($results_per_page === 5) echo 'selected'; ?>>5</option>
            <option value="10" <?php if ($results_per_page === 10) echo 'selected'; ?>>10</option>
            <option value="15" <?php if ($results_per_page === 15) echo 'selected'; ?>>15</option>
        </select>

        <button type="submit">Apply</button>
    </form>

    <!-- Articles Grid -->
    <div class="articles-grid">
        <?php foreach ($articles as $article): ?>
            <div class="article-item <?php echo empty($article['image']) ? 'no-image' : ''; ?>" 
                style="<?php echo !empty($article['image']) ? 'background-image: url(\'' . htmlspecialchars($article['image']) . '\');' : ''; ?>"> <!-- concat file name and escape it -->
                <h2><?php echo htmlspecialchars($article['title']); ?></h2>
                <p><?php echo nl2br(substr($article['description'], 0, 100)); ?>...</p>
                <p><strong>Genre:</strong> <?php echo htmlspecialchars($article['genre'] ?? 'Unknown'); ?></p>
                <p><strong>Release Year:</strong> <?php echo htmlspecialchars($article['release_year']); ?></p>
                <a href="view_article.php?id=<?php echo $article['id']; ?>">Read More</a>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <div class="pagination">
        <?php if ($current_page > 1): ?>
            <a href="?page=<?php echo $current_page - 1; ?>&results_per_page=<?php echo $results_per_page; ?>&search=<?php echo urlencode($search_query); ?>&category=<?php echo $category_filter; ?>&sort=<?php echo $sort_by; ?>">&laquo; Prev</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a class="<?php echo ($i === $current_page) ? 'active' : ''; ?>" href="?page=<?php echo $i; ?>&results_per_page=<?php echo $results_per_page; ?>&search=<?php echo urlencode($search_query); ?>&category=<?php echo $category_filter; ?>&sort=<?php echo $sort_by; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>

        <?php if ($current_page < $total_pages): ?>
            <a href="?page=<?php echo $current_page + 1; ?>&results_per_page=<?php echo $results_per_page; ?>&search=<?php echo urlencode($search_query); ?>&category=<?php echo $category_filter; ?>&sort=<?php echo $sort_by; ?>">Next &raquo;</a>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; 2025 Movie Max. All Rights Reserved.</p>
    </footer>
</body>
</html>
