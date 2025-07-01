<?php
include_once 'config.php';

if (!isset($_GET['id'])) {
    header("Location: clients.php");
    exit;
}

$client_id = (int) $_GET['id'];

// Получение текущих данных
$stmt = $pdo->prepare("SELECT * FROM clients WHERE client_id = ?");
$stmt->execute([$client_id]);
$client = $stmt->fetch();

if (!$client) {
    echo "Клиент не найден.";
    exit;
}

// Обработка отправки формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $surname = $_POST['surname'];
    $name = $_POST['name'];
    $patronymic = $_POST['patronymic'];
    $phone = $_POST['phone'];
    $sex = $_POST['sex'];
    $birthday = $_POST['birthday'];

    if ($surname && $name && $phone && $sex && $birthday > 0) {
        $stmt = $pdo->prepare("UPDATE clients SET surname=?, name=?, patronymic=?, phone=?, sex=?, birthday=? WHERE client_id=?");
        $stmt->execute([$surname, $name, $patronymic, $phone, $sex, $birthday, $client_id]);
        header("Location: clients.php");
        exit;
    } else {
        $error = "Пожалуйста, заполните все обязательные поля.";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактировать клиента</title>
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
}

        .error {
            color: red;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Редактировать информацию о клиенте</h2>

    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

    <form method="POST">
        <label for="surname">Фамилия:</label>
        <input type="text" name="surname" id="surname" value="<?= htmlspecialchars($client['surname']) ?>" required>

        <label for="name">Имя:</label>
        <input type="text" name="name" id="name" value="<?= htmlspecialchars($client['name']) ?>" required>

        <label for="patronymic">Отчество:</label>
        <input type="text" name="patronymic" id="patronymic" value="<?= htmlspecialchars($client['patronymic']) ?>">

        <label for="phone">Телефон:</label>
        <input type="text" name="phone" id="phone" value="<?= htmlspecialchars($client['phone']) ?>" required>

        <label for="sex">Пол:</label>
        <select name="sex" id="sex" required>
            <option value="мужской" <?= $client['sex'] === 'мужской' ? 'selected' : '' ?>>Мужской</option>
            <option value="женский" <?= $client['sex'] === 'женский' ? 'selected' : '' ?>>Женский</option>
        </select>

        <label for="birthday">Дата рождения:</label>
        <input type="date" name="birthday" id="birthday" value="<?= $client['birthday'] ?>" required>

        <button type="submit" class="btn">Сохранить</button>
    </form>

    <a href="clients.php" class="back-btn">Назад</a>
</div>
</body>
</html>
