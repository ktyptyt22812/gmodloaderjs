<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$profile_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$profile_user_id]);
$profile_user = $stmt->fetch();
if (!$profile_user) {
    die("Пользователь не найден.");
}

$is_admin = false;
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
if ($user['role'] === 'admin') {
    $is_admin = true;
}

$stmt = $pdo->prepare("SELECT * FROM videos WHERE user_id = ?");
$stmt->execute([$profile_user_id]);
$videos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Профиль пользователя</title>
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
            background-color: #4682B4;
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
            background-color: #e9e9e9;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        .sidebar a:hover {
            background-color: #d4d4d4;
        }
        .content {
            margin-left: 220px;
            padding: 20px;
        }
        .video-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
        }
        .video-container {
            background-color: #f5f5f5;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            position: relative;
        }
        .video-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }
        .video-description {
            font-size: 12px;
            color: #666;
            margin-bottom: 10px;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }
        .video-file {
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #000;
            margin-bottom: 10px;
        }
        .video-owner {
            font-size: 12px;
            color: #333;
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
    <div class="container">
        <div class="header">
            Профиль пользователя
        </div>
        <div class="sidebar">
            <h2>Меню</h2>
            <a href="index.php">Список видео</a>
            <a href="logout.php">Выйти</a>
        </div>
        <div class="content">
            <h2>Видео пользователя <?= htmlspecialchars($profile_user['username']) ?>:</h2>
            <div class="video-grid">
                <?php foreach ($videos as $video): ?>
                    <div class="video-container">
                        <div class="video-title"><?= htmlspecialchars($video['title']) ?></div>
                        <video class="video-file" src="uploads/<?= htmlspecialchars($video['file_name']) ?>" controls></video>
                        <div class="video-description"><?= nl2br(htmlspecialchars($video['description'])) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>
