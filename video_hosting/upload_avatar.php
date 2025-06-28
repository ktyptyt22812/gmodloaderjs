<?php
session_start();
require 'db.php';


if (!isset($pdo)) {
    die('Ошибка подключения к базе данных.');
}

if (!isset($_SESSION['user_id'])) {
    die('Вы не авторизованы.');
}

$user_id = $_SESSION['user_id'];


$upload_dir = 'images/';


if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['avatar'])) {
    $file = $_FILES['avatar'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $file['tmp_name'];
        $name = basename($file['name']);
        $upload_path = $upload_dir . $name;

        if (move_uploaded_file($tmp_name, $upload_path)) {

            $user_id = $_SESSION['user_id'];
            $avatar_url = $upload_path;

            $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
            $stmt->execute([$avatar_url, $user_id]);

            echo "Аватар обновлен успешно!";
            header('Location: forum.php');
            exit();
        } else {
            echo "Ошибка при загрузке файла.";
        }
    } else {
        echo "Ошибка при загрузке файла.";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Обновить аватар</title>
</head>
<body>
    <form method="POST" enctype="multipart/form-data">
        <label for="avatar">Выберите аватар:</label>
        <input type="file" name="avatar" accept="image/*" required>
        <button type="submit">Обновить аватар</button>
    </form>
</body>
</html>
