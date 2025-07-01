<?php
include_once 'config.php';

// Добавление шаблона тренировки
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_template') {
    $name = $_POST['name'];
    $duration = (int) $_POST['duration'];
    $description = $_POST['description'];

    if ($name && $duration > 0) {
        $stmt = $pdo->prepare("INSERT INTO templates (name, duration, description) VALUES (?, ?, ?)");
        $stmt->execute([$name, $duration, $description]);
        header("Location: templates.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавление направления</title>
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

        input, textarea, button {
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

    </style>
</head>
<body>
<div class="container">
    <h2>Добавление направления</h2>

    <form method="POST">
        <input type="hidden" name="action" value="add_template">

        <label for="name">Название направления:</label>
        <input type="text" name="name" id="name" required>

        <label for="duration">Длительность (в минутах):</label>
        <input type="number" name="duration" id="duration" required min="1">

        <label for="description">Описание:</label>
        <textarea name="description" id="description" rows="4"></textarea>

        <button class="submit-button" type="submit">Добавить</button>
    </form>
    <a href="templates.php" class="back-button">Назад</a>
</div>
</body>
</html>
