<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT * FROM videos WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $videos = $stmt->fetchAll();

    foreach ($videos as $video) {
        unlink("uploads/" . $video['file_name']);
    }

    $stmt = $pdo->prepare("DELETE FROM videos WHERE user_id = ?");
    $stmt->execute([$user_id]);

    // Удаление пользователя
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);

    session_destroy();
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>удаление</title>
</head>
<body>
    <h1>уделение</h1>
    <form method="POST">
        <p>уверены? это удалит все что было связано с вашим аккаунтом.</p>
        <button type="submit">удалить</button>
        <a href="index.php">отменить</a>
    </form>
</body>
</html>
