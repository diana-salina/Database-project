<?php 
include_once 'config.php';
session_start();

// Проверка авторизации
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

// Получение данных пользователя
$role = $_SESSION['role'] ?? 'client';
$user_id = $_SESSION['user_id'];
$username = $_SESSION['user'];
$selectedDate = $_GET['date'] ?? date('Y-m-d');

$success_message = '';
$error_message = '';

// Определение недельного диапазона
$startOfWeek = date('Y-m-d', strtotime('monday this week', strtotime($selectedDate)));
$endOfWeek = date('Y-m-d', strtotime('sunday this week', strtotime($selectedDate)));

$workouts = [];

try {
    if ($role === 'coach') {
        // Для тренера - его тренировки
        $stmt = $pdo->prepare("CALL get_coach_workouts(?, ?)");
        $stmt->execute([$user_id, $selectedDate]);
    } else {
        // Для всех остальных - все тренировки на дату
        $stmt = $pdo->prepare("CALL get_workouts_by_date(?)");
        $stmt->execute([$selectedDate]);
    }
    $workouts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor(); // важно
} catch (PDOException $e) {
    $error_message = "Ошибка загрузки расписания: " . $e->getMessage();
}

// Получение записей клиента
$clientVisits = [];
if ($role === 'client') {
    try {
        $stmt = $pdo->prepare("CALL get_client_visits(?)");
        $stmt->execute([$user_id]);
        $clientVisits = $stmt->fetchAll(PDO::FETCH_COLUMN, 1); // workout_id
        $stmt->closeCursor(); // важно
    } catch (PDOException $e) {
        $error_message = "Ошибка загрузки ваших записей: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_workout_id'])) {
    $delete_id = $_POST['delete_workout_id'];

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT client_id FROM visits WHERE workout_id = ?");
        $stmt->execute([$delete_id]);
        $clients = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($clients as $client_id) {
            $stmt = $pdo->prepare("
                SELECT s.subscription_id
                FROM subscriptions s
                JOIN subscription_templates t ON s.subtemp_id = t.subtemp_id
                WHERE s.client_id = ?
                  AND s.expiration_date >= CURDATE()
                  AND s.rest < t.amount
                ORDER BY s.expiration_date ASC
                LIMIT 1
            ");
            $stmt->execute([$client_id]);
            $subscription_id = $stmt->fetchColumn();

            if ($subscription_id) {
                $stmt = $pdo->prepare("
                    UPDATE subscriptions
                    SET rest = rest + 1
                    WHERE subscription_id = ?
                ");
                $stmt->execute([$subscription_id]);
            }
        }

        $stmt = $pdo->prepare("DELETE FROM visits WHERE workout_id = ?");
        $stmt->execute([$delete_id]);

        $stmt = $pdo->prepare("DELETE FROM workouts WHERE workout_id = ?");
        $stmt->execute([$delete_id]);

        $pdo->commit();
        header("Location: schedule.php");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Ошибка при удалении тренировки: " . $e->getMessage());
    }
}




// Обработка записи клиента
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_workout_id']) && $role === 'client') {
    $workout_id = $_POST['book_workout_id'];

    try {
        $pdo->beginTransaction();

        // Вызов процедуры записи
        $stmt = $pdo->prepare("CALL book_workout(?, ?, @result)");
        $stmt->execute([$user_id, $workout_id]);
        $stmt->closeCursor();

        // Получение результата
        $result = $pdo->query("SELECT @result AS result")->fetch(PDO::FETCH_ASSOC);
        if (!$result || $result['result'] !== 'OK') {
            throw new Exception($result['result'] ?? 'Неизвестная ошибка');
        }

        $pdo->commit();
        $success_message = "Вы успешно записались на тренировку.";

        // Обновление записей
        $clientVisits = [];
        if ($role === 'client') {
            $stmt = $pdo->prepare("CALL get_client_visits(:client_id)");
            $stmt->execute(['client_id' => $_SESSION['client_id']]);
            $visits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            foreach ($visits as $visit) {
                    $clientVisits[] = $visit['workout_id']; // Явно собираем ID тренировок
            }

            // Очистка курсора, если дальше идёт другой запрос
            $stmt->closeCursor();
            header("Location: schedule.php"); // Обновить страницу
        }

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error_message = "Ошибка записи: " . $e->getMessage();
    }
}

// Массив дней недели
$daysOfWeek = [
    'Пн' => 'monday',
    'Вт' => 'tuesday',
    'Ср' => 'wednesday', 
    'Чт' => 'thursday',
    'Пт' => 'friday',
    'Сб' => 'saturday',
    'Вс' => 'sunday'
];
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Расписание</title>
    <link rel="stylesheet" href="schedule_style.css">
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
    <div class="top-bar">
        <div class="date"><?= strftime('%d %B, %Y', strtotime($selectedDate)) ?></div>
        <?php if ($role === 'admin'): ?>
            <a href="add_workout.php" class="plus">+</a>
        <?php endif; ?>
    </div>

    <div class="week-days">
        <a href="?date=<?= date('Y-m-d', strtotime($selectedDate . ' -1 week')) ?>" class="arrow">&larr;</a>
        <?php foreach ($daysOfWeek as $short => $day):
            $dayDate = date('Y-m-d', strtotime($day . ' this week', strtotime($selectedDate)));
            $isActive = ($dayDate == $selectedDate) ? 'active' : '';
        ?>
            <a href="?date=<?= $dayDate ?>" class="day <?= $isActive ?>"><?= $short ?></a>
        <?php endforeach; ?>
        <a href="?date=<?= date('Y-m-d', strtotime($selectedDate . ' +1 week')) ?>" class="arrow">&rarr;</a>
    </div>

    <?php if (!empty($success_message)): ?>
        <div class="success-message"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
        <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <div class="workouts">
        <?php if ($workouts): ?>
            <?php foreach ($workouts as $workout):
                $start = date('H:i', strtotime($workout['start_time']));
                $end = date('H:i', strtotime($workout['start_time']) + $workout['duration'] * 60);
                $isClientBooked = in_array($workout['workout_id'], $clientVisits);
            ?>
                <div class="workout">
                    <div class="workout-info">
                        <div class="title"><?= htmlspecialchars($workout['template_name']) ?></div>
                        <div class="time"><?= $start ?> - <?= $end ?></div>
                        <div class="coach">Тренер: <?= $workout['coach_surname'] ?> <?= $workout['coach_name'] ?> <?= $workout['coach_patronymic'] ?></div>
                        <div class="booking">Запись: <?= $workout['booked'] ?> / <?= $workout['visit_limit'] ?></div>
                    </div>

                    <?php if ($role === 'admin'): ?>
                        <div class="admin-actions">
                            <a href="edit_workout.php?id=<?= $workout['workout_id'] ?>" class="edit-button">Редактировать</a>
                            <form method="post" onsubmit="return confirm('Удалить тренировку?')" style="margin: 5px 0 0;">
                            <input type="hidden" name="delete_workout_id" value="<?= $workout['workout_id'] ?>">
                            <button type="submit" class="delete-button">Удалить</button>
                            </form>
                        </div>
                    <?php elseif ($role === 'client'): ?>
                        <?php if ($isClientBooked): ?>
                            <div class="booked-label">Записан</div>
                        <?php else: ?>
                            <form method="post" style="margin-top: 10px;">
                                <input type="hidden" name="book_workout_id" value="<?= $workout['workout_id'] ?>">
                                <button type="submit" class="signup-button">Записаться</button>
                            </form>
                        <?php endif; ?>

                    <?php elseif ($role === 'coach'): ?>
                        <a href="mark_attendance.php?id=<?= $workout['workout_id'] ?>" class="mark-button">Отметить</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-workouts">Нет тренировок на выбранный день</div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
