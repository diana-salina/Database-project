<?php
include_once 'config.php';

if (!isset($_GET['client_id'])) {
    die("ID клиента не указан.");
}

$client_id = (int)$_GET['client_id'];

// Получение клиента
$stmt = $pdo->prepare("SELECT * FROM clients WHERE client_id = ?");
$stmt->execute([$client_id]);
$client = $stmt->fetch();

if (!$client) {
    die("Клиент не найден.");
}

// Получение шаблонов абонементов
$templates = $pdo->query("SELECT * FROM subscription_templates")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subtemp_id = $_POST['subtemp_id'];
    $start_date = $_POST['start_date'];

    // Получаем количество занятий
    $stmt = $pdo->prepare("SELECT amount FROM subscription_templates WHERE subtemp_id = ?");
    $stmt->execute([$subtemp_id]);
    $template = $stmt->fetch();

    if ($template) {
        $rest = $template['amount'];
        $expiration = date('Y-m-d', strtotime($start_date . ' +1 month'));

        $stmt = $pdo->prepare("INSERT INTO subscriptions (subtemp_id, purchase_date, expiration_date, rest, client_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$subtemp_id, $start_date, $expiration, $rest, $client_id]);

        header("Location: clients.php");
        exit;
    } else {
        $error = "Неверный шаблон абонемента.";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Оформление абонемента</title>
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

        .btn,
.back-btn {
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

.btn:hover,
.back-btn:hover {
    background-color: #b870aa;
</style>
</head>
<body>

<hr class="line">

<div class="container">
    <h2>Оформить абонемент для <?= htmlspecialchars($client['surname'] . ' ' . $client['name']) ?></h2>

    <?php if (!empty($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <label for="subtemp_id">Выберите шаблон:</label>
        <select name="subtemp_id" id="subtemp_id" required>
            <option value="" disabled selected>-- Выберите --</option>
            <?php foreach ($templates as $template): ?>
                <option value="<?= $template['subtemp_id'] ?>">
                    <?= htmlspecialchars($template['name']) ?> — <?= $template['amount'] ?> занятий, <?= $template['cost'] ?> ₽
                </option>
            <?php endforeach; ?>
        </select>
        <label for="start_date">Дата начала действия:</label>
        <input type="date" name="start_date" id="start_date" required min="<?= date('Y-m-d') ?>">

        <button type="submit" class="back-btn">Оформить абонемент</button>
    </form>
    <a href="coaches.php" class="back-btn">Назад</a>
</div>
</body>
</html>
