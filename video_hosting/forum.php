<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];


$is_admin = false;
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
if ($user['role'] === 'admin') {
    $is_admin = true;
}

if ($is_admin && isset($_POST['delete_post_id'])) {
    $delete_post_id = $_POST['delete_post_id'];


    $stmt = $pdo->prepare("DELETE FROM forum_posts WHERE id = ?");
    $stmt->execute([$delete_post_id]);

    header("Location: forum.php");
    exit;
}

if ($is_admin && isset($_POST['delete_comment_id'])) {
    $delete_comment_id = $_POST['delete_comment_id'];

    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
    $stmt->execute([$delete_comment_id]);

    header("Location: forum.php");
    exit;
}

$stmt = $pdo->query("SELECT forum_posts.*, users.username FROM forum_posts JOIN users ON forum_posts.user_id = users.id ORDER BY created_at DESC");
$posts = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['title'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];

    $stmt = $pdo->prepare("INSERT INTO forum_posts (title, content, user_id) VALUES (?, ?, ?)");
    $stmt->execute([$title, $content, $user_id]);

    header("Location: forum.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment_content']) && isset($_POST['post_id'])) {
    $comment_content = $_POST['comment_content'];
    $post_id = $_POST['post_id'];

    $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$post_id, $user_id, $comment_content]);

    header("Location: forum.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Форум</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e5e5e5;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 960px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border: 1px solid #ccc;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #000080;
            color: #ffffff;
            padding: 10px;
            text-align: center;
            font-size: 24px;
            border-bottom: 3px solid #cc0000;
        }
        .sidebar {
            float: left;
            width: 200px;
            background-color: #f9f9f9;
            padding: 15px;
            border-right: 1px solid #ccc;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }
        .sidebar h2 {
            font-size: 18px;
            color: #4682B4;
            margin-top: 0;
        }
        .sidebar a {
            display: block;
            color: #333;
            text-decoration: none;
            padding: 10px;
            margin-bottom: 5px;
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        .sidebar a:hover {
            background-color: #ffffff;
        }
        .content {
            margin-left: 220px;
            padding: 20px;
        }
        .post {
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 10px;
        }
        .post-meta, .comment-meta {
            font-size: 12px;
            color: #777;
        }
        .delete-button {
            background-color: #ff0000;
            color: #ffffff;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            font-size: 12px;
            float: right;
        }
        .comment {
            margin-left: 20px;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Форум</h1>
        </div>
        <div class="sidebar">
            <h2></h2>
            <a href="index.php">Список видео</a>
            <a href="upload_avatar.php">Загрузить аватарку</a>
        </div>
        <div class="new-post">
            <h2>Создать новый пост</h2>
            <form method="POST">
                <label for="title">Заголовок:</label>
                <input type="text" name="title" required><br>
                <label for="content">Контент:</label>
                <textarea name="content" required></textarea><br>
                <button type="submit">Создать</button>
            </form>
        </div>
        <div class="posts">
            <h2>Посты</h2>
            <?php foreach ($posts as $post): ?>
                <div class="post">
                    <?php
                    $post_avatar = !empty($post['avatar']) ? htmlspecialchars($post['avatar']) : 'images/default-avatar.jpg';
                    ?>
                    <img src="<?= $post_avatar ?>" alt="Avatar" style="width: 50px; height: 50px; border-radius: 50%;">
                    <h3><?= htmlspecialchars($post['title']) ?></h3>
                    <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
                    <div class="post-meta">Автор: <?= htmlspecialchars($post['username']) ?> | Дата: <?= htmlspecialchars($post['created_at']) ?></div>
                    <div class="likes-count">👍: <?= htmlspecialchars($post['likes_count']) ?></div>
                    <form method="POST" action="like.php">
                        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                        <button type="submit">👍</button>
                    </form>
                    <?php if ($is_admin): ?>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="delete_post_id" value="<?= $post['id'] ?>">
                            <button type="submit" class="delete-button">Удалить</button>
                        </form>
                    <?php endif; ?>
                    <div class="comments">
                        <h4>Комментарии</h4>
                        <?php

                        $stmt = $pdo->prepare("SELECT comments.*, users.username, users.avatar FROM comments JOIN users ON comments.user_id = users.id WHERE post_id = ? ORDER BY created_at ASC");
                        $stmt->execute([$post['id']]);
                        $comments = $stmt->fetchAll();

                        foreach ($comments as $comment):
                            $comment_avatar = !empty($comment['avatar']) ? htmlspecialchars($comment['avatar']) : 'images/default-avatar.jpg';
                        ?>
                            <div class="comment">
                                <img src="<?= $comment_avatar ?>" alt="Avatar" style="width: 30px; height: 30px; border-radius: 50%;">
                                <p><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
                                <div class="comment-meta">Автор: <?= htmlspecialchars($comment['username']) ?> | Дата: <?= htmlspecialchars($comment['created_at']) ?></div>
                                <?php if ($is_admin): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="delete_comment_id" value="<?= $comment['id'] ?>">
                                        <button type="submit" class="delete-button">Удалить</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <div class="new-comment">
                            <h5>Добавить комментарий</h5>
                            <form method="POST">
                                <textarea name="comment_content" required></textarea><br>
                                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                <button type="submit">Отправить</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
