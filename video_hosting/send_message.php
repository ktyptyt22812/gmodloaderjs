<?php
require 'db.php';
session_start();


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT users.id, users.username FROM users 
                       JOIN friends ON friends.friend_id = users.id 
                       WHERE friends.user_id = ?");
$stmt->execute([$user_id]);
$friends = $stmt->fetchAll();

if (isset($_POST['send_message']) && !empty($_POST['message']) && !empty($_POST['receiver_id'])) {
    $receiver_id = (int)$_POST['receiver_id'];
    $message = $_POST['message'];

    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $receiver_id, $message]);
    echo "Сообщение отправлено.";
}

$messages = [];
if (!empty($_GET['friend_id'])) {
    $friend_id = (int)$_GET['friend_id'];
    $stmt = $pdo->prepare("SELECT * FROM messages 
                           WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
                           ORDER BY created_at ASC");
    $stmt->execute([$user_id, $friend_id, $friend_id, $user_id]);
    $messages = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>live chat</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>live chat</h1>
        </div>
        <div class="navbar">
            <h2>Меню</h2>
            <a href="index.php">Главная</a>
            <a href="messenger.php">Найти человека</a>
            <a href="logout.php">Выйти</a>
        </div>
        <div class="content">
            <h2>Ваши друзья</h2>
            <ul>
                <?php foreach ($friends as $friend): ?>
                    <li>
                        <a href="send_message.php?friend_id=<?= $friend['id'] ?>"><?= htmlspecialchars($friend['username']) ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php if (!empty($_GET['friend_id'])): ?>
                <h2>Сообщения с <?= htmlspecialchars($friend['id']) ?></h2>
                <div class="messages">
                    <?php foreach ($messages as $message): ?>
                        <p>
                            <strong><?= ($message['sender_id'] == $user_id) ? 'Вы' : htmlspecialchars($message['sender_id']) ?>:</strong>
                            <?= htmlspecialchars($message['message']) ?>
                        </p>
                    <?php endforeach; ?>
                </div>
                
                <form method="post">
                    <textarea name="message" placeholder="Введите ваше сообщение"></textarea>
                    <input type="hidden" name="receiver_id" value="<?= $_GET['friend_id'] ?>">
                    <button type="submit" name="send_message">Отправить</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
