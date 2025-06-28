<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "Ошибка: Вы должны быть авторизованы, чтобы ставить или убирать лайки.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post_id = $_POST['post_id'];
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT * FROM forum_posts WHERE id = ?");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch();

    if (!$post) {
        echo "Ошибка: Пост не существует.";
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM likes WHERE post_id = ? AND user_id = ?");
    $stmt->execute([$post_id, $user_id]);
    $like = $stmt->fetch();

    if ($like) {

        $stmt = $pdo->prepare("DELETE FROM likes WHERE post_id = ? AND user_id = ?");
        $stmt->execute([$post_id, $user_id]);

        $stmt = $pdo->prepare("UPDATE forum_posts SET likes_count = likes_count - 1 WHERE id = ?");
        $stmt->execute([$post_id]);

        echo "Лайк успешно удален!";
    } else {

        $stmt = $pdo->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)");
        $stmt->execute([$post_id, $user_id]);

        $stmt = $pdo->prepare("UPDATE forum_posts SET likes_count = likes_count + 1 WHERE id = ?");
        $stmt->execute([$post_id]);

        echo "Лайк успешно добавлен!";
    }

    header("Location: forum.php");
    exit;
}
?>
