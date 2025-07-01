CREATE PROCEDURE `book_workout`(
    IN p_client_id INT,
    IN p_workout_id INT,
    OUT p_result VARCHAR(255)
)
proc_end: BEGIN
    DECLARE v_sub_id INT DEFAULT NULL;
    DECLARE v_workout_date DATE;
    DECLARE v_booked INT;
    DECLARE v_limit INT;
    DECLARE v_coach_id INT;

    -- Получаем данные по тренировке
    SELECT DATE(start_time), visit_limit, coach_id
    INTO v_workout_date, v_limit, v_coach_id
    FROM workouts 
    WHERE workout_id = p_workout_id;

    IF v_workout_date IS NULL THEN
        SET p_result = 'Тренировка не найдена';
        LEAVE proc_end;
    END IF;

    -- Проверка лимита мест
    SELECT COUNT(*) INTO v_booked 
    FROM visits 
    WHERE workout_id = p_workout_id;

    IF v_booked >= v_limit THEN
        SET p_result = 'Нет свободных мест на эту тренировку';
        LEAVE proc_end;
    END IF;

    -- Проверка абонемента (без coach_id)
    SELECT subscription_id INTO v_sub_id
    FROM subscriptions 
    WHERE client_id = p_client_id 
      AND rest > 0 
      AND purchase_date <= v_workout_date 
      AND expiration_date >= v_workout_date
    ORDER BY expiration_date DESC
    LIMIT 1;

    IF v_sub_id IS NULL THEN
        SET p_result = 'Нет действующего абонемента';
        LEAVE proc_end;
    END IF;

    -- Проверка дублирования
    IF EXISTS (
        SELECT 1 FROM visits 
        WHERE client_id = p_client_id AND workout_id = p_workout_id
    ) THEN
        SET p_result = 'Вы уже записаны на эту тренировку';
        LEAVE proc_end;
    END IF;

    -- Запись
    INSERT INTO visits (client_id, workout_id, is_attended) 
    VALUES (p_client_id, p_workout_id, 'не определено');

    -- Списание занятия
    UPDATE subscriptions 
    SET rest = rest - 1 
    WHERE subscription_id = v_sub_id;

    SET p_result = 'OK';
END