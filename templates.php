<?php
include_once 'config.php';

$role = $_SESSION['role'];

// Удаление шаблона
if (isset($_GET['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM templates WHERE template_id = ?");
    $stmt->execute([$_GET['delete_id']]);
    header("Location: templates.php");
    exit;
}

$templates = $pdo->query("SELECT * FROM templates")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Направления тренировок</title>
    <link rel="stylesheet" href="templates_style.css">
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

<hr class="line">
    <div class="container">
        <div class="header">
            <h1>Направления тренировок</h1>
            <?php if($role == 'admin'): ?>
            <a class="add-button" href="add_template.php">Добавить направление</a>
            <?php endif; ?>
        </div>

        <?php if (empty($templates)): ?>
            <p>Пока нет направлений.</p>
        <?php else: ?>
            <div class="template-list">
                <?php foreach ($templates as $template): ?>
                    <div class="template-card">
                        <div class="template-info">
                            <h2><?= htmlspecialchars($template['name']) ?></h2>
                            <p><strong>Длительность:</strong> <?= $template['duration'] ?> мин</p>
                            <p><?= nl2br(htmlspecialchars($template['description'])) ?></p>
                        </div>
                        <?php if($role == 'admin'): ?>
                        <form method="GET" onsubmit="return confirm('Удалить это направление?')">
                            <input type="hidden" name="delete_id" value="<?= $template['template_id'] ?>">
                            <button type="submit" class="delete-button">Удалить</button>
                        </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
