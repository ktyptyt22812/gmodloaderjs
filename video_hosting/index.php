<?php
require 'db.php';
session_start();

$is_logged_in = isset($_SESSION['user_id']);
$is_admin = false;
$user_id = null;

if ($is_logged_in) {
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    if ($user && $user['role'] === 'admin') {
        $is_admin = true;
    }
} else {

    $is_guest = true;
}


$stmt = $pdo->query("SELECT videos.*, users.username FROM videos JOIN users ON videos.user_id = users.id");
$videos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>zhest</title>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная страница</title>
    <link rel="stylesheet" href="style.css"> <!-- Подключаем CSS файл -->
</head>
<body>
    <div class="navbar">
        <a href="index.php">Список видео</a>
        
        <?php if ($is_logged_in): ?>
            <a href="upload.php" class="uplb">Загрузить видео</a>
            <a href="forum.php">Форум</a>
            <div class="separator"></div> 
            <a href="logout.php" class="logout-button">Выйти</a>
            <a href="delete_account.php">Удалить аккаунт</a>
            <a href="send_message.php">Мессенджер</a>
            <?php if ($is_admin): ?>
                <a href="admin_panel.php" class="admin-button">Админ панель</a>
            <?php endif; ?>
        
        <?php else: ?>
            <a href="login.php">Войти</a>
            <a href="register.php">Зарегистрироваться</a>
        <?php endif; ?>
    </div>
    

    <div class="container">
        <div class="header">
            <h1>Липучка videos</h1>
        </div>

        <div class="content">
            <h2>Видео:</h2>
            <div class="video-grid">
                <?php foreach ($videos as $video): ?>
                    <div class="video-container">
                        <a href="video.php?id=<?= htmlspecialchars($video['id']) ?>">
                            <?php
                            $thumbnail_path = 'uploads/' . htmlspecialchars($video['thumbnail']);
                            if (file_exists($thumbnail_path) && !empty($video['thumbnail'])) {
                                $img_src = $thumbnail_path;
                            } else {
                                $img_src = 'uploads/default_thumbnail.png';
                            }
                            ?>
                            <img class="video-thumbnail" src="<?= $img_src ?>" alt="Thumbnail">
                            <div class="video-title"><?= htmlspecialchars($video['title']) ?></div>
                        </a>
                        <div class="video-description"><?= nl2br(htmlspecialchars($video['description'])) ?></div>
                        <div class="video-owner">
                            Загружено: <a href="profile.php?user_id=<?= htmlspecialchars($video['user_id']) ?>"><?= htmlspecialchars($video['username']) ?></a>
                        </div>
                        <?php if ($is_admin || $video['user_id'] == $user_id): ?>
                            <form method="POST" action="delete_video.php" style="position: absolute; bottom: 10px; right: 10px;">
                                <input type="hidden" name="video_id" value="<?= $video['id'] ?>">
                                <button class="delete-button" type="submit">Удалить</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>