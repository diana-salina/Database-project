<?php
include_once 'config.php';

if (!isset($_GET['id'])) {
    header("Location: coaches.php");
    exit;
}

$coach_id = (int) $_GET['id'];

// Получение текущих данных
$stmt = $pdo->prepare("SELECT * FROM coaches WHERE coach_id = ?");
$stmt->execute([$coach_id]);
$coach = $stmt->fetch();

if (!$coach) {
    echo "Тренер не найден.";
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
    $bid = (float) $_POST['bid'];
    $description = $_POST['description'];

    if ($surname && $name && $phone && $sex && $birthday && $bid > 0) {
        $stmt = $pdo->prepare("UPDATE coaches SET surname=?, name=?, patronymic=?, phone=?, sex=?, birthday=?, bid_per_hour=?, description=? WHERE coach_id=?");
        $stmt->execute([$surname, $name, $patronymic, $phone, $sex, $birthday, $bid, $description, $coach_id]);
        header("Location: coaches.php");
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
    <title>Редактировать тренера</title>
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
    <h2>Редактировать информацию о тренере</h2>

    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

    <form method="POST">
        <label for="surname">Фамилия:</label>
        <input type="text" name="surname" id="surname" value="<?= htmlspecialchars($coach['surname']) ?>" required>

        <label for="name">Имя:</label>
        <input type="text" name="name" id="name" value="<?= htmlspecialchars($coach['name']) ?>" required>

        <label for="patronymic">Отчество:</label>
        <input type="text" name="patronymic" id="patronymic" value="<?= htmlspecialchars($coach['patronymic']) ?>">

        <label for="phone">Телефон:</label>
        <input type="text" name="phone" id="phone" value="<?= htmlspecialchars($coach['phone']) ?>" required>

        <label for="sex">Пол:</label>
        <select name="sex" id="sex" required>
            <option value="мужской" <?= $coach['sex'] === 'мужской' ? 'selected' : '' ?>>Мужской</option>
            <option value="женский" <?= $coach['sex'] === 'женский' ? 'selected' : '' ?>>Женский</option>
        </select>

        <label for="birthday">Дата рождения:</label>
        <input type="date" name="birthday" id="birthday" value="<?= $coach['birthday'] ?>" required>

        <label for="bid">Ставка за занятие (₽):</label>
        <input type="number" step="0.01" name="bid" id="bid" value="<?= $coach['bid_per_hour'] ?>" required min="0">

        <label for="description">Описание:</label>
        <textarea name="description" id="description" rows="4"><?= htmlspecialchars($coach['description']) ?></textarea>

        <button type="submit" class="btn">Сохранить</button>
    </form>

    <a href="coaches.php" class="back-btn">Назад</a>
</div>
</body>
</html>
