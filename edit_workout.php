<?php
include_once 'config.php';

$workout_id = $_GET['id'] ?? $_POST['workout_id'] ?? null;
if (!$workout_id) {
    die("ID тренировки не указан.");
}
$stmt = $pdo->prepare("SELECT * FROM workouts WHERE workout_id = ?");
$stmt->execute([$workout_id]);
$workout = $stmt->fetch();

// Все тренеры
$coaches = $pdo->query("SELECT * FROM coaches")->fetchAll();

if (!$workout || !isset($workout['coach_id'])) {
    die("Тренировка не найдена или не содержит coach_id.");
}

// Обновление тренировки
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $start_time = $_POST['start_time'];
    $template_id = $_POST['template_id'];
    $coach_id = $_POST['coach_id'];

    $stmt = $pdo->prepare("UPDATE workouts SET start_time = ?, template_id = ?, coach_id = ? WHERE workout_id = ?");
    $stmt->execute([$start_time, $template_id, $coach_id, $workout_id]);
    header("Location: schedule.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_visit_id'])) {
    $visit_id = (int)$_POST['remove_visit_id'];

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("SELECT client_id, workout_id FROM visits WHERE visit_id = ?");
        $stmt->execute([$visit_id]);
        $visit = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$visit) {
            throw new Exception("Посещение не найдено.");
        }

        $client_id = $visit['client_id'];

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
            $stmt = $pdo->prepare("UPDATE subscriptions SET rest = rest + 1 WHERE subscription_id = ?");
            $stmt->execute([$subscription_id]);
        }

        $stmt = $pdo->prepare("DELETE FROM visits WHERE visit_id = ?");
        $stmt->execute([$visit_id]);

        $pdo->commit();
        header("Location: edit_workout.php?id=$workout_id");
        exit;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = "Ошибка при удалении клиента: " . $e->getMessage();
    }
}


// Добавление клиента в тренировку
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_client_id'])) {
    $client_id = (int)$_POST['add_client_id'];

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("CALL book_workout(:client_id, :workout_id, @result)");
        $stmt->execute([
            'client_id' => $client_id,
            'workout_id' => $workout_id
        ]);
        $stmt->closeCursor();

        $result = $pdo->query("SELECT @result AS result")->fetch(PDO::FETCH_ASSOC);
        if (!$result || $result['result'] !== 'OK') {
            throw new Exception($result['result'] ?? 'Неизвестная ошибка');
        }

        $pdo->commit();
        header("Location: edit_workout.php?id=$workout_id");
        exit;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = "Ошибка при добавлении клиента: " . $e->getMessage();
    }
}

// Получение данных тренировки
$stmt = $pdo->prepare("SELECT * FROM workouts WHERE workout_id = ?");
$stmt->execute([$workout_id]);
$workout = $stmt->fetch();

// Все шаблоны
$templates = $pdo->query("SELECT * FROM templates")->fetchAll();

// Записанные клиенты
$stmt = $pdo->prepare("
    SELECT v.visit_id, c.client_id, c.name, c.surname, c.patronymic
    FROM visits v
    JOIN clients c ON v.client_id = c.client_id
    WHERE v.workout_id = ?
");
$stmt->execute([$workout_id]);
$clients = $stmt->fetchAll();

// Получить клиентов, которых ещё нет в записях
$existing_ids = array_column($clients, 'client_id');
$placeholders = str_repeat('?,', count($existing_ids) ?: 1);
$placeholders = rtrim($placeholders, ',');

if (count($existing_ids)) {
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE client_id NOT IN ($placeholders)");
    $stmt->execute($existing_ids);
} else {
    $stmt = $pdo->query("SELECT * FROM clients");
}
$available_clients = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактирование тренировки</title>
    <style>
        body {
            font-family: sans-serif;
            background: #f4f4f4;
            padding: 0;
            margin: 0;
        }
        .container {
            max-width: 700px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2, h3 {
            margin-top: 0;
            color: #333;
        }
        form {
            margin-bottom: 30px;
        }
        label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }
        input, select, button {
            padding: 10px;
            font-size: 16px;
            margin-top: 5px;
        }
        input, select {
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        .edit-button {
            background: #ce8bc0;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            margin-top: 15px;
            cursor: pointer;
        }
        .edit-button:hover {
            background: #b870aa;
        }
        .client-list {
            list-style: none;
            padding: 0;
        }
        .client-item {
            background: #e7c5e0;
            border: 2px solid #c67cc4;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
        }
        .client-item form {
            margin: 0;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Редактирование тренировки</h2>

    <?php if (!empty($error)): ?>
        <div class="error" style="color:red"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="workout_id" value="<?= $workout_id ?>">
        <input type="hidden" name="action" value="update">

        <label for="start_time">Время начала:</label>
        <input type="datetime-local" id="start_time" name="start_time"
               value="<?= date('Y-m-d\TH:i', strtotime($workout['start_time'])) ?>" required>

        <label for="template_id">Направление:</label>
        <select name="template_id" id="template_id">
            <?php foreach ($templates as $template): ?>
                <option value="<?= $template['template_id'] ?>"
                    <?= $template['template_id'] == $workout['template_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($template['name']) ?> (<?= $template['duration'] ?> мин)
                </option>
            <?php endforeach; ?>
        </select>
        <label for="coach_id">Тренер:</label>
        <select name="coach_id" id="coach_id">
            <?php foreach ($coaches as $coach): ?>
                <option value="<?= $coach['coach_id'] ?>" <?= $coach['coach_id'] == $workout['coach_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($coach['surname'] . ' ' . $coach['name'] . ' ' . $coach['patronymic']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="edit-button">Сохранить</button>
    </form>

    <h3>Записанные клиенты</h3>
    <ul class="client-list">
        <?php foreach ($clients as $client): ?>
            <li class="client-item">
                <?= htmlspecialchars($client['surname'] . ' ' . $client['name'] . ' ' . $client['patronymic']) ?>
                <form method="post" onsubmit="return confirm('Удалить клиента из записи?')">
                    <input type="hidden" name="remove_visit_id" value="<?= $client['visit_id'] ?>">
                    <button type="submit" class="edit-button">Удалить</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>

    <h3>Добавить клиента</h3>
    <form method="post">
        <input type="hidden" name="workout_id" value="<?= $workout_id ?>">
        <select name="add_client_id" required>
            <?php foreach ($available_clients as $client): ?>
                <option value="<?= $client['client_id'] ?>">
                    <?= htmlspecialchars($client['surname'] . ' ' . $client['name'] . ' ' . $client['patronymic']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="edit-button">Записать</button>
    </form>
</div>
</body>
</html>
