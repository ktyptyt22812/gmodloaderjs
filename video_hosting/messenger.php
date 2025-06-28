<?php
require 'db.php';
session_start();


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];


$search_results = [];
if (isset($_POST['search']) && !empty($_POST['username'])) {
    $username = '%' . $_POST['username'] . '%';
    

    echo "Ищем пользователя: " . htmlspecialchars($_POST['username']) . "<br>";
    
    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE username LIKE ? AND id != ?");
    $stmt->execute([$username, $user_id]);
    $search_results = $stmt->fetchAll();

    echo "Найдено пользователей: " . count($search_results) . "<br>";
}

if (isset($_POST['add_friend']) && !empty($_POST['friend_id'])) {
    $friend_id = (int)$_POST['friend_id'];


    $stmt = $pdo->prepare("SELECT * FROM friends WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)");
    $stmt->execute([$user_id, $friend_id, $friend_id, $user_id]);
    
    if ($stmt->rowCount() === 0) {
        $stmt = $pdo->prepare("INSERT INTO friends (user_id, friend_id) VALUES (?, ?), (?, ?)");
        $stmt->execute([$user_id, $friend_id, $friend_id, $user_id]);
        echo "Пользователь добавлен в друзья.";
        header("Location: send_message.php");
        exit;
    } else {
        echo "Вы уже друзья или запрос на дружбу отправлен.";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Найти человека</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Найти человека</h1>
        </div>
        <div class="navbar">
            <h2>Меню</h2>
            <a href="index.php">Главная</a>
            <a href="send_message.php">live chat</a>
            <a href="logout.php">Выйти</a>
        </div>
        <div class="content">
            <h2>Поиск пользователей</h2>
            <form method="post">
                <input type="text" name="username" placeholder="Введите имя пользователя">
                <button type="submit" name="search">Поиск</button>
            </form>
            
            <?php if (!empty($search_results)): ?>
                <h3>Результаты поиска:</h3>
                <ul>
                    <?php foreach ($search_results as $user): ?>
                        <li>
                            <?= htmlspecialchars($user['username']) ?>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="friend_id" value="<?= $user['id'] ?>">
                                <button type="submit" name="add_friend">Добавить в друзья</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Нет пользователей для отображения.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
