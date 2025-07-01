<?php
include_once 'config.php';

session_start();

$role = $_SESSION['role'] ?? 'client';

// Проверяем, есть ли данные подключения в сессии
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

// Подключаемся к БД
try {
    $dsn = "mysql:host={$_SESSION['host']};dbname=fitness_salina";
    $pdo = new PDO($dsn, $_SESSION['user'], $_SESSION['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к БД: " . $e->getMessage());
}

// Обработка кнопки "Выйти"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Обработка кнопки "Сбросить базу данных"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_db'])) {
    // Пример очистки всех данных из таблиц (убедись, что это безопасно!)
    $pdo->exec("DROP DATABASE IF EXISTS fitness_salina");

    session_destroy();
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Главная</title>
    <link rel="stylesheet" href="main_style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet">
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

<main class="content">
    <div class="card-container">
        <a href="schedule.php" class="card">РАСПИСАНИЕ</a>
        <?php if ($role == 'admin'): ?>
        <a href="statistics.php" class="card">ПОЛУЧИТЬ СТАТИСТИКУ</a>
        <?php endif; ?>
        <?php if ($role != 'coach'): ?>
        <a href="coaches.php" class="card">ПРОСМОТРЕТЬ ИНФОРМАЦИЮ О ТРЕНЕРАХ</a>
        <?php endif; ?>
        <?php if ($role == 'admin'): ?>
        <a href="clients.php" class="card">ПРОСМОТРЕТЬ ИНФОРМАЦИЮ О КЛИЕНТАХ</a>
        <?php endif; ?>
        <a href="templates.php" class="card">ПРОСМОТРЕТЬ ИНФОРМАЦИЮ О НАПРАВЛЕНИЯХ</a>
        <a href="subscriptions.php" class="card">ПРОСМОТРЕТЬ ИНФОРМАЦИЮ ОБ АБОНЕМЕНТАХ</a>
    </div>


    <div class="buttons-container">
    <form method="post" class="inline-form">
        <button type="submit" name="logout" class="button exit-button">ВЫЙТИ</button>
    </form>
    <?php if ($role === 'admin'): ?>
    <form method="post" class="inline-form" onsubmit="return confirm('Вы уверены, что хотите сбросить базу данных? Это удалит все данные.');">
        <button type="submit" name="reset_db" class="button reset-button">СБРОСИТЬ БАЗУ ДАННЫХ</button>
    </form>
    <?php endif; ?>
</div>
    </main>
</body>
</html>
