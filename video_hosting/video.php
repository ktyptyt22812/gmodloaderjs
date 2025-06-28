<?php
require 'db.php';
session_start();

$is_logged_in = isset($_SESSION['user_id']);
$is_admin = false;
$user_id = null;

if ($is_logged_in) {
    $user_id = $_SESSION['user_id'];
    // Проверка прав администратора
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    if ($user && $user['role'] === 'admin') {
        $is_admin = true;
    }
}

// Проверка ID видео
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Некорректный идентификатор видео.";
    exit;
}

$video_id = (int)$_GET['id'];

// Получение информации о видео
$stmt = $pdo->prepare("SELECT videos.*, users.username FROM videos JOIN users ON videos.user_id = users.id WHERE videos.id = ?");
$stmt->execute([$video_id]);
$video = $stmt->fetch();

if (!$video) {
    echo "Видео не найдено.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Просмотр видео</title>
    <style>
        /* Основные стили страницы */
        body {
            font-family: Arial, sans-serif;
            background-color: #e5e5e5;
            margin: 0;
            padding: 0;
        }

        /* Контейнер для всего содержимого */
        .container {
            width: 960px;
            margin: 80px auto 0; /* Отступ сверху для фиксации навбара */
            background-color: #ffffff;
            padding: 20px;
            border: 1px solid #ccc;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        /* Навигационная панель */
        .navbar {
            position: fixed; /* Фиксируем навбар наверху страницы */
            top: 0;
            left: 0;
            width: 100%;
            background-color: #000080;
            color: #ffffff;
            padding: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            z-index: 1000; /* Навбар поверх всего остального контента */
        }

        .navbar ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            justify-content: center;
        }

        .navbar li {
            margin: 0 15px;
        }

        .navbar a {
            color: #ffffff;
            text-decoration: none;
            font-size: 16px;
            font-weight: bold;
        }

        .navbar a:hover {
            text-decoration: underline;
        }

        /* Стили заголовка */
        .header {
            background-color: #000080;
            color: #ffffff;
            padding: 10px;
            text-align: center;
            font-size: 24px;
            border-bottom: 3px solid #cc0000;
        }

        /* Стили основного контента */
        .content {
            padding: 20px;
        }

        /* Стили контейнера видео */
        .video-container {
            background-color: #f5f5f5;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        /* Стили заголовка видео */
        .video-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
        }

        /* Стили видеофайла */
        .video-file {
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #000;
            margin-bottom: 15px;
        }

        /* Стили описания видео */
        .video-description {
            font-size: 16px;
            color: #666;
            margin-bottom: 15px;
        }

        /* Стили владельца видео */
        .video-owner {
            font-size: 14px;
            color: #000;
        }

        .video-owner a {
            color: #ff0000;
            text-decoration: none;
        }

        .video-owner a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- Навигационная панель -->
    <div class="navbar">
            <h2>Меню</h2>
            <a href="index.php">Список видео</a>
            <link rel="stylesheet" href="video.css"> <!-- Подключаем ваш CSS файл -->
            <?php if ($is_logged_in): ?>
                <a href="upload.php">Загрузить видео</a>
                <a href="forum.php">Форум</a>
                <a href="logout.php">Выйти</a>
                
                <?php if ($is_admin): ?>
                    <a href="delete_account.php">Удалить аккаунт</a>
                <?php endif; ?>
            
            <?php else: ?>
                <a href="login.php">Войти</a>
                <a href="register.php">Зарегистрироваться</a>
            <?php endif; ?>
        </div>
    <div class="container">
        <div class="header">
            Липучка video
        </div>

        <div class="content">
            <div class="video-container">
                <h1 class="video-title"><?= htmlspecialchars($video['title']) ?></h1>
                <video class="video-file" src="uploads/<?= htmlspecialchars($video['file_name']) ?>" controls></video>
                <div class="video-description"><?= nl2br(htmlspecialchars($video['description'])) ?></div>
                <div class="video-owner">
                    Загружено: <a href="profile.php?user_id=<?= htmlspecialchars($video['user_id']) ?>"><?= htmlspecialchars($video['username']) ?></a> | Дата: <?= htmlspecialchars($video['upload_date']) ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
