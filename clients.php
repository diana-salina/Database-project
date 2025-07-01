<?php
include_once 'config.php';

$role = $_SESSION['role'];

if (isset($_GET['delete_id'])) {
    $client_id = (int)$_GET['delete_id'];
    $dbUser = "client_" . $client_id;

    $pdo->exec("DROP USER IF EXISTS '$dbUser'@'localhost'");

    $stmt = $pdo->prepare("DELETE FROM clients WHERE client_id = ?");
    $stmt->execute([$client_id]);

    header("Location: clients.php");
    exit;
}


$clients = $pdo->query("SELECT * FROM clients")->fetchAll();

$subscriptions_stmt = $pdo->prepare("
    SELECT s.client_id, st.amount, s.rest
    FROM subscriptions s
    JOIN subscription_templates st ON s.subtemp_id = st.subtemp_id
    WHERE s.client_id = ?
");
?>


<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Клиенты</title>
    <link rel="stylesheet" href="coaches_style.css">
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
    <h2>Список клиетнов</h2>

    <div class="top-buttons">
        <a href="add_client.php" class="btn">Добавить клиента</a>
    </div>
    <?php foreach ($clients as $client): ?>
    <div class="coach">
        <p><strong>ФИО:</strong> <?= htmlspecialchars($client['surname']) . ' ' . htmlspecialchars($client['name']) . ' ' . htmlspecialchars($client['patronymic']) ?></p>
        <?php if ($role == 'admin'): ?>
        <p><strong>Логин: </strong>client_<?= htmlspecialchars($client['client_id']) ?></p>
        <?php endif; ?>
        <p><strong>Телефон:</strong> <?= htmlspecialchars($client['phone']) ?></p>
        <p><strong>Пол:</strong> <?= htmlspecialchars($client['sex']) ?></p>
        <p><strong>Дата рождения:</strong> <?= htmlspecialchars($client['birthday']) ?></p>

        <?php
            $subscriptions_stmt->execute([$client['client_id']]);
            $subs = $subscriptions_stmt->fetchAll();
        ?>

        <?php if (count($subs) > 0): ?>
            <p><strong>Абонементы:</strong></p>
            <ul>
                <?php foreach ($subs as $sub): ?>
                    <li>На <?= $sub['amount'] ?> занятий, осталось: <?= $sub['rest'] ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p><em>Нет активных абонементов</em></p>
        <?php endif; ?>

        <div class="coach-buttons">
    <a href="edit_client.php?id=<?= $client['client_id'] ?>" class="btn">Редактировать</a>
    <a href="assign_subscription.php?client_id=<?= $client['client_id'] ?>" class="btn">Оформить абонемент</a>
    <a href="clients.php?delete_id=<?= $client['client_id'] ?>" class="btn" onclick="return confirm('Удалить этого клиента?')">Удалить</a>
</div>

    </div>
<?php endforeach; ?>


</div>
</body>
</html>
