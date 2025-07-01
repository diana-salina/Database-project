<?php
include_once 'config.php';

function fetchFromView($pdo, string $view, int $limit = 5): array {
    $sql = "SELECT * FROM $view LIMIT :limit";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

$top_attending = fetchFromView($pdo, 'top_attending_clients', $_GET['top_attending'] ?? 5);
$top_skipping = fetchFromView($pdo, 'top_skipping_clients', $_GET['top_skipping'] ?? 5);
$top_coaches = fetchFromView($pdo, 'top_coaches_view', $_GET['top_coaches'] ?? 5);
$top_templates = fetchFromView($pdo, 'top_templates_view', $_GET['top_templates'] ?? 5);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Статистика</title>
    <link rel="stylesheet" href="templates_style.css">
    <style>
        .stat-section {
            background: #fff;
            padding: 20px;
            margin-bottom: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .stat-section h2 {
            margin-top: 0;
        }
        form {
            margin-bottom: 15px;
        }
        form input[type="number"] {
            padding: 5px;
            width: 60px;
            font-size: 16px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        form button {
            padding: 5px 12px;
            background-color: #ce8bc0;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        form button:hover {
            background-color: #b870aa;
        }
    </style>
</head>
<body>
<header>
    <nav class="navbar">
        <a href="main.php">ГЛАВНАЯ</a>
        <a href="schedule.php">РАСПИСАНИЕ</a>
        <a href="templates.php">НАПРАВЛЕНИЯ</a>
        <a href="subscriptions.php">АБОНЕМЕНТЫ</a>
        <a href="coaches.php">ТРЕНЕРА</a>
        <a href="clients.php">КЛИЕНТЫ</a>
        <a href="statistics.php">СТАТИСТИКА</a>
    </nav>
</header>

<hr class="line">
<div class="container">

    <div class="stat-section">
        <h2>Топ клиентов по посещаемости</h2>
        <form method="GET">
            <label>Количество: <input type="number" name="top_attending" min="1" required></label>
            <button type="submit">Показать</button>
        </form>
        <?php if ($top_attending): ?>
            <ul>
                <?php foreach ($top_attending as $client): ?>
                    <li><?= htmlspecialchars("{$client['surname']} {$client['name']} {$client['patronymic']}") ?> — <?= $client['count'] ?> (посещений)</li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <div class="stat-section">
        <h2>Топ клиентов по пропускам</h2>
        <form method="GET">
            <label>Количество: <input type="number" name="top_skipping" min="1" required></label>
            <button type="submit">Показать</button>
        </form>
        <?php if ($top_skipping): ?>
            <ul>
                <?php foreach ($top_skipping as $client): ?>
                    <li><?= htmlspecialchars("{$client['surname']} {$client['name']} {$client['patronymic']}") ?> — <?= $client['count'] ?> (пропусков)</li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <div class="stat-section">
        <h2>Топ тренеров по проведённым тренировкам</h2>
        <form method="GET">
            <label>Количество: <input type="number" name="top_coaches" min="1" required></label>
            <button type="submit">Показать</button>
        </form>
        <?php if ($top_coaches): ?>
            <ul>
                <?php foreach ($top_coaches as $coach): ?>
                    <li><?= htmlspecialchars("{$coach['surname']} {$coach['name']} {$coach['patronymic']}") ?>: <?= $coach['count'] ?> (тренировок)</li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <div class="stat-section">
        <h2>Популярные направления</h2>
        <form method="GET">
            <label>Количество: <input type="number" name="top_templates" min="1" required></label>
            <button type="submit">Показать</button>
        </form>
        <?php if ($top_templates): ?>
            <ul>
                <?php foreach ($top_templates as $template): ?>
                    <li><?= htmlspecialchars($template['name']) ?>: <?= $template['count'] ?> (записей)</li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

</div>
</body>
</html>
