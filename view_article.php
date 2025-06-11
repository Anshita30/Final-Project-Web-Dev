<?php
require 'connect.php'; 
session_start();


// Check if the user is logged in
if (!isset($_SESSION['user_role'])) {
    die("Access denied.");
}

// Fetch the article details based on the article ID
$article_id = intval($_GET['id']); // use int value of id
$stmt = $pdo->prepare("SELECT * FROM Anime WHERE id = :id");
$stmt->execute([':id' => $article_id]);
$article = $stmt->fetch();

if (!$article) {
    die("Article not found.");
}

//User prepared sql statement and Fetch comments for the article in reverse order
$comments_stmt = $pdo->prepare("
    SELECT comments.id, comments.content, comments.status, users.username, comments.created_at 
    FROM comments
    JOIN users ON comments.user_id = users.id
    WHERE comments.anime_id = :anime_id
    ORDER BY comments.created_at DESC
");
$comments_stmt->execute([':anime_id' => $article_id]);
$comments = $comments_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle comment submission non-admin users
$comment_error = ''; // To store any errors during submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_comment') {
    if ($_SESSION['user_role'] === 'user') {
        $comment_content = htmlspecialchars($_POST['content']);
        $captcha_input = $_POST['captcha'];

        // Validate CAPTCHA
        if ($captcha_input != $_SESSION['captcha']) {
            $comment_error = "Invalid CAPTCHA. Please try again.";
            $_SESSION['captcha'] = rand(1000, 9999); // Regenerate CAPTCHA
        } else {
            try {
                // Insert new comment into the comments table
                $insert_stmt = $pdo->prepare("
                    INSERT INTO comments (content, status, user_id, anime_id) 
                    VALUES (:content, 'visible', :user_id, :anime_id)
                ");
                $insert_stmt->execute([
                    ':content' => $comment_content,
                    ':user_id' => $_SESSION['user_id'],
                    ':anime_id' => $article_id
                ]);
                echo "Comment added successfully.";
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
            }
            $_SESSION['captcha'] = rand(1000, 9999); // Regenerate CAPTCHA after successful submission
        }
    }
}

// Handle admin comment moderation actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['user_role'] === 'admin') {
    $comment_id = intval($_POST['comment_id']);
    $action = $_POST['action'];
    try {
        if ($action === 'delete') {
            $delete_stmt = $pdo->prepare("DELETE FROM comments WHERE id = :id");
            $delete_stmt->execute([':id' => $comment_id]);
            echo "Comment deleted successfully.";
        } elseif ($action === 'hide') {
            $hide_stmt = $pdo->prepare("UPDATE comments SET status = 'hidden' WHERE id = :id");
            $hide_stmt->execute([':id' => $comment_id]);
            echo "Comment hidden successfully.";
        } elseif ($action === 'unhide') {
            $unhide_stmt = $pdo->prepare("UPDATE comments SET status = 'visible' WHERE id = :id");
            $unhide_stmt->execute([':id' => $comment_id]);
            echo "Comment unhidden successfully.";
        } elseif ($action === 'disemvowel') {
            $stmt = $pdo->prepare("SELECT content FROM comments WHERE id = :id");
            $stmt->execute([':id' => $comment_id]);
            $comment = $stmt->fetch();
            $disemvoweled_content = preg_replace('/[aeiouAEIOU]/', '', $comment['content']);
            $disemvowel_stmt = $pdo->prepare("UPDATE comments SET content = :content WHERE id = :id");
            $disemvowel_stmt->execute([':content' => $disemvoweled_content, ':id' => $comment_id]);
            echo "Comment disemvoweled successfully.";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
    header("Location: view_article.php?id=$article_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['title']); ?> - Movie Max</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #ecd9ba;
        }

        .article {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .comments {
            margin-top: 30px;
        }

        .comment {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .moderation-buttons button {
            margin-right: 5px;
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }

        .delete {
            background: #e74c3c;
            color: white;
        }

        .delete:hover {
            background: #c0392b;
        }

        .hide, .unhide, .disemvowel {
            background: #5bb656;
            color: white;
        }

        .hide:hover, .unhide:hover, .disemvowel:hover {
            background: #29b948;
        }

        .captcha {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .captcha img {
            border: 1px solid #ddd;
            padding: 5px;
            border-radius: 5px;
        }

        .submit-comment {
            margin-top: 20px;
            background: #2ecc71;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
        }

        .submit-comment:hover {
            background: #27ae60;
        }

        .back-button {
            margin-bottom: 20px;
            display: inline-block;
            padding: 10px 15px;
            background-color: #5bb656;
            color: white;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
        }

        .back-button:hover {
            background-color: #29b948;
        }

        .error-message {
            color: red;
        }
    </style>
</head>
<body>
    <!-- go back in history -->
    <a href="javascript:history.back()" class="back-button">← Back</a>

    <div class="article">
        <h1><?php echo htmlspecialchars($article['title']); ?></h1>
        <p><?php echo $article['description']; ?></p>
        <p><strong>Created At:</strong> <?php echo htmlspecialchars($article['created_at']); ?></p>
        <p><strong>Updated At:</strong> <?php echo htmlspecialchars($article['updated_at']); ?></p>
    </div>

    <div class="comments">
        <h2>comments</h2>
        <?php foreach ($comments as $comment): ?>
            <div class="comment">
                <p><strong><?php echo htmlspecialchars($comment['username']); ?>:</strong></p>
                <p><?php echo htmlspecialchars($comment['content']); ?></p>
                <p><strong>Status:</strong> <?php echo htmlspecialchars($comment['status']); ?></p>
                <p><strong>Created At:</strong> <?php echo htmlspecialchars($comment['created_at']); ?></p>
                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <div class="moderation-buttons">
                        <form method="POST" action="view_article.php?id=<?php echo $article_id; ?>" style="display: inline;">
                            <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" class="delete">Delete</button>
                        </form>
                        <form method="POST" action="view_article.php?id=<?php echo $article_id; ?>" style="display: inline;">
                            <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                            <input type="hidden" name="action" value="hide">
                            <button type="submit" class="hide">Hide</button>
                        </form>
                        <form method="POST" action="view_article.php?id=<?php echo $article_id; ?>" style="display: inline;">
                            <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                            <input type="hidden" name="action" value="unhide">
                            <button type="submit" class="unhide">Unhide</button>
                        </form>
                        <form method="POST" action="view_article.php?id=<?php echo $article_id; ?>" style="display: inline;">
                            <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                            <input type="hidden" name="action" value="disemvowel">
                            <button type="submit" class="disemvowel">Disemvowel</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <!-- Comment Submission Form for Non-Admin users -->
        <?php if ($_SESSION['user_role'] === 'user'): ?>
            <h3>Add a Comment</h3>
            <?php if (!empty($comment_error)) echo "<p class='error-message'>$comment_error</p>"; ?>
            <form action="view_article.php?id=<?php echo $article_id; ?>" method="POST">
                <textarea name="content" rows="4" cols="50" placeholder="Write your comment here..." required><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea><br>
                <div class="captcha">
                    <p>CAPTCHA:</p>
                    <img src="view_article.php?captcha=1" alt="CAPTCHA">
                    <input type="text" id="captcha" name="captcha" placeholder="Enter CAPTCHA" required>
                </div>
                <input type="hidden" name="action" value="submit_comment">
                <button type="submit" class="submit-comment">Submit Comment</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
