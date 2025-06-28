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

if (!$user || $user['role'] !== 'admin') {
    echo "У вас нет прав для доступа к этой странице.";
    exit;
}

$stmt = $pdo->query("SELECT * FROM users");
$users = $stmt->fetchAll();

$stmt = $pdo->query("SELECT videos.*, users.username FROM videos JOIN users ON videos.user_id = users.id");
$videos = $stmt->fetchAll();

if (isset($_POST['delete_user'])) {
    $user_id_to_delete = $_POST['user_id'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id_to_delete]);
    header("Location: admin_panel.php");
    exit;
}


if (isset($_POST['delete_video'])) {
    $video_id_to_delete = $_POST['video_id'];
    $stmt = $pdo->prepare("DELETE FROM videos WHERE id = ?");
    $stmt->execute([$video_id_to_delete]);
    header("Location: admin_panel.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ панель</title>
</head>
<body>
    <h1>Административная панель</h1>
    
    <h2>Список пользователей</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>username</th>
            <th>Email</th>
            <th>role</th>
            <th>Действия</th>
        </tr>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['id']) ?></td>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= htmlspecialchars($user['role']) ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
                        <button type="submit" name="delete_user">Удалить</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h2>Список видео</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Название</th>
            <th>Описание</th>
            <th>Пользователь</th>
            <th>Дата загрузки</th>
            <th>Действия</th>
        </tr>
        <?php foreach ($videos as $video): ?>
            <tr>
                <td><?= htmlspecialchars($video['id']) ?></td>
                <td><?= htmlspecialchars($video['title']) ?></td>
                <td><?= htmlspecialchars($video['description']) ?></td>
                <td><?= htmlspecialchars($video['username']) ?></td>
                <td><?= htmlspecialchars($video['upload_date']) ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="video_id" value="<?= htmlspecialchars($video['id']) ?>">
                        <button type="submit" name="delete_video">Удалить</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h2>Отправить SQL-запрос</h2>
    <form method="POST">
        <textarea name="sql_query" rows="5" cols="50" placeholder="Введите SQL-запрос"></textarea><br>
        <button type="submit" name="execute_query">Выполнить запрос</button>
    </form>

    <?php

    if (isset($_POST['execute_query'])) {
        $sql_query = $_POST['sql_query'];
        try {
            $stmt = $pdo->query($sql_query);
            echo "<h3>Результаты запроса:</h3>";
            echo "<table border='1'>";
            echo "<tr>";

            for ($i = 0; $i < $stmt->columnCount(); $i++) {
                $columnMeta = $stmt->getColumnMeta($i);
                echo "<th>" . htmlspecialchars($columnMeta['name']) . "</th>";
            }
            echo "</tr>";
            

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>";
                foreach ($row as $cell) {
                    echo "<td>" . htmlspecialchars($cell) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } catch (PDOException $e) {
            echo "<p>Ошибка выполнения запроса: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
    ?>

</body>
</html>
