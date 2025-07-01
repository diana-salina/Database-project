<?php
include_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $surname = $_POST['surname'];
    $name = $_POST['name'];
    $patronymic = $_POST['patronymic'];
    $phone = $_POST['phone'];
    $sex = $_POST['sex'];
    $birthday = $_POST['birthday'];

    if ($surname && $name && $patronymic && $phone && $sex && $birthday > 0) {
        // Последние 4 цифры телефона
        $password = substr(preg_replace('/\D/', '', $phone), -4);

        $username = $phone;
        $stmt = $pdo->prepare("INSERT INTO clients (name, surname, patronymic, phone, password, sex, birthday) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $surname, $patronymic, $phone, $password, $sex, $birthday]);

        $client_id = $pdo->lastInsertId();
        $dbUser = "client_" . $client_id;
        $dbPass = $password;
        $pdo->exec("CREATE USER IF NOT EXISTS '$dbUser'@'localhost' IDENTIFIED WITH mysql_native_password BY '$dbPass'");
        
        $pdo->exec("GRANT SELECT ON fitness_salina.* TO '$dbUser'@'localhost';");
        $pdo->exec("FLUSH PRIVILEGES");
        $pdo->exec("GRANT EXECUTE ON PROCEDURE `fitness_salina`.`get_workouts_by_date` TO '$dbUser'@'localhost'");
        $pdo->exec("GRANT EXECUTE ON PROCEDURE `fitness_salina`.`get_client_visits` TO '$dbUser'@'localhost'");
        $pdo->exec("GRANT EXECUTE ON PROCEDURE `fitness_salina`.`book_workout` TO '$dbUser'@'localhost'");
        $pdo->exec("FLUSH PRIVILEGES");


        header("Location: clients.php");
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавить клиента</title>
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
    </style>
</head>
<body>
<div class="container">
    <h2>Добавить клиента</h2>

    <form method="POST">
        <label for="surname">Фамилия:</label>
        <input type="text" name="surname" id="surname" required>

        <label for="name">Имя:</label>
        <input type="text" name="name" id="name" required>

        <label for="patronymic">Отчество:</label>
        <input type="text" name="patronymic" id="patronymic">

        <label for="phone">Телефон:</label>
        <input type="text" name="phone" id="phone" required>

        <label for="sex">Пол:</label>
        <select name="sex" id="sex" required>
            <option value="">Выберите</option>
            <option value="мужской">Мужской</option>
            <option value="женский">Женский</option>
        </select>

        <label for="birthday">Дата рождения:</label>
        <input type="date" name="birthday" id="birthday" required>

        <button type="submit" class="btn">Добавить</button>
    </form>

    <a href="clients.php" class="back-btn">Назад</a>
</div>
</body>
</html>
