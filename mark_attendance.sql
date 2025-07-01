CREATE PROCEDURE `mark_attendance`(
    IN p_visit_id INT,
    IN p_status VARCHAR(20),
    OUT p_result VARCHAR(255)
)
proc_end: BEGIN
    DECLARE v_coach_id INT;

    -- Проверка допустимого значения
    IF p_status NOT IN ('посетил', 'не посетил') THEN
        SET p_result = 'Недопустимое значение статуса';
        LEAVE proc_end;
    END IF;

    -- Получаем coach_id
    SELECT w.coach_id INTO v_coach_id
    FROM visits v
    JOIN workouts w ON v.workout_id = w.workout_id
    WHERE v.visit_id = p_visit_id;

    IF v_coach_id IS NULL THEN
        SET p_result = 'Запись не найдена';
        LEAVE proc_end;
    END IF;

    -- Обновляем статус
    UPDATE visits 
    SET is_attended = p_status
    WHERE visit_id = p_visit_id;

    SET p_result = 'OK';
END