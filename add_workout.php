<?php
include_once 'config.php';

// Добавление тренировки
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_workout') {
    $template_id = (int) $_POST['template_id'];
    $start_time = $_POST['start_time'];
    $visit_limit = (int) $_POST['visit_limit'];

    if (strtotime($start_time) > time()) {
        $coach_id = 1; // допустим, по умолчанию id тренера 1
        $stmt = $pdo->prepare("INSERT INTO workouts (coach_id, template_id, start_time, visit_limit) VALUES (?, ?, ?, ?)");
        $stmt->execute([$coach_id, $template_id, $start_time, $visit_limit]);
        header("Location: schedule.php");
        exit;
    } else {
        $error = "Дата и время должны быть в будущем.";
    }
}

// Шаблоны тренировок
$templates = $pdo->query("SELECT * FROM templates")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавление тренировки</title>
    <style>
        body {
            font-family: sans-serif;
            background: #f2f2f2;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 700px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        h2 {
            color: #333;
            margin-top: 0;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
        }

        input, select, textarea, button {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-sizing: border-box;
        }

        textarea {
            resize: vertical;
        }

        .submit-button,
.back-button {
    margin-top: 20px;
    background-color: #ce8bc0;
    color: white;
    border: none;
    cursor: pointer;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 16px;
    width: 100%;
    box-sizing: border-box;
}

.submit-button:hover,
.back-button:hover {
    background-color: #b870aa;
}


        .error {
            color: red;
            margin-top: 10px;
        }

        .form-section {
            margin-bottom: 40px;
        }

        hr {
            margin: 40px 0;
            border: 0;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Добавление тренировки</h2>

    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="form-section">
        <form method="POST">
            <input type="hidden" name="action" value="add_workout">

            <label for="start_time">Дата и время начала:</label>
            <input type="datetime-local" name="start_time" id="start_time" required>

            <label for="template_id">Направление:</label>
            <select name="template_id" id="template_id" required>
                <?php foreach ($templates as $template): ?>
                    <option value="<?= $template['template_id'] ?>">
                        <?= htmlspecialchars($template['name']) ?> (<?= $template['duration'] ?> мин)
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="visit_limit">Лимит посещений:</label>
            <input type="number" name="visit_limit" id="visit_limit" value="10" min="1" required>

            <button class="submit-button" type="submit">Добавить тренировку</button>
        </form>
        <a href="schedule.php" class="back-button">Назад</a>
    </div>
</div>
</body>
</html>
