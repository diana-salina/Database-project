<?php

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получение данных из формы
    $HOST = $_POST['server'] ?? 'localhost';
    $USER = trim($_POST['username'] ?? 'root');
    $PASSWORD = $_POST['password'] ?? '';
    $DBNAME = 'fitness_salina';

    // Сохраняем данные в сессию
    $_SESSION['host'] = $HOST;
    $_SESSION['user'] = $USER;

    $_SESSION['password'] = $PASSWORD;

    // Определение роли по имени пользователя
    if (strpos($USER, 'client_') === 0) {
        $_SESSION['role'] = 'client';
    } elseif (strpos($USER, 'coach_') === 0) {
        $_SESSION['role'] = 'coach';
    } elseif ($USER === 'admin' || $USER === 'root') {
        $_SESSION['role'] = 'admin';
    } else {
        $_SESSION['role'] = 'unknown';
    }

    if ($_SESSION['role'] === 'client') {
        $_SESSION['user_id'] = intval(substr($USER, strlen('client_')));
    } elseif ($_SESSION['role'] === 'coach') {
        $_SESSION['user_id'] = intval(substr($USER, strlen('coach_')));
    }

    try {
        // $dsn = "mysql:host=$HOST";
        $dsn = ($USER === 'root' || $USER === 'admin') 
        ? "mysql:host=$HOST;charset=utf8mb4" 
        : "mysql:host=$HOST;dbname=fitness_salina;charset=utf8mb4";

    $pdo = new PDO($dsn, $USER, $PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Только root может запускать reset_db.sql
        if ($USER === 'root' || $USER === 'admin') {
            $stmt = $pdo->query("SHOW DATABASES LIKE '$DBNAME'");
            $dbExists = $stmt->fetch();
            // Если базы данных нет — загружаем и запускаем reset_db.sql
            if (!$dbExists) {
                $Q = file_get_contents('reset_db.sql');
                $pdo->exec($Q);
                $Q = file_get_contents('get_client_visits.sql');
                $pdo->exec($Q);
                $Q = file_get_contents('get_coach_workouts.sql');
                $pdo->exec($Q);
                $Q = file_get_contents('get_workouts_by_date.sql');
                $pdo->exec($Q);
                $Q = file_get_contents('mark_attendance.sql');
                $pdo->exec($Q);
                $Q = file_get_contents('book_workout.sql');
                $pdo->exec($Q);
                $Q = file_get_contents('views.sql');
                $pdo->exec($Q);
                $Q = file_get_contents('fill_bd.sql');
                $pdo->exec($Q);
            }
        }

        // Если подключение прошло успешно — переходим в main.php
        header("Location: main.php");
        exit;
    } catch (PDOException $e) {
        $error = "Ошибка подключения: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход</title>
    <link rel="stylesheet" href="login_style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
        }
        .error {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <form class="login-form" method="POST">
            <h2>ВХОД</h2>

            <label for="server">СЕРВЕР:</label>
            <input type="text" id="server" name="server" value="localhost">

            <label for="username">ИМЯ ПОЛЬЗОВАТЕЛЯ:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">ПАРОЛЬ:</label>
            <div class="password-container">
                <input type="password" id="password" name="password" required>
            </div>
            <div class="toggle-password">
                <button type="button" id="togglePassword">Показать пароль</button>
            </div>

            <button type="submit" class="login-button">ВОЙТИ</button>

            <?php if (!empty($error)): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
        </form>
    </div>

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordField = document.getElementById('password');

        togglePassword.addEventListener('click', function () {
            const isPassword = passwordField.getAttribute('type') === 'password';
            passwordField.setAttribute('type', isPassword ? 'text' : 'password');
            togglePassword.textContent = isPassword ? 'Скрыть пароль' : 'Показать пароль';
        });
    </script>
</body>
</html>
