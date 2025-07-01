INSERT INTO `templates` (`name`, `duration`, `description`)
VALUES (
    'Пилатес',
    60,
    'Тренировка на укрепление мышц кора'
),
(
    'Подкачка+растяжка',
    90,
    'Тренировка с силовым уклонов в первой половине и общей растяжкой во второй'
);
INSERT INTO `coaches` (`name`, `surname`, `patronymic`, `phone`, `password`, `sex`, `birthday`, `description`, `bid_per_hour`)
VALUES (
    'Диана',
    'Салина',
    'Евгеньевна',
    '89137737022',
    '7022',
    'женский',
    '2004-11-05',
    'Сертифицированнный тренер по стретчингу',
    100
),
(
    'Иванова',
    'Анна',
    'Юрьевна',
    '89137731111',
    '1111',
    'женский',
    '2004-12-05',
    'Сертифицированнный тренер по стретчингу',
    150
);
INSERT INTO `workouts` (`coach_id`, `template_id`, `start_time`, `visit_limit`)
VALUES (
    1,
    1,
    '2025-06-02 10:00:00',
    9
),
(
    1,
    1,
    '2025-06-02 20:00:00',
    9
),
(2, 2, '2025-06-02 18:00:00', 10),
(1, 1, '2025-06-03 08:00:00', 8),
(2, 2, '2025-06-03 17:00:00', 10);

INSERT INTO `clients` (`name`, `surname`, `patronymic`, `phone`, `password`, `sex`, `birthday`)
VALUES (
    'Анастасия',
    'Суркова',
    'Алексеевна',
    '89139293232',
    '3232',
    'женский',
    '2004-12-15'
),
(
    'Тимофей',
    'Гавриков',
    'Михайлович',
    '89139000001',
    '0001',
    'мужской',
    '2004-05-20'
);

INSERT INTO `subscription_templates` (`name`, `amount`, `cost`)
VALUES (
    '8 занятий',
    8,
    2300
),
(
    'Разовый',
    1,
    400
);
INSERT INTO `subscriptions` (`subtemp_id`, `purchase_date`, `expiration_date`, `rest`, `client_id`)
VALUES (
    1,
    '2025-05-28',
    '2025-06-28',
    3,
    1
),
(
    1,
    '2025-05-28',
    '2025-06-28',
    4,
    2
);
INSERT INTO `visits` (`client_id`, `workout_id`, `is_attended`)
VALUES (1, 1, 'не определено'),
(2, 1, 'не определено'),
(1, 2, 'посещена'),
(1, 3, 'посещена'),
(2, 4, 'посещена'),
(1, 5, 'не определено'),
(2, 2, 'не посещена'),
(2, 3, 'не посещена');

-- Создание пользователей
CREATE USER IF NOT EXISTS 'client_1'@'localhost' IDENTIFIED BY '3232';
CREATE USER IF NOT EXISTS 'client_2'@'localhost' IDENTIFIED BY '0001';
CREATE USER IF NOT EXISTS 'coach_1'@'localhost' IDENTIFIED BY '7022';
CREATE USER IF NOT EXISTS 'coach_2'@'localhost' IDENTIFIED BY '1111';

-- Права на чтение
GRANT SELECT ON fitness_salina.* TO 'client_1'@'localhost';
GRANT SELECT ON fitness_salina.* TO 'client_2'@'localhost';
GRANT SELECT ON fitness_salina.* TO 'coach_1'@'localhost';
GRANT SELECT ON fitness_salina.* TO 'coach_2'@'localhost';
FLUSH PRIVILEGES;

-- EXECUTE права на процедуры
GRANT EXECUTE ON PROCEDURE fitness_salina.get_workouts_by_date TO 'client_1'@'localhost', 'client_2'@'localhost';
GRANT EXECUTE ON PROCEDURE fitness_salina.get_client_visits TO 'client_1'@'localhost', 'client_2'@'localhost';
GRANT EXECUTE ON PROCEDURE fitness_salina.book_workout TO 'client_1'@'localhost', 'client_2'@'localhost';
GRANT EXECUTE ON PROCEDURE fitness_salina.mark_attendance TO 'coach_1'@'localhost', 'coach_2'@'localhost';
GRANT EXECUTE ON PROCEDURE fitness_salina.get_coach_workouts TO 'coach_1'@'localhost', 'coach_2'@'localhost';

FLUSH PRIVILEGES;