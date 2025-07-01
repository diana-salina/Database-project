<?php
include_once 'config.php';

$workout_id = $_GET['id'] ?? null;
if (!$workout_id) {
    die("ID тренировки не указан.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['attendance'] as $visit_id => $status) {
        $stmt = $pdo->prepare("UPDATE visits SET is_attended = ? WHERE visit_id = ?");
        $stmt->execute([$status, $visit_id]);
    }
    header("Location: schedule.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT v.visit_id, c.name, c.surname, c.patronymic, v.is_attended
    FROM visits v
    JOIN clients c ON v.client_id = c.client_id
    WHERE v.workout_id = ?
");
$stmt->execute([$workout_id]);
$clients = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Отметка посещаемости</title>
    <link rel="stylesheet" href="mark_attendance_style.css">
</head>
<body>
<div class="container">
    <h2>Отметка посещаемости</h2>
    <form method="post">
        <ul class="client-list">
            <?php foreach ($clients as $client): ?>
                <li class="client-item">
                    <span><?= htmlspecialchars($client['surname'] . ' ' . $client['name'] . ' ' . $client['patronymic']) ?></span>
                    <div class="attendance-buttons">
                        <?php
                            $visitId = $client['visit_id'];
                            $checkedYes = $client['is_attended'] === 'посещена' ? 'checked' : '';
                            $checkedNo = $client['is_attended'] === 'не посещена' ? 'checked' : '';
                        ?>
                        <input type="radio" id="yes-<?= $visitId ?>" name="attendance[<?= $visitId ?>]" value="посещена" <?= $checkedYes ?>>
                        <label for="yes-<?= $visitId ?>" class="option-button">Был</label>

                        <input type="radio" id="no-<?= $visitId ?>" name="attendance[<?= $visitId ?>]" value="не посещена" <?= $checkedNo ?>>
                        <label for="no-<?= $visitId ?>" class="option-button">Не был</label>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="button-group">
            <button type="submit" class="main-button save-button">Сохранить</button>
            <a href="schedule.php" class="main-button back-button">Назад</a>
        </div>
    </form>
</div>
</body>
</html>
