<?php
include_once 'config.php';

$role = $_SESSION['role'];

if (isset($_GET['delete_id'])) {
    $coach_id = (int)$_GET['delete_id'];
    $dbUser = "coach_" . $coach_id;

    $pdo->exec("DROP USER IF EXISTS '$dbUser'@'localhost'");

    $stmt = $pdo->prepare("DELETE FROM coaches WHERE coach_id = ?");
    $stmt->execute([$coach_id]);

    header("Location: coaches.php");
    exit;
}


// Получение списка тренеров
$coaches = $pdo->query("SELECT * FROM coaches")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Тренеры</title>
    <link rel="stylesheet" href="coaches_style.css">
</head>
<body>
<header>
    <nav class="navbar">
        <a href="main.php">ГЛАВНАЯ</a>
        <a href="schedule.php">РАСПИСАНИЕ</a>
        <a href="templates.php">НАПРАВЛЕНИЯ</a>
        <a href="subscriptions.php">АБОНЕМЕНТЫ</a>
        <?php if ($role != 'coach'): ?>
        <a href="coaches.php">ТРЕНЕРА</a>
        <?php endif; ?>
        <?php if ($role == 'admin'): ?>
        <a href="clients.php">КЛИЕНТЫ</a>
        <a href="statistics.php">СТАТИСТИКА</a>
        <?php endif; ?>
    </nav>
</header>

<hr class="line">
<div class="container">
    <h2>Список тренеров</h2>

    <div class="top-buttons">
        <?php if ($role == 'admin'): ?>
        <a href="add_coach.php" class="btn">Добавить тренера</a>
        <?php endif; ?>
    </div>

    <?php foreach ($coaches as $coach): ?>
        <div class="coach">
            <p><strong>ФИО:</strong> <?= htmlspecialchars($coach['surname']) . ' ' . htmlspecialchars($coach['name']) . ' ' . htmlspecialchars($coach['patronymic']) ?></p>
            <?php if ($role == 'admin'): ?>
            <p><strong>Логин: </strong>coach_<?= htmlspecialchars($coach['coach_id']) ?></p>
            <p><strong>Телефон:</strong> <?= htmlspecialchars($coach['phone']) ?></p>
            <?php endif; ?>
            <p><strong>Пол:</strong> <?= htmlspecialchars($coach['sex']) ?></p>
            <p><strong>Дата рождения:</strong> <?= htmlspecialchars($coach['birthday']) ?></p>
            <?php if ($role == 'admin'): ?>
            <p><strong>Ставка:</strong> <?= htmlspecialchars($coach['bid_per_hour']) ?> ₽</p>
            <?php endif; ?>
            <p><strong></strong> <?= nl2br(htmlspecialchars($coach['description'])) ?></p>

            <?php if ($role == 'admin'): ?>
            <div class="coach-buttons">
                <a href="edit_coach.php?id=<?= $coach['coach_id'] ?>" class="btn">Редактировать</a>
                <a href="coaches.php?delete_id=<?= $coach['coach_id'] ?>" class="btn" onclick="return confirm('Удалить этого тренера?')">Удалить</a>
            </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
</body>
</html>
