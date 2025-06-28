<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $user_id = $_SESSION['user_id'];


        $unique_id = bin2hex(random_bytes(16));
        $video_extension = pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION);
        $video_file_name = $unique_id . '.' . $video_extension;
        $thumbnail_file_name = $unique_id . '.jpg';


        $target_dir = "uploads/";
        $target_file = $target_dir . $video_file_name;
        $thumbnail_file_path = $target_dir . $thumbnail_file_name;

        if (move_uploaded_file($_FILES["video"]["tmp_name"], $target_file)) {
            $command = "ffmpeg -i \"$target_file\" -ss 00:00:01.000 -vframes 1 \"$thumbnail_file_path\"";
            exec($command, $output, $return_var);

            if ($return_var === 0) {

                $stmt = $pdo->prepare("INSERT INTO videos (title, description, file_name, thumbnail, user_id) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$title, $description, $video_file_name, $thumbnail_file_name, $user_id]);
                echo "Видео успешно загружено!";
                header('Location: index.php');
                exit();
            } else {
                echo "Ошибка при создании миниатюры.";
            }
        } else {
            echo "Произошла ошибка при загрузке файла.";
        }
    } else {
        echo "Файл не был загружен или произошла ошибка при загрузке.";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Загрузка видео</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
        }
        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 0 auto;
        }
        label {
            display: block;
            margin-bottom: 10px;
        }
        input[type="text"], textarea, input[type="file"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h1>Загрузите ваше видео</h1>
    <form method="POST" enctype="multipart/form-data">
        <label for="title">Название:</label>
        <input type="text" name="title" required>
        <label for="description">Описание:</label>
        <textarea name="description" required></textarea>
        <label for="video">Файл видео:</label>
        <input type="file" name="video" accept="video/*" required>
        <button type="submit">Загрузить</button>
    </form>
</body>
</html>
