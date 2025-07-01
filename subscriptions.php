<?php 
include_once 'config.php';

$role = $_SESSION['role'];
$client_id = $_SESSION['user_id'] ?? null;

// Удаление шаблона абонемента
if (isset($_GET['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM subscription_templates WHERE subtemp_id = ?");
    $stmt->execute([$_GET['delete_id']]);
    header("Location: subscriptions.php");
    exit;
}

$subscriptions = $pdo->query("SELECT * FROM subscription_templates")->fetchAll();

// Мои абонементы (только для клиента)
$my_subs = [];
if ($role === 'client' && $client_id) {
    $stmt = $pdo->prepare("
        SELECT st.name, s.purchase_date, s.expiration_date, s.rest, st.amount, st.cost
        FROM subscriptions s
        JOIN subscription_templates st ON s.subtemp_id = st.subtemp_id
        WHERE s.client_id = ?
    ");
    $stmt->execute([$client_id]);
    $my_subs = $stmt->fetchAll();
}
$templates = $pdo->query("SELECT * FROM subscription_templates")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Абонементы</title>
    <link rel="stylesheet" href="templates_style.css">
</head>
<body>
<header>
    <nav class="navbar">
        <a href="main.php">ГЛАВНАЯ</a>
        <a href="schedule.php">РАСПИСАНИЕ</a>
        <a href="templates.php">НАПРАВЛЕНИЯ</a>
        <a href="subscriptions.php">АБОНЕМЕНТЫ</a>
        <?php if ($role !== 'coach'): ?>
        <a href="coaches.php">ТРЕНЕРА</a>
        <?php endif; ?>
        <?php if ($role === 'admin'): ?>
        <a href="clients.php">КЛИЕНТЫ</a>
        <a href="statistics.php">СТАТИСТИКА</a>
        <?php endif; ?>
    </nav>
</header>

<hr class="line">

<div class="container">
    <div class="header">
        <h1>Абонементы</h1>
        <?php if ($role === 'admin'): ?>
        <a class="add-button" href="add_subscription.php">Добавить абонемент</a>
        <?php endif; ?>
    </div>

    <!-- Мои абонементы (для клиента) -->
    <?php if ($role === 'client'): ?>
        <h2>Мои абонементы</h2>
        <?php if (empty($my_subs)): ?>
            <p>У вас пока нет активных абонементов.</p>
        <?php else: ?>
            <div class="template-list">
                <?php foreach ($my_subs as $sub): ?>
                    <div class="template-card">
                        <div class="template-info">
                            <h3><?= htmlspecialchars($sub['name']) ?></h3>
                            <p><strong>Куплен:</strong> <?= $sub['purchase_date'] ?></p>
                            <p><strong>Истекает:</strong> <?= $sub['expiration_date'] ?></p>
                            <p><strong>Осталось занятий:</strong> <?= $sub['rest'] ?> / <?= $sub['amount'] ?></p>
                            <p><strong>Стоимость:</strong> <?= $sub['cost'] ?> ₽</p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <hr>
    <?php endif; ?>

    <!-- Шаблоны абонементов -->
    <h2>Доступные абонементы</h2>
    <?php if (empty($templates)): ?>
        <p>Пока нет шаблонов абонементов.</p>
    <?php else: ?>
        <div class="template-list">
            <?php foreach ($templates as $sub): ?>
                <div class="template-card">
                    <div class="template-info">
                        <h3><?= htmlspecialchars($sub['name']) ?></h3>
                        <p><strong>Количество занятий:</strong> <?= $sub['amount'] ?></p>
                        <p><strong>Стоимость:</strong> <?= $sub['cost'] ?> ₽</p>
                    </div>
                    <?php if ($role === 'admin'): ?>
                    <form method="GET" onsubmit="return confirm('Удалить этот абонемент?')">
                        <input type="hidden" name="delete_id" value="<?= $sub['subtemp_id'] ?>">
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
