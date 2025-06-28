<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];


$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$is_admin = $user['role'] === 'admin';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['video_id'])) {
    $video_id = $_POST['video_id'];

    if ($is_admin) {
        $stmt = $pdo->prepare("SELECT file_name FROM videos WHERE id = ?");
        $stmt->execute([$video_id]);
        $video = $stmt->fetch();

        if ($video) {
            $stmt = $pdo->prepare("DELETE FROM videos WHERE id = ?");
            $stmt->execute([$video_id]);

            $file_path = 'uploads/' . $video['file_name'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            echo "Видео успешно удалено!";
            header('Location: index.php');
            exit();
        } else {
            echo "Видео не найдено.";
        }
    } else {
        $stmt = $pdo->prepare("SELECT file_name FROM videos WHERE id = ? AND user_id = ?");
        $stmt->execute([$video_id, $user_id]);
        $video = $stmt->fetch();

        if ($video) {
            $stmt = $pdo->prepare("DELETE FROM videos WHERE id = ? AND user_id = ?");
            $stmt->execute([$video_id, $user_id]);

            // Удаление файла
            $file_path = 'uploads/' . $video['file_name'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            echo "видео удалено!";
        } else {
            echo "ошибка.";
        }
    }
} else {
    echo "неверный запрос.";
}
?>
